<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Listener\RpcServerListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RpcServerListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $servers = [];

        if ($container->hasParameter('cmobi_msf.rpc_servers')) {
            $servers = $container->getParameter('cmobi_msf.rpc_servers');
        }
        $env = $container->getParameter('kernel.environment');
        $definition = new Definition(
            RpcServerListener::class,
            [
                'servers' => $servers,
                'env' => $env
            ]
        );
        $definition->addTag('kernel.event_listener', ['event' => 'microservice.start']);

        $container->setDefinition('cmobi_msf.rpc_server_listener', $definition);
    }
}