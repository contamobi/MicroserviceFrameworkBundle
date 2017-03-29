<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Cmobi\MicroserviceFrameworkBundle\Listener\RpcServerListener;
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
                //$serviceName  = $this->extractServiceName($process->getCommandLine());
                $jobs[] = $process;
            }
        }

        return $jobs;
    }

    public function stop()
    {
        $getProcess = new Process(sprintf("ps a | grep %s | grep -v grep | awk {'print $1'}", $this->micName));

        $getProcess->run(function ($type, $buffer) {

            if (Process::ERR === $type) {
                throw new \Exception('Failed list process with error: ' . $buffer);
            } else {
                $pids = implode(' ', explode(PHP_EOL, $buffer));
                $killProcess = new Process(sprintf('kill -9 %s', $pids));

                $killProcess->run(function ($type, $buffer) use ($pids) {

                    if (Process::ERR === $type) {
                        throw new \Exception(sprintf(
                            'Failed kill process with pids [%s] and with error [%s]',
                            $pids,
                            $buffer
                        ));
                    }
                });
            }
        });
    }

    public function status($serviceName = null)
    {
        $services = [];

        /** @var RpcServerListener $rpc */
        $rpc = $this->getContainer()->get('cmobi_msf.rpc_server_listener');
        array_merge($services, $rpc);
        $workers = $this->getContainer()->get('cmobi_msf.worker_listener');
        array_merge($services, $workers);
        $subscribers = $this->getContainer()->get('cmobi_msf.subscriber_listener');
        array_merge($services, $subscribers);

        if (! is_null($serviceName)) {

            if (! in_array($serviceName, $services)) {
                throw new \Exception(sprintf('Service [%s] not found.', $serviceName));
            }

        }
    }

    public function listServices()
    {
        $listProcess = new Process(sprintf(
            "ps a | grep %s | grep -v grep | awk {'print $1\" \"$8'}",
            $this->micName
        ));
        $listProcess->start();

        while($listProcess->isRunning()) {
            continue;
        }

        return $listProcess->getOutput();
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