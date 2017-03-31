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
        $output->writeln(sprintf('[%s] ................... Starting', self::COMMAND_NAME));
        $loader = $this->getContainer()->get('cmobi_msf.service.loader');
        $jobs = $loader->run();

        while ($jobs) {

            $job = array_shift($jobs);

            if ($job instanceof Process) {

                if ($job->isRunning()) {
                    array_push($jobs, $job);
                }

                if ($job->getOutput()) {
                    $this->getContainer()->get('logger')->info($job->getOutput());
                    $job->clearOutput();
                }

                if ($job->getErrorOutput()) {
                    $this->getContainer()->get('logger')->error($job->getErrorOutput());
                    $job->clearErrorOutput();
                }
            }
        }
        $output->writeln(sprintf('[%s] ................... Finished', self::COMMAND_NAME));
    }
}
