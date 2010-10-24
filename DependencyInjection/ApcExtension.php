<?php

namespace Bundle\ApcBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApcExtension extends Extension
{

    public function configLoad($config, ContainerBuilder $container)
    {
        if(isset($config['host'])) {
            $container->setParameter('apc.host', trim($config['host'], '/'));
        }
        else {
            throw new \InvalidArgumentException('You must provide the host (e.g. http/example.com)');
        }

        if(isset($config['web_dir'])) {
            $container->setParameter('apc.web_dir', $config['web_dir']);
        }
        else {
            throw new \InvalidArgumentException('You must provide the web_dir (e.g. %kernel.root_dir%/../web)');
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    public function getAlias()
    {
        return 'apc';
    }

}
