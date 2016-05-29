<?php

namespace Cmobi\MicroserviceFrameworkBundle\Routing\Loader\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\ObjectRouteLoader;

/**
 * A route loader that executes a service to load the routes.
 *
 * This depends on the DependencyInjection component.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 */
class ServiceRouterLoader extends ObjectRouteLoader
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getServiceObject($id)
    {
        return $this->container->get($id);
    }
}
