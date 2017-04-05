<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Cmobi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Cmobi\MicroserviceFrameworkBundle\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

class WorkerListener implements ServiceListenerInterface
{
    use ContainerAwareTrait;

    private $processManager;
    private $workers;
    private $microserviceName;
    private $env;

    public function __construct(array $workers = [], $env, $microserviceName, ProcessManager $manager)
    {
        $this->processManager = $manager;
        $this->workers = $workers;
        $this->microserviceName = $microserviceName;
        $this->env = $env;
    }

    public function onMicroserviceStart(Event $event)
    {
        foreach ($this->workers as $worker) {
            $process = new Process(
                sprintf(
                    'php %s/console %s %s --env=%s --microservice=%s >> /proc/$$/fd/1',
                    $this->container->get('kernel')->getRootDir(),
                    BootstrapServiceCommand::COMMAND_NAME,
                    $worker,
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
        return $this->workers;
    }
}
