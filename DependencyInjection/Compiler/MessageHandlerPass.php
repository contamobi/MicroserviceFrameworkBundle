<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Broker\MessageHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;


class MessageHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(MessageHandler::class, [
            'controllerResolver' => new Reference('cmobi_msf.controller.resolver')
        ]);
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);

        $container->setDefinition('cmobi_msf.message.handler', $definition);
    }
}