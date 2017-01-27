<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Cmobi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

class RpcServerListener
{
    use ContainerAwareTrait;

    private $processManager;
    private $servers;
    private $microserviceName;
    private $env;

    public function __construct(array $servers = [], $env, $microserviceName, ProcessManager $processManager)
    {
        $this->processManager = $processManager;
        $this->servers = $servers;
        $this->microserviceName = $microserviceName;
        $this->env = $env;
    }

    public function onMicroserviceStart(Event $event)
    {
        foreach ($this->servers as $server) {
            $process = new Process(
                sprintf(
                    'php app/console %s %s --env=%s --microservice=%s',
                    BootstrapServiceCommand::COMMAND_NAME,
                    $server,
                    $this->env,
                    $this->microserviceName
                    )
            );
            $process->start();
            $this->getProcessManager()->add($process);
        }
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager()
    {
        return $this->processManager;
    }
}