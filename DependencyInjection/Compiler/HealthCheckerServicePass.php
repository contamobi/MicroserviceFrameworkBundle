<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Healthcheck\HealthCheckerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class HealthCheckerServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('cmobi_msf.healthcheck');
        $definition = new Definition(HealthCheckerService::class);

        foreach ($services as $id => $tag) {
            $definition->addMethodCall('add', [new Reference($id)]);
        }
        $container->setDefinition('cmobi_msf.healthchecker', $definition);
    }
}