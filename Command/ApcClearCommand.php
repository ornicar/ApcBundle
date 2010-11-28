<?php

namespace Bundle\ApcBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Loads initial data
 */
class ApcClearCommand extends BaseCommand
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
        $clearOpcode = $input->getOption('opcode') || !$input->getOption('user');
        $clearUser = $input->getOption('user') || !$input->getOption('opcode');

        $webDir = $this->container->getParameter('apc.web_dir');
        if(!is_dir($webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir does not exist "%s"', $webDir));
        }
        if(!is_writable($webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir is not writable "%s"', $webDir));
        }
        $filename = md5(uniqid().mt_rand(0, 9999999).php_uname()).'.php';
        $file = $webDir.'/'.$filename;

        $templateFile = __DIR__.'/../Resources/template.tpl';
        $template = file_get_contents($templateFile);
        $code = strtr($template, array(
            '%user%' => var_export($clearUser, true),
            '%opcode%' => var_export($clearOpcode, true)
        ));

        file_put_contents($file, $code);

        $url = $this->container->getParameter('apc.host').'/'.$filename;
        $result = file_get_contents($url);
        $result = json_decode($result, true);

        unlink($file);

        if(!empty($result['success'])) {
            $output->writeLn($result['message']);
        } else {
            throw new \RuntimeException('Failed.');
        }
    }
}
