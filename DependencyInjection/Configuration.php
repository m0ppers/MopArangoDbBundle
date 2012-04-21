<?php

namespace Mop\AvocadoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mop_avocado');
        
        $rootNode
            ->children()
                ->scalarNode('default_connection')->defaultNull()->end()
                ->arrayNode('connections')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('fos')
                    ->children()
                        ->scalarNode('connection')->end()
                        ->scalarNode('collection')->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
