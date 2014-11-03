<?php

namespace Ornicar\ApcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrnicarApcExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['host'] && strncmp($config['host'], 'http', 4) !== 0) {
            $config['host'] = 'http://'.$config['host'];
        }
        $container->setParameter('ornicar_apc.host', $config['host'] ? trim($config['host'], '/') : false);
        $container->setParameter('ornicar_apc.web_dir', $config['web_dir']);
        $container->setParameter('ornicar_apc.mode', $config['mode']);
    }
}
