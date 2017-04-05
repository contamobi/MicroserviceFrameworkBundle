<?php

namespace Cmobi\MicroserviceFrameworkBundle\Listener;

use Cmobi\MicroserviceFrameworkBundle\Command\BootstrapServiceCommand;
use Cmobi\MicroserviceFrameworkBundle\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

class SubscriberListener implements ServiceListenerInterface
{
    use ContainerAwareTrait;

    private $processManager;
    private $subscribers;
    private $microserviceName;
    private $env;

    public function __construct(array $subscribers = [], $env, $microserviceName, ProcessManager $manager)
    {
        $this->processManager = $manager;
        $this->subscribers = $subscribers;
        $this->microserviceName = $microserviceName;
        $this->env = $env;
    }

    public function onMicroserviceStart(Event $event)
    {
        foreach ($this->subscribers as $subscriber) {
            $process = new Process(
                sprintf(
                    'php %s/console %s %s --env=%s --microservice=%s >> /proc/$$/fd/1',
                    $this->container->get('kernel')->getRootDir(),
                    BootstrapServiceCommand::COMMAND_NAME,
                    $subscriber,
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
        return $this->subscribers;
    }
}
