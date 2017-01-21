<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Combi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class RpcServerListener
{
    use ContainerAwareTrait;

    private $servers;

    public function __construct(array $servers = [])
    {
        $this->servers = $servers;
    }

    public function onMicroserviceStart(Event $event)
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');
        $pids = [];

        foreach ($this->servers as $server) {
            $process = new Process(
                sprintf(
                    'php ../app/console %s %s --env=%s',
                    BootstrapServiceCommand::COMMAND_NAME,
                    $server,
                    $kernel->getEnvironment()
                    )
            );
            $process->start();
            $pids[] = $process->getPid();
        }

        return $pids;
    }
}