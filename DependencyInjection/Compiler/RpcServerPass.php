<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\RabbitmqBundle\Queue\QueueServiceInterface;
use Cmobi\RabbitmqBundle\Transport\Rpc\RpcQueueBag;
use Cmobi\RabbitmqBundle\Transport\Rpc\RpcServerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RpcServerPass implements CompilerPassInterface
{
    private $queueName;
    private $connectionName;
    private $serviceName;
    private $basicQos;
    private $durable;
    private $autoDelete;
    private $arguments;

    public function __construct(
        $queueName,
        $connectionName,
        $serviceName,
        $basicQos,
        $durable,
        $autoDelete,
        array $arguments
    )
    {
        $this->queueName = $queueName;
        $this->connectionName = $connectionName;
        $this->serviceName = $serviceName;
        $this->basicQos = $basicQos;
        $this->durable = $durable;
        $this->autoDelete = $autoDelete;
        $this->arguments = $arguments;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $connection = $container->getDefinition($this->connectionName);
        $logger = $container->getDefinition('cmobi_msf.logger');
        $service = $container->get($this->serviceName);
        $queueBag = new RpcQueueBag(
            $this->queueName,
            $this->basicQos,
            $this->durable,
            $this->autoDelete,
            $this->arguments
        );

        if (! $service instanceof QueueServiceInterface) {
            throw new \Exception('Failed build rpc server, service can be instance of QueueServiceInterface');
        }
        $definition = new Definition(
            RpcServerBuilder::class,
            [
                'connManager' => $connection,
                'logger' => $logger
            ]
        );
        $definition->addMethodCall('buildQueue', [
           $this->queueName,
            $service,
            $queueBag
        ]);

        $container->setDefinition(sprintf('cmobi_msf.rpc_server.%s', $this->queueName), $definition);
    }
}