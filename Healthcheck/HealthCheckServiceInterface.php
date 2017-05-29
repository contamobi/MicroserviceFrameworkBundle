<?php

namespace Cmobi\MicroserviceFrameworkBundle\Healthcheck;

interface HealthCheckServiceInterface
{
    public function check();

    public function getName();
}