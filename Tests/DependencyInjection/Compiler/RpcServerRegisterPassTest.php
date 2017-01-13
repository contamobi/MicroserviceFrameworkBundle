<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\RpcServerRegisterPass;
use Cmobi\MicroserviceFrameworkBundle\Tests\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RpcServerRegisterPassTest extends BaseTestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('cmobi_msf.rpc_server_1', \StdClass::class)
            ->setTags(['cmobi.rpc_server'=> []]);

        $this->process($container);

        $this->assertTrue(count($container->getParameter('cmobi_msf.rpc_servers')) > 0);
    }

    protected function process(ContainerBuilder $container)
    {
        $rpcServerRegisterPass = new RpcServerRegisterPass();
        $rpcServerRegisterPass->process($container);
    }
}