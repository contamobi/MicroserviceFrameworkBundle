<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

interface ServiceListenerInterface
{
    /**
     * @return array
     */
    public function getServices();
}