<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\LogDispatcherPass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\RpcServerListenerPass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\RpcServerPass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\ServiceLoaderPass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\SubscriberPass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\WorkerListenerPass;
use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\WorkerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MicroserviceFrameworkExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new YamlFileLoader($container, $fileLocator);
        $loader->load('microserviceframework.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $configs = $this->processConfiguration($configuration, $configs);
        $container->setParameter('cmobi_msf.microservice_name', $configs['microservice_name']);
        $container->addCompilerPass(new ServiceLoaderPass());
        $this->registerLogger($container, $configs['log_path']);
        $this->loadConnections($container, $configs);
        $this->loadRpcServers($container, $configs);
        $this->loadWorkers($container, $configs);
        $container->addCompilerPass(new RpcServerListenerPass());
        $container->addCompilerPass(new WorkerListenerPass());
        /* Compile and lock container */
        $container->compile();
    }

    protected function loadConnections(ContainerBuilder $container, array $configs)
    {
        $factories = [];

        foreach ($configs['connections'] as $name => $connection) {
            $connectionClass = '%cmobi_msf.connection.class%';

            if ($connection['lazy']) {
                $connectionClass = '%cmobi_msf.lazy.connection.class%';
            }
            $definition = new Definition(
                '%cmobi_msf.connection.factory.class%',
                [
                    $connectionClass,
                    $connection,
                ]
            );
            $factoryName = sprintf('cmobi_msf.connection.factory.%s', $name);
            $container->setDefinition($factoryName, $definition);
            $factories[$name] = $factoryName;
        }
        $container->setParameter('cmobi_msf.connection.factories', $factories);
    }

    public function loadRpcServers(ContainerBuilder $container, array $configs)
    {
        foreach ($configs['rpc_servers'] as $server) {

            $container->addCompilerPass(new RpcServerPass(
                $server['queue']['name'],
                $server['queue']['connection'],
                $server['service'],
                $server['queue']['basic_qos'],
                $server['queue']['durable'],
                $server['queue']['auto_delete'],
                $server['queue']['arguments'],
                $server['jobs']
            ));
        }
    }

    public function loadWorkers(ContainerBuilder $container, array $configs)
    {
        foreach ($configs['workers'] as $worker) {
            $container->addCompilerPass(new WorkerPass(
                $worker['queue']['name'],
                $worker['queue']['connection'],
                $worker['service'],
                $worker['queue']['basic_qos'],
                $worker['queue']['arguments'],
                $worker['jobs']
            ));
        }
    }

    public function loadSubscribers(ContainerBuilder $container, array $configs)
    {
        foreach ($configs['subscribers'] as $subscriber) {
            $container->addCompilerPass(new SubscriberPass(
                $subscriber['queue']['exchange'],
                $subscriber['queue']['exchange_type'],
                $subscriber['queue']['name'],
                $subscriber['queue']['connection'],
                $subscriber['service'],
                $subscriber['queue']['basic_qos'],
                $subscriber['queue']['arguments'],
                $subscriber['jobs']
            ));
        }
    }

    /**
     * @param $path
     * @param ContainerBuilder $container
     */
    public function registerLogger(ContainerBuilder $container, $path)
    {
        $logDispatcherPass = new LogDispatcherPass($path);
        $container->addCompilerPass($logDispatcherPass);
    }
}