<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Logger\LoggerRawFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LoggerRawFormatterPass implements CompilerPassInterface
{
    private $serviceName;

    public function __construct($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(
            LoggerRawFormatter::class,
            [
                'microservicename' => $this->serviceName
            ]);

        $container->setDefinition('cmobi_msf.logger.raw', $definition);
    }
}