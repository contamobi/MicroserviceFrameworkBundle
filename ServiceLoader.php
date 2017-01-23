<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ServiceLoader
{
    use ContainerAwareTrait;

    public function run()
    {
        $this->getContainer()->get('debug.event_dispatcher')->dispatch('microservice.start');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}