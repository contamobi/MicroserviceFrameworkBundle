<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Cmobi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

class WorkerListener
{
    use ContainerAwareTrait;

    private $processManager;
    private $workers;
    private $env;

    public function __construct(array $workers = [], $env, ProcessManager $manager)
    {
        $this->processManager = $manager;
        $this->workers = $workers;
        $this->env = $env;
    }

    public function onMicroserviceStart(Event $event)
    {
        foreach ($this->workers as $worker) {
            $process = new Process(
                sprintf(
                    'php ../app/console %s %s --env=%s',
                    BootstrapServiceCommand::COMMAND_NAME,
                    $worker,
                    $this->env
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