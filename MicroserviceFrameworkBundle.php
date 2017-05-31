<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\HealthCheckerServicePass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\MicroserviceFrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MicroserviceFrameworkBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new HealthCheckerServicePass());
    }

    public function getContainerExtension()
    {
        return new MicroserviceFrameworkExtension();
    }
}