<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Logger\LoggerRawFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LoggerRawFormatterPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(LoggerRawFormatter::class);

        $container->setDefinition('cmobi_msf.logger_format.raw', $definition);
    }
}
