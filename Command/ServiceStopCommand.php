<?php

namespace Cmobi\MicroserviceFrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

class ServiceStopCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'cmobi:microservice:stop';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Stop services')
            ->addArgument('service', InputArgument::OPTIONAL, 'Symfony service name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = new LockHandler(self::COMMAND_NAME);

        if (! $lock->lock()) {
            $output->writeln('The command is already running in another process.');
            return;
        }
        $output->writeln(sprintf('[%s %s] ................... Stopping', date('Y-m-d H:i:s'), self::COMMAND_NAME));
        $loader = $this->getContainer()->get('cmobi_msf.service.loader');
        $loader->stop();

        $output->writeln(sprintf('[%s %s] ................... Stopped!', date('Y-m-d H:i:s'), self::COMMAND_NAME));
    }
}