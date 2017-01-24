<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Cmobi\MicroserviceFrameworkBundle\Listener\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Process;

class ServiceLoader
{
    use ContainerAwareTrait;

    private $micName;
    private $processManager;

    public function __construct($microserviceName, ProcessManager $processManager)
    {
        $this->micName = $microserviceName;
        $this->processManager = $processManager;
    }

    public function run()
    {
        $jobs = [];
        $this->getContainer()->get('event_dispatcher')->dispatch('microservice.start');

        /** @var \SplObjectStorage $processList */
        $processList = $this->getContainer()->get('cmobi_msf.process.manager')->getProcessList();

        foreach ($processList as $process) {

            if ($process instanceof Process) {
                $serviceName  = $this->extractServiceName($process->getCommandLine());
                $jobs[$serviceName][] = $process->getPid();
            }
        }

        return $jobs;
    }

    /**
     * @param $commandLine
     * @return string
     */
    public function extractServiceName($commandLine)
    {
        $command = explode(' ', $commandLine);
        $job = $command[3];
        $pos = strrpos($job, '_');
        $serviceName = substr($job, 0, $pos);

        return $serviceName;
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager()
    {
        return $this->processManager;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}