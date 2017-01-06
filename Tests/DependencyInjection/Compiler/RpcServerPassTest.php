<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\RpcServerPass;
use Cmobi\MicroserviceFrameworkBundle\Tests\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RpcServerPassTest extends BaseTestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('msf.rpc.test')
            ->setPublic(false);

        $this->process($container);

        $this->assertTrue($container->hasDefinition('msf.rpc.test'));
    }

    protected function process(ContainerBuilder $container)
    {
        $rpcServerPass = new RpcServerPass();
        $rpcServerPass->process($container);
    }
}