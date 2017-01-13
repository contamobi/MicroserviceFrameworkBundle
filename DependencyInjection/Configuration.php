<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $rootNode = $tree->root('microservice_framework');
        $rootNode
            ->children()
                ->scalarNode('log_path')->end()
            ->end();

        return $tree;
    }
}