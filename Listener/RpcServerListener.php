<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Cmobi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

class RpcServerListener
{
    use ContainerAwareTrait;

    private $servers;
    private $env;

    public function __construct(array $servers = [], $env)
    {
        $this->servers = $servers;
        $this->env = $env;
    }

    public function onMicroserviceStart(Event $event)
    {
        $pids = [];

        foreach ($this->servers as $server) {
            $process = new Process(
                sprintf(
                    'php ../app/console %s %s --env=%s',
                    BootstrapServiceCommand::COMMAND_NAME,
                    $server,
                    $this->env
                    )
            );
            $process->start();
            $pids[] = $process->getPid();
        }

        return $pids;
    }
}