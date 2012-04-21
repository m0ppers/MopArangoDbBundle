<?php

namespace Mop\AvocadoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MopAvocadoExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        if (true === $container->getParameter('kernel.debug')) {
            $loader->load('logger.xml');
        }
        if (isset($config['connections'])) {
            if (!isset($config['default_connection'])) {
                $keys = array_keys($config['connections']);
                $config['default_connection'] = reset($keys);
            }
            $loader->load('connection.xml');
            foreach ($config['connections'] as $name => $connection) {
                $arguments = array_merge(array($name), array_values($connection));
                
                $logger = new Reference('mop_avocado.data_collector');
                if (true === $container->getParameter('kernel.debug')) {
                    $container->getDefinition('mop_avocado.connection_factory')->addMethodCall('addLogger', array($logger));
                }
                $container
                    ->setDefinition('mop_avocado.connections.'.$name, new DefinitionDecorator('mop_avocado.connection'))
                    ->setArguments($arguments);
            }
        }


        $container->setAlias('mop_avocado.default_connection', 'mop_avocado.connections.'.$config['default_connection']);
        if (isset($config['fos'])) {
            $container->setParameter('mop_avocado.fos.collection', $config['fos']['collection']);
            $container->setAlias('mop_avocado.fos.connection', 'mop_avocado.connections.'.$config['fos']['connection']);

            $loader->load('fos.xml');
        }
    }
}
