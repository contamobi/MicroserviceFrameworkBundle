<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Cmobi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Cmobi\MicroserviceFrameworkBundle\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

class RpcServerListener implements ServiceListenerInterface
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
                    'php %s/console %s %s --env=%s --microservice=%s >> /proc/$$/fd/1',
                    $this->container->get('kernel')->getRootDir(),
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

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->servers;
    }
}
