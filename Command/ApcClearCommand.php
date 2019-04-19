<?php

namespace Ornicar\ApcBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Loads initial data
 */
class ApcClearCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->addOption('opcode', null, InputOption::VALUE_NONE, 'Clear only opcode cache')
            ->addOption('user', null, InputOption::VALUE_NONE, 'Clear only user cache')
            ->addOption('cli', null, InputOption::VALUE_NONE, 'Only clear the cache via the CLI')
            ->addOption('auth', null, InputOption::VALUE_REQUIRED, 'HTTP authentication as username:password')
            ->setName('apc:clear')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clearOpcode = $input->getOption('opcode') || !$input->getOption('user');
        $clearUser = $input->getOption('user') || !$input->getOption('opcode');
        $cli = $input->getOption('cli');

        if ($cli) {
            $result = $this->clearCliCache($clearUser, $clearOpcode);

            if($result['success']) {
                $output->writeln('Cli: '.$result['message']);
            } else {
                throw new \RuntimeException($result['message']);
            }

            return;
        }

        $webDir = $this->getContainer()->getParameter('ornicar_apc.web_dir');
        if (!is_dir($webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir does not exist "%s"', $webDir));
        }
        if (!is_writable($webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir is not writable "%s"', $webDir));
        }
        $filename = 'apc-'.md5(uniqid().mt_rand(0, 9999999).php_uname()).'.php';
        $file = $webDir.'/'.$filename;

        $templateFile = __DIR__.'/../Resources/template.tpl';
        $template = file_get_contents($templateFile);
        $code = strtr($template, array(
            '%user%' => var_export($clearUser, true),
            '%opcode%' => var_export($clearOpcode, true)
        ));

        if (false === @file_put_contents($file, $code)) {
            throw new \RuntimeException(sprintf('Unable to write "%s"', $file));
        }

        if (!$host = $this->getContainer()->getParameter('ornicar_apc.host')) {
            $host = sprintf("%s://%s", $this->getContainer()->getParameter('router.request_context.scheme'), $this->getContainer()->getParameter('router.request_context.host'));
        }

        $parsedUrl = parse_url($host);
        $hostname = $parsedUrl['host'];

        if (array_key_exists('port', $parsedUrl)) {
            $url = sprintf('%s:%s/%s', $hostname, $parsedUrl['port'], $filename);
        } else {
            $url = sprintf('%s/%s', $hostname, $filename);
        }

        $ch = curl_init();
        $curlParameters = array(
            CURLOPT_HEADER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_HTTPHEADER => array('Host: ' . $hostname),
            CURLOPT_SSL_VERIFYHOST => 0,
        );

        // If a username and a password have been specified use them
        if ($this->getContainer()->hasParameter('ornicar_apc.basic_auth_username')
            && $this->getContainer()->hasParameter('ornicar_apc.basic_auth_password')
            && $this->getContainer()->getParameter('ornicar_apc.basic_auth_username') != null
            && $this->getContainer()->getParameter('ornicar_apc.basic_auth_password') != null
        ) {
            $curlParameters[CURLOPT_USERPWD] = $this->getContainer()->getParameter('ornicar_apc.basic_auth_username') . ':' . $this->getContainer()->getParameter('ornicar_apc.basic_auth_password');
        }

        curl_setopt_array($ch, $curlParameters);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            unlink($file);
            throw new \RuntimeException(sprintf('Curl error reading "%s": %s', $url, $error));
        }
        curl_close($ch);

        $result = json_decode($result, true);
        unlink($file);

        if($result['success']) {
            $output->writeln('Web: '.$result['message'].". Reset attempts: ".(empty($i) ? 1 : $i+1).".");
        } else {
            throw new \RuntimeException($result['message']);
        }
    }

    protected function clearCliCache($clearUser, $clearOpcode)
    {
        $success = true;
        $message = '';

        if (function_exists('apc_clear_cache')) {
            if ($clearUser) {
                if (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '>=') && apc_clear_cache()) {
                    $message .= ' User Cache: success';
                } elseif (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '<') && apc_clear_cache('user')) {
                    $message .= ' User Cache: success';
                } else {
                    $success = false;
                    $message .= ' User Cache: failure';
                }
            }

            if ($clearOpcode) {
                if (function_exists('opcache_reset') && opcache_reset()) {
                    $message .= ' Opcode Cache: success';
                } elseif (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '<') && apc_clear_cache('opcode')) {
                    $message .= ' Opcode Cache: success';
                }
                else {
                    $success = false;
                    $message .= ' Opcode Cache: failure';
                }
            }
        }

        return array('success' => $success, 'message' => $message);
    }
}
