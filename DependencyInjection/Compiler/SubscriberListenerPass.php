<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Listener\SubscriberListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SubscriberListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $subscribers = [];

        $taggedServices = $container->findTaggedServiceIds('cmobi.subscriber');

        foreach ($taggedServices as $id => $tags) {
            $subscribers[] = $id;
        }
        $env = $container->getParameter('kernel.environment');
        $definition = new Definition(
            SubscriberListener::class,
            [
                'subscribers' => $subscribers,
                'env' => $env,
                'microserviceName' => $container->getParameter('cmobi_msf.microservice_name'),
                'processManager' => new Reference('cmobi_msf.process.manager')
            ]
        );
        $definition->addTag('kernel.event_listener', ['event' => 'microservice.start']);

        $container->setDefinition('cmobi_msf.subscriber_listener', $definition);
    }
}