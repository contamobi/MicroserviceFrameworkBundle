<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler\ConfigCachePass;
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
        $this->registerRouterConfiguration($configs[0]['router']);

        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(new ConfigCachePass());
        }
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
     * @param $resource
     */
    public function registerRouterConfiguration($resource)
    {
        $this->getContainer()->setParameter('cmobi_msf.router.resource', $resource);
        $this->getContainer()->setParameter(
            'method.cache_class_prefix',
            $this->getContainer()->getParameter('kernel.name')
            . ucfirst($this->getContainer()->getParameter('kernel.environment'))
        );
        $this->addClassesToCompile([
            'Cmobi\\MicroserviceFrameworkBundle\\Routing\\Matcher\\MethodMatcher',
            $this->getContainer()->findDefinition('cmobi_msf.router')->getClass(),
        ]);
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        return $this->container;
    }
}