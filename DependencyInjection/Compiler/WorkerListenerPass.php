<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Listener\WorkerListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class WorkerListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(
            WorkerListener::class,
            [
                'workers' => $container->getParameter('cmobi_msf.workers')
            ]
        );
        $definition->addMethodCall('setContainer', [$container]);
        $definition->addTag('kernel.event_listener', ['event' => 'microservice.start']);

        $container->setDefinition('cmobi_msf.worker_listener', $definition);
    }
}