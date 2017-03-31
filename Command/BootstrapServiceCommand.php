<?php

namespace Cmobi\MicroserviceFrameworkBundle\Command;

use Cmobi\RabbitmqBundle\Queue\QueueInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BootstrapServiceCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'cmobi:service:bootstrap';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Run single service')
            ->addArgument('service', InputArgument::REQUIRED, 'Symfony service name')
            ->addOption('microservice', 'mic', InputOption::VALUE_REQUIRED, 'Microservice name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceName = $input->getArgument('service');

        if (! $this->getContainer()->has($serviceName)) {
            $this->getContainer()->get('logger')->error(
                sprintf('%s [%s] - Service not found.',
                    self::COMMAND_NAME,
                    $serviceName
                ));

            return;
        }
        $queue = $this->getContainer()->get($serviceName);

        if (! $queue instanceof QueueInterface) {
            $this->getContainer()->get('logger')->error(
                sprintf('%s [%s] - Unsupported service.',
                    self::COMMAND_NAME,
                    $serviceName
                ));
            return;
        }
        try {
            $output->writeln(sprintf('Starting ...................: %s', $serviceName));
            $queue->start();
        } catch (\Exception $e) {
            $output->writeln(sprintf('error [%s] - [%s]', self::COMMAND_NAME, $e->getMessage()));
        }

        $output->writeln(sprintf('[%s %s] [%s %s] ................... Exiting Exiting', date('Y-m-d H:i:s'), self::COMMAND_NAME));
    }
}