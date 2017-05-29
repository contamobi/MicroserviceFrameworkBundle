<?php

namespace Cmobi\MicroserviceFrameworkBundle\Healthcheck;

class HealthServiceBag
{
    const STATUS_OK = 'Success';
    const STATUS_FAIL = 'Fail';

    private $service;
    private $name;
    private $status;

    public function __construct(HealthCheckServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return HealthCheckServiceInterface
     */
    public function getService()
    {
        return $this->service;
    }
}