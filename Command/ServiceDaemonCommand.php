<?php

namespace Cmobi\MicroserviceFrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

class ServiceDaemonCommand extends ContainerAwareCommand
{
    private $name;

    protected function configure()
    {
        $this->name = 'cmobi:microservice:run';
        $this->setName($this->name)
            ->setDescription('Run microservice daemon');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = new LockHandler($this->name);

        if (! $lock->lock()) {
            $output->writeln('The command is already running in another process.');
            return;
        }
        $output->writeln(sprintf('[%s %s] ................... Running', date('Y-m-d H:i:s'), $this->name));
        $loader = $this->getContainer()->get('cmobi_msf.service.loader');
        $loader->run();

        $output->writeln(sprintf('[%s %s] ................... Exiting', date('Y-m-d H:i:s'), $this->name));
    }
}