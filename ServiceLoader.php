<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ServiceLoader
{
    use ContainerAwareTrait;

    public function run()
    {
        $this->getContainer()->get('debug.event_dispatcher')->dispatch('microservice.start');

        /** @var \SplObjectStorage $processList */
        $processList = $this->getContainer()->get('cmobi_msf.process.manager')->getProcessList();

    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}