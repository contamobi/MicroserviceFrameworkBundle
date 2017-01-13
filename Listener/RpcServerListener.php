<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;

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

    }
}