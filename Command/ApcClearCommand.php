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
            ->setName('apc:clear')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Find out type of clean up
        $clearOpcode = $input->getOption('opcode') || !$input->getOption('user');
        $clearUser = $input->getOption('user') || !$input->getOption('opcode');

        if ($clearOpcode && !$clearUser) {
            $clearType = 'opcode';
        } else if (!$clearOpcode && $clearUser) {
            $clearType = 'user';
        } else if ($clearOpcode && $clearUser) {
            $clearType = 'the entire';
        }

        $output->writeLn(sprintf('Clearing <info>%s</info> APC cache', $clearType));

        $webDir = $this->getContainer()->getParameter('ornicar_apc.web_dir');

        if (!is_dir($webDir)) {
            throw new \RuntimeException(sprintf('Web dir does not exist "%s"', $webDir));
        }

        if (!is_writable($webDir)) {
            throw new \RuntimeException(sprintf('Web dir is not writable "%s"', $webDir));
        }

        // Generate random file name
        $filename = md5(uniqid().mt_rand(0, 9999999).php_uname()).'.php';
        $file = $webDir.'/'.$filename;

        // Write to new file from template
        $templateFile = __DIR__.'/../Resources/template.tpl';
        $template = file_get_contents($templateFile);
        $code = strtr($template, array(
            '%user%' => var_export($clearUser, true),
            '%opcode%' => var_export($clearOpcode, true)
        ));

        if (false === @file_put_contents($file, $code)) {
            throw new \RuntimeException(sprintf('Unable to write "%s"', $file));
        }

        // Clear cache
        $url = $this->getContainer()->getParameter('ornicar_apc.host').'/'.$filename;
        $headers = get_headers($url);

        if (false === $headers) {
            unlink($file);
            throw new \RuntimeException(
                sprintf('Unable to read "%s". Does the host resolve locally?', $url)
            );
        }

        // Check if everything went ok
        $responseCode = explode(' ', $headers[0]);
        $responseCode = $responseCode[1];

        if ($responseCode !== '200') {
            $output->writeLn('APC cache could not be cleared.');
        }

        unlink($file);
    }
}
