<?php

namespace Ornicar\ApcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ornicar_apc');

        $rootNode
            ->children()
                ->scalarNode('host')->defaultFalse()->end()
                ->scalarNode('web_dir')->isRequired()->end()
                ->scalarNode('mode')->defaultValue('fopen')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
