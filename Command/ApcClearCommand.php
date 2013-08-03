<?php

namespace Ornicar\ApcBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ApcClearCommand
 * @package Ornicar\ApcBundle\Command
 */
class ApcClearCommand extends ContainerAwareCommand
{
    /**
     * @var boolean
     */
    public $clearMode;

    /**
     * @var boolean
     */
    public $clearOpCodeCacheOnly;

    /**
     * @var boolean
     */
    public $clearUserCacheOnly;

    /**
     * @var boolean
     */
    public $clearAll;

    /**
     * @var \Goutte\Client
     */
    public $client;

    /**
     * @var string
     */
    public $filePath;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var string
     */
    public $webDir;

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param mixed $clearMode
     */
    public function setClearMode($clearMode)
    {
        $this->clearMode = $clearMode;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param \Goutte\Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $webDir
     */
    public function setWebDir($webDir)
    {
        $this->webDir = $webDir;
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->addOption('opcode', null, InputOption::VALUE_NONE, 'Clear only opcode cache')
            ->addOption('user', null, InputOption::VALUE_NONE, 'Clear only user cache')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL,
                'Server host from which a script clearing APC cache is triggered, e.g. --mode="http://127.0.0.1"')
            ->addOption('mode', null, InputOption::VALUE_OPTIONAL,
                'Clearing mode (possible choices are "fopen", "curl"), e.g. --mode=fopen)', 'fopen')
            ->addOption('web_dir', null, InputOption::VALUE_OPTIONAL,
                'Relative path to web dir (from kernel root dir, e.g. --web_dir="../web")')
            ->setName('apc:clear')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUpOptions($input);

        $filename = 'apc-'.md5(uniqid().mt_rand(0, 9999999).php_uname()).'.php';
        $this->filePath = $this->webDir.'/'.$filename;

        $this->saveClearCacheScript();
        $url = $this->host . '/' . $filename;

        if ($this->mode == 'fopen') {
            $result = $this->clearCacheWithFopen($url);
        } elseif ($this->mode == 'curl') {
            $result = $this->clearCacheWithCurl($url);
        } elseif ($this->mode == 'goutte') {
            $result = $this->clearCacheWithGoutte($url);
        }

        $result = json_decode($result, true);
        $lastJsonError = json_last_error();
        if ($lastJsonError !== JSON_ERROR_NONE) {
            throw new \Exception(sprintf('Clearing APC cache failed with JSON decoding error code %d', $lastJsonError));
        }

        unlink($this->filePath);

        if ($result['success']) {
            $output->writeln($result['message']);
        } else {
            throw new \RuntimeException($result['message']);
        }
    }

    /**
     * @param InputInterface $input
     */
    protected function setUpOptions(InputInterface $input)
    {
        $this->clearOpCodeCacheOnly = $input->getOption('opcode') || !$input->getOption('user');
        $this->clearUserCacheOnly = $input->getOption('user') || !$input->getOption('opcode');

        $this->setUpHost($input);
        $this->setUpMode($input);
        $this->setUpWebDir($input);
    }

    /**
     * @param InputInterface $input
     */
    protected function setUpHost(InputInterface $input)
    {
        if ($input->hasOption('host') && $input->getOption('host')) {
            $this->host = $input->getOption('host');
        } else {
            if ($this->getContainer()->getParameter('ornicar_apc.host')) {
                $this->host = $this->getContainer()->getParameter('ornicar_apc.host');
            } else {
                $this->host = sprintf("%s://%s", $this->getContainer()->getParameter('router.request_context.scheme'),
                    $this->getContainer()->getParameter('router.request_context.host'));
            }
        }
    }

    /**
     * @param InputInterface $input
     */
    protected function setUpMode(InputInterface $input)
    {
        if ($input->hasOption('mode')) {
            $this->mode = $input->getOption('mode');
        } else {
            $this->mode = $this->getContainer()->getParameter('ornicar_apc.mode');
        }

        $this->validateMode();
    }


    protected function validateMode()
    {
        $modes = array('fopen', 'curl', 'goutte');
        if (is_null($this->mode) || !in_array($this->mode, $modes)) {
            throw new \InvalidArgumentException('Clearing APC cache requires selecting one of the following modes: ' .
            implode(', ', $modes));
        }

        if ($this->mode == 'curl' && !extension_loaded('curl')) {
            throw new \InvalidArgumentException('Curl extension is missing.');
        }

        if ($this->mode == 'goutte' && !class_exists('\Goutte\Client')) {
            throw new \InvalidArgumentException('Install Goutte Client by running "composer install --dev" from this project root directory.');
        } else {
            if (is_null($this->client)) {
                $this->client = new \Goutte\Client;
            }
        }
    }

    /**
     * @param InputInterface $input
     */
    protected function setUpWebDir(InputInterface $input)
    {
        if (is_null($this->webDir)) {
            if ($input->hasOption('web_dir') && $input->getOption('web_dir')) {
                $kernelRootDir = $this->getContainer()->getParameter('kernel.root_dir');
                $this->webDir = $kernelRootDir . '/' .  $input->getOption('web_dir');
            } else {
                $this->webDir = $this->getContainer()->getParameter('ornicar_apc.web_dir');
            }
        }

        $this->validateWebDir();
    }

    protected function validateWebDir()
    {
        if (!is_dir($this->webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir does not exist "%s"', $this->webDir));
        }
        if (!is_writable($this->webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir is not writable "%s"', $this->webDir));
        }
    }

    /**
     * @param $url
     * @return mixed
     */
    protected function clearCacheWithGoutte($url)
    {
        $this->client->request('GET', $url);

        /**
         * @var $response \Symfony\Component\BrowserKit\Response
         */
        $response = $this->client->getResponse();
        if ($response->getStatus() !== 200) {
            unlink($this->filePath);
            throw new \Exception(sprintf('Has your host / server been properly configured? See also %s for nginx.',
                'https://github.com/ornicar/ApcBundle/pull/22'));
        }

        return $response->getContent();
    }

    /**
     * @param $url
     * @throws \RuntimeException
     */
    protected function clearCacheWithCurl($url)
    {
        $ch = curl_init($url);
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FAILONERROR => true
            )
        );

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            unlink($this->filePath);
            throw new \RuntimeException(sprintf('Curl error reading "%s": %s', $url, $error));
        }
        curl_close($ch);

        return $result;
    }

    /**
     * @param $url
     * @return string
     * @throws \RuntimeException
     */
    protected function clearCacheWithFopen($url)
    {
        try {
            $result = file_get_contents($url);

            if (!$result) {
                unlink($this->filePath);

                throw new \RuntimeException(sprintf('Unable to read "%s", does the host locally resolve?', $url));
            }
        } catch (\ErrorException $e) {
            unlink($this->filePath);
            throw new \RuntimeException(sprintf('Unable to read "%s", does the host locally resolve?', $url));
        }

        return $result;
    }

    /**
     * @throws \RuntimeException
     */
    protected function saveClearCacheScript()
    {
        $clearCacheTemplate = __DIR__ . '/../Resources/clear_cache.php.tpl';
        $template = file_get_contents($clearCacheTemplate);
        $code = strtr(
            $template,
            array(
                '%clear_user_cache%' => var_export($this->clearUserCacheOnly, true),
                '%clear_opcode_cache%' => var_export($this->clearOpCodeCacheOnly, true)
            )
        );
        if (false === @file_put_contents($this->filePath, $code)) {
            throw new \RuntimeException(sprintf('Unable to write "%s"', $this->filePath));
        }
    }
}
