<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Listener\RpcServerListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RpcServerListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $servers = [];

        $taggedServices = $container->findTaggedServiceIds('cmobi.rpc_server');

        foreach ($taggedServices as $id => $tags) {
            $servers[] = $id;
        }
        $env = $container->getParameter('kernel.environment');
        $definition = new Definition(
            RpcServerListener::class,
            [
                'servers' => $servers,
                'env' => $env,
                'processManager' => new Reference('cmobi_msf.process.manager')
            ]
        );
        $definition->addTag('kernel.event_listener', ['event' => 'microservice.start']);

        $container->setDefinition('cmobi_msf.rpc_server_listener', $definition);
    }
}