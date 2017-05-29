<?php

namespace Cmobi\MicroserviceFrameworkBundle\Controller;

use Cmobi\MicroserviceFrameworkBundle\Healthcheck\HealthCheckerService;
use Cmobi\MicroserviceFrameworkBundle\Healthcheck\HealthServiceBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController extends Controller
{
    public function indexAction()
    {
        $this->get('cmobi_msf.healthchecker')->load();
        $status = $this->get('cmobi_msf.healthchecker')->getStatus();
        $httpStatus = Response::HTTP_OK;

        if ($status !== HealthCheckerService::STATUS_OK) {
           $httpStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        $services = [];

        /** @var HealthServiceBag $serviceBag */
        foreach ($this->get('cmobi_msf.healthchecker')->getServices() as $serviceBag) {
            $services[$serviceBag->getName()] = $serviceBag->getStatus();
        }

        return new JsonResponse([
            'status' => HealthCheckerService::STATUS[$status],
            'services' => $services
        ], $httpStatus);
    }
}