<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RpcServerRegisterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = [];
        $taggedServices = $container->findTaggedServiceIds('cmobi.rpc_server');

        foreach ($taggedServices as $id => $tags) {
            $services[] = $id;
        }
        $container->setParameter('cmobi_msf.rpc_servers', $services);
    }
}