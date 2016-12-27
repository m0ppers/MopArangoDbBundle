<?php

namespace Mop\ArangoDbBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('mop_arangodb');
        
        $rootNode
            ->children()
                ->scalarNode('default_connection')->defaultNull()->end()
                ->arrayNode('connections')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->defaultValue(8529)->end()
                            ->scalarNode('database')->defaultValue('_system')->end()
                            ->scalarNode('user')->defaultValue('root')->end()
                            ->scalarNode('password')->defaultValue('password')->end()
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
