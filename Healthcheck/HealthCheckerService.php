<?php

namespace Cmobi\MicroserviceFrameworkBundle\Healthcheck;

class HealthCheckerService
{
    const STATUS_UNCHECKED = 0;
    const STATUS_OK = 1;
    const STATUS_DEGRADED = 2;
    const STATUS_ERROR = 3;

    const STATUS = [
        self::STATUS_UNCHECKED => 'UNCHECKED',
        self::STATUS_OK => 'OK',
        self::STATUS_DEGRADED => 'DEGRADED',
        self::STATUS_ERROR => 'ERROR'
    ];
    private $services;
    private $status;

    public function __construct()
    {
        $this->status = self::STATUS_UNCHECKED;
        $this->services = [];
    }

    public function load()
    {
        $this->status = self::STATUS_OK;
        $degraded = 0;

        /** @var HealthServiceBag $service */
        foreach ($this->services as $service) {

            $service->setStatus(HealthServiceBag::STATUS_OK);

            if (! $service->getService()->check()) {
                $service->setStatus(HealthServiceBag::STATUS_FAIL);
                $this->status = self::STATUS_DEGRADED;
                $degraded++;
            }
        }

        if (count($this->services) === $degraded && $degraded > 1) {
            $this->status = self::STATUS_ERROR;
        }
    }

    /**
     * @param HealthCheckServiceInterface $service
     * @return $this
     */
    public function add(HealthCheckServiceInterface $service)
    {
        $this->services[] = new HealthServiceBag($service);

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }
}