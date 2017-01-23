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
        $workers = [];

        if ($container->hasParameter('cmobi_msf.workers')) {
            $workers = $container->getParameter('cmobi_msf.workers');
        }
        $env = $container->getParameter('kernel.environment');
        $definition = new Definition(
            WorkerListener::class,
            [
                'workers' => $workers,
                'env' => $env
            ]
        );
        $definition->addTag('kernel.event_listener', ['event' => 'microservice.start']);

        $container->setDefinition('cmobi_msf.worker_listener', $definition);
    }
}