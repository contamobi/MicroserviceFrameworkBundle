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
        $configs = $this->processConfiguration($configuration, $configs);

        $this->registerLogger($container, $configs['log_path']);
        /* Compile and lock container */
        $container->compile();
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