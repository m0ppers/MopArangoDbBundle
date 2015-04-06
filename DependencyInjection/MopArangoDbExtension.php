<?php

namespace Mop\ArangoDbBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MopArangoDbExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $isDebug = $container->getParameter('kernel.debug');
        $hasConnections = !empty($config['connections']);
        $hasDefaultConnection = !empty($config['default_connection']);
        $hasFosUserBundle = !empty($config['fos']);
        $logger = new Reference('mop_arangodb.data_collector');

        if ($isDebug) {
            $loader->load('logger.xml');
        }

        if ($hasConnections) {

            if (!$hasDefaultConnection) {
                $keys = array_keys($config['connections']);
                $config['default_connection'] = reset($keys);
                if (!empty($config['default_connection'])) {
                    $hasDefaultConnection = true;
                }
            }

            $loader->load('connection.xml');
            foreach ($config['connections'] as $name => $connection) {
                $arguments = array_merge(array($name), array_values($connection));

                if ($isDebug) {
                    $container
                        ->getDefinition('mop_arangodb.connection_factory')
                        ->addMethodCall(
                            'addLogger',
                            array($logger)
                        );
                }
                $container
                    ->setDefinition(
                        'mop_arangodb.connections.' . $name,
                        new DefinitionDecorator('mop_arangodb.connection')
                    )
                    ->setArguments($arguments);
            }
        }


        if ($hasDefaultConnection) {
            $container->setAlias(
                'mop_arangodb.default_connection',
                'mop_arangodb.connections.' . $config['default_connection']
            );
        }

        if ($hasFosUserBundle) {
            $container->setParameter('mop_arangodb.fos.collection', $config['fos']['collection']);
            $container->setAlias(
                'mop_arangodb.fos.connection',
                'mop_arangodb.connections.' . $config['fos']['connection']
            );

            $loader->load('fos.xml');
        }
    }
}
