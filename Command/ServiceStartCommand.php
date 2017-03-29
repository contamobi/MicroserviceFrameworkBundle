<?php

namespace Cmobi\MicroserviceFrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\Process\Process;

class ServiceStartCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'cmobi:microservice:run';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Run microservice daemon');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = new LockHandler(
            self::COMMAND_NAME
            . '.'
            . $this->getContainer()->getParameter('cmobi_msf.microservice_name')
        );

        if (! $lock->lock()) {
            $output->writeln('The command is already running in another process.');
            return;
        }
        $output->writeln(sprintf('[%s %s] ................... Starting', date('Y-m-d H:i:s'), self::COMMAND_NAME));
        $loader = $this->getContainer()->get('cmobi_msf.service.loader');
        $jobs = $loader->run();

        while ($job = array_pop($jobs)) {

            if ($job instanceof Process) {

                if ($job->isRunning()) {
                    $jobs[] = $job;
                }
                $this->getContainer()->get('logger')->info($job->getOutput());

                if ($job->getErrorOutput()) {
                    $this->getContainer()->get('logger')->error($job->getErrorOutput());
                }
                $job->clearOutput();
                $job->clearErrorOutput();
            }
        }

        $output->writeln(sprintf('[%s %s] ................... Finished', date('Y-m-d H:i:s'), self::COMMAND_NAME));
    }
}
