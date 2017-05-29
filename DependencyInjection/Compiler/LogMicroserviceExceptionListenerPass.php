<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Listener\JsonExceptionListener;
use Cmobi\MicroserviceFrameworkBundle\Listener\LogMicroserviceExceptionListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LogMicroserviceExceptionListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(LogMicroserviceExceptionListener::class, [
            'environment' => $container->getParameter("kernel.environment"),
            'logger' => new Reference('cmobi_msf.logger')
        ]);
        $definition->addTag(
            'kernel.event_listener',
            [
                'event' => 'kernel.exception',
                'method' => 'onKernelException',
                'priority' => 255
            ]);

        $container->setDefinition('cmobi_msf.microservice_exception_listener', $definition);
    }
}
