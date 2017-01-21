<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Combi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class WorkerListener
{
    use ContainerAwareTrait;

    private $workers;

    public function __construct(array $workers = [])
    {
        $this->workers = $workers;
    }

    public function onMicroserviceStart(Event $event)
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');
        $pids = [];

        foreach ($this->workers as $worker) {
            $process = new Process(
                sprintf(
                    'php ../app/console %s %s --env=%s',
                    BootstrapServiceCommand::COMMAND_NAME,
                    $worker,
                    $kernel->getEnvironment()
                    )
            );
            $process->start();
            $pids[] = $process->getPid();
        }

        return $pids;
    }
}