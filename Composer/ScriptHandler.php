<?php

namespace Ornicar\ApcBundle\Composer;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as SymfonyScriptHandler;

class ScriptHandler extends SymfonyScriptHandler
{

    /**
     * Clears the APC/Opcache cache.
     *
     * @param $event CommandEvent A instance
     */
    public static function clearApcCache(CommandEvent $event)
    {
        $options = parent::getOptions($event);
        $consoleDir = parent::getConsoleDir($event, 'clear the apc cache');

        if (null === $consoleDir) {
            return;
        }

        $opcode = '';
        if (array_key_exists('ornicar-apc-opcode', $options))
        {
            $opcode .= ' --opcode';
        }

        $user = '';
        if (array_key_exists('ornicar-apc-user', $options))
        {
            $user .= ' --user';
        }

        $cli = '';
        if (array_key_exists('ornicar-apc-cli', $options))
        {
            $cli .= ' --cli';
        }

        $auth = '';
        if (array_key_exists('ornicar-apc-auth', $options))
        {
            $auth .= ' --auth '.  escapeshellarg($options['ornicar-apc-auth']);
        }

        static::executeCommand($event, $consoleDir, 'apc:clear'.$opcode.$user.$cli.$auth, $options['process-timeout']);
    }

}
