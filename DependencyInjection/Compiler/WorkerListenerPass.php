<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Listener\WorkerListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class WorkerListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $workers = [];

        $taggedServices = $container->findTaggedServiceIds('cmobi.worker');

        foreach ($taggedServices as $id => $tags) {
            $workers[] = $id;
        }
        $env = $container->getParameter('kernel.environment');
        $definition = new Definition(
            WorkerListener::class,
            [
                'workers' => $workers,
                'env' => $env,
                'processManager' => new Reference('cmobi_msf.process.manager')
            ]
        );
        $definition->addTag('kernel.event_listener', ['event' => 'microservice.start']);

        $container->setDefinition('cmobi_msf.worker_listener', $definition);
    }
}