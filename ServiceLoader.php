<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Symfony\Component\EventDispatcher\EventDispatcher;

class ServiceLoader
{
    public function run()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch('microservice.start');
    }
}