<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\LogDispatcherPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MicroserviceFrameworkExtension extends Extension
{
    private $container;
    private $config;

    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new YamlFileLoader($container, $fileLocator);
        $loader->load('microserviceframework.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $this->config = $this->processConfiguration($configuration, $configs);
        $this->container = $container;
        $this->registerLogger($configs[0]['log_path']);

        /* Compile and lock container */
        $container->compile();
    }

    /**
     * @param $path
     */
    public function registerLogger($path)
    {
        $logDispatcherPass = new LogDispatcherPass($path);
        $this->getContainer()->addCompilerPass($logDispatcherPass);
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        return $this->container;
    }
}