<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class JsonExceptionListener
 * @package ApiBundle\Listener
 */
class JsonExceptionListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $statusCode = null;

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
        $response = new JsonResponse($data);
        $event->setResponse($response);
    }
}