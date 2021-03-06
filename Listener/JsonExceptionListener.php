<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JsonExceptionListener
{
    private $env;

    public function __construct($environment)
    {
        $this->env = $environment;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        }

        $data = [
            'error' => [
                'exception_code' => $exception->getCode(),
                'http_code' => $statusCode,
                'message' => $exception->getMessage()
            ]
        ];

         if ($this->env !== 'prod') {
            $data['error']['strace'] = $exception->getTraceAsString();
        }
        $response = new JsonResponse($data);
        $event->setResponse($response);
    }
}
