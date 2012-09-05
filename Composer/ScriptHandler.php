<?php

namespace Ornicar\ApcBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseScriptHandler;

class ScriptHandler extends BaseScriptHandler
{
    public static function clearApc($event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not clear the cache.'.PHP_EOL;
            return;
        }

        static::executeCommand($event, $appDir, 'apc:clear', $options['process-timeout']);
    }
}
