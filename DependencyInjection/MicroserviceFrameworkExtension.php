<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\LogDispatcherPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $config = $this->processConfiguration($configuration, $configs);
        $this->loadRpcServers($config);
        $this->registerLogger($configs[0]['log_path'], $container);

        /* Compile and lock container */
        $container->compile();
    }

    public function loadRpcServers(array $configs)
    {

    }

    /**
     * @param $path
     * @param ContainerBuilder $container
     */
    public function registerLogger($path, ContainerBuilder $container)
    {
        $logDispatcherPass = new LogDispatcherPass($path);
        $container->addCompilerPass($logDispatcherPass);
    }
}