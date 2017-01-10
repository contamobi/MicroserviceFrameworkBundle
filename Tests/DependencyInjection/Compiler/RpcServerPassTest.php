<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Broker\MessageHandler;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\RpcServerPass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\MicroserviceFrameworkExtension;
use Cmobi\MicroserviceFrameworkBundle\Tests\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RpcServerPassTest extends BaseTestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $this->declareServiceMock($container);
        $container->register('cmobi_msf.message.handler', MessageHandler::class)
            ->setPublic(false);

        $this->process($container);

        $this->assertTrue($container->hasDefinition('msf.rpc.test'));
    }

    public function testProcessWithoutQueueServiceInterface()
    {
        $this->setExpectedException(
            \Exception::class,
            'Failed build rpc server, service can be instance of QueueServiceInterface'
        );
        $container = new ContainerBuilder();
        $this->declareServiceMock($container);
        $container->register('cmobi_msf.message.handler', \stdClass::class)
            ->setPublic(false);

        $this->process($container);
    }

    private function declareServiceMock(ContainerBuilder $container)
    {
        $container->registerExtension(new MicroserviceFrameworkExtension());
        $container->register('msf.rpc.test')
            ->setPublic(false);
        $container->register('cmobi_rabbitmq.connection.default')
            ->setPublic(false);
        $container->register('cmobi_msf.logger')
            ->setPublic(false);
    }

    protected function process(ContainerBuilder $container)
    {
        $rpcServerPass = new RpcServerPass(
            'test',
            'cmobi_rabbitmq.connection.default',
            'cmobi_msf.message.handler',
            1,
            false,
            true,
            []
        );
        $rpcServerPass->process($container);
    }
}