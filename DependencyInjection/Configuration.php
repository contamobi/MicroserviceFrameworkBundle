<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $rootNode = $tree->root('cmobi_msf');
        $rootNode
            ->children()
                ->scalarNode('log_path')->end()
            ->end();
        $rootNode->fixXmlConfig('rpc_server')
            ->children()
                ->arrayNode('rpc_servers')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('queue')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('connection')->defaultValue('default')->end()
                                    ->scalarNode('basic_qos')->defaultValue(1)->end()
                                    ->booleanNode('durable')->defaultTrue()->end()
                                    ->booleanNode('auto_delete')->defaultFalse()->end()
                                    ->variableNode('arguments')->defaultNull()->end()
                                ->end()
                            ->end()
                        ->scalarNode('service')->defaultValue('cmobi_msf.message.handler')->end()
                        ->scalarNode('jobs')->defaultValue(1)->end()
                        ->arrayNode('arguments')->canBeDisabled()->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        $rootNode->fixXmlConfig('worker')
            ->children()
                ->arrayNode('workers')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('queue')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('connection')->defaultValue('default')->end()
                                    ->scalarNode('basic_qos')->defaultValue(1)->end()
                                    ->variableNode('arguments')->defaultNull()->end()
                                ->end()
                            ->end()
                        ->scalarNode('service')->defaultValue('cmobi_msf.message.handler')->end()
                        ->scalarNode('jobs')->defaultValue(1)->end()
                        ->arrayNode('arguments')->canBeDisabled()->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        $rootNode->fixXmlConfig('subscriber')
            ->children()
                ->arrayNode('subscribers')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('queue')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('exchange')->end()
                                    ->scalarNode('connection')->defaultValue('default')->end()
                                    ->scalarNode('basic_qos')->defaultValue(1)->end()
                                    ->variableNode('arguments')->defaultNull()->end()
                                    ->scalarNode('exchange_type')->defaultValue('topic')->end()
                            ->end()
                        ->end()
                        ->scalarNode('service')->defaultValue('cmobi_msf.message.handler')->end()
                        ->scalarNode('jobs')->defaultValue(1)->end()
                        ->arrayNode('arguments')->canBeDisabled()->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        return $tree;
    }
}