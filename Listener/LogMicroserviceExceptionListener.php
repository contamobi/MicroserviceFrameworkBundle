<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Cmobi\MicroserviceFrameworkBundle\Exception\MicroserviceException;
use Cmobi\MicroserviceFrameworkBundle\Logger\LoggerService;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class LogMicroserviceExceptionListener
{
    private $env;
    private $logger;

    public function __construct($environment, LoggerService $logger)
    {
        $this->env = $environment;
        $this->logger = $logger;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof MicroserviceException) {
            if ($this->env !== 'prod') {
                $this->logger->exception($exception, true);
            } else {
                $this->logger->exception($exception);
            }
        }
    }
}
