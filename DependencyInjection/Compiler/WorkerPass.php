<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\RabbitmqBundle\Queue\Queue;
use Cmobi\RabbitmqBundle\Transport\Worker\WorkerQueueBag;
use Cmobi\RabbitmqBundle\Transport\Worker\WorkerQueueCallback;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class WorkerPass implements CompilerPassInterface
{
    private $queueName;
    private $connectionName;
    private $serviceName;
    private $basicQos;
    private $arguments;
    private $jobs;

    public function __construct(
        $queueName,
        $connectionName,
        $serviceName,
        $basicQos,
        array $arguments,
        $jobs = 1
    )
    {
        $this->queueName = $queueName;
        $this->connectionName = $connectionName;
        $this->serviceName = $serviceName;
        $this->basicQos = $basicQos;
        $this->arguments = $arguments;
        $this->jobs = $jobs;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        for ($job = 0; $job < $this->jobs; $job++) {
            $definition = $this->buildDefinition($container);
            $definition->addTag('cmobi.worker');
            $jobName = sprintf('cmobi_msf.worker.%s_%s', $this->queueName, $job);
            $container->setDefinition($jobName, $definition);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @return Definition
     * @throws \Exception
     */
    protected function buildDefinition(ContainerBuilder $container)
    {
        $connection = $container->getDefinition('cmobi_msf.connection.manager');
        $serviceDefinition = new Reference($this->serviceName);
        $queueBagDefinition = new Definition(
            WorkerQueueBag::class,
            [
                'queueName' => $this->queueName,
                'basicQos' => $this->basicQos,
                'arguments' => $this->arguments
            ]
        );
        $queueCallbackDefinition = new Definition(
            WorkerQueueCallback::class,
            [
                'queueService' => $serviceDefinition
            ]
        );
        $definition = new Definition(
            Queue::class,
            [
                'connectionManager' => $connection,
                'queueBag' => $queueBagDefinition,
                'connectionName' => $this->connectionName,
                'callback' => $queueCallbackDefinition
            ]
        );

        return $definition;
    }
}