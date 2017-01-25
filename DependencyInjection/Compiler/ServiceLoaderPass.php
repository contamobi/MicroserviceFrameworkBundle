<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\ServiceLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ServiceLoaderPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $serviceName = $container->getParameter('cmobi_msf.microservice_name');
        $processManagerDefinition = $container->getDefinition('cmobi_msf.process.manager');
        $definition = new Definition(
            ServiceLoader::class,
            [
                'microserviceName' => $serviceName,
                'processManager' => $processManagerDefinition
            ]
        );
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);
        $container->setDefinition('cmobi_msf.service.loader', $definition);
    }
}