<?php

namespace Cmobi\MicroserviceFrameworkBundle\Command;

use Cmobi\RabbitmqBundle\Queue\QueueBuilderInterface;
use Cmobi\RabbitmqBundle\Queue\QueueInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BootstrapServiceCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'cmobi:service:bootstrap';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Run single service')
            ->addArgument('service', InputArgument::REQUIRED, 'Symfony service name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceName = $input->getArgument('service');

        if (! $this->getContainer()->has($serviceName)) {
            $this->getContainer()->get('logger')->error(self::COMMAND_NAME . ' - Service not found.');

            return;
        }
        $serviceBuilder = $this->getContainer()->get($serviceName);

        if (! $serviceBuilder instanceof QueueBuilderInterface) {
            $this->getContainer()->get('logger')->error(self::COMMAND_NAME . ' - Unsupported service builder.');
            return;
        }
        try {
            /** @var QueueInterface $queue */
            $queue = $serviceBuilder->getQueue();
            $queue->start();
        } catch (\Exception $e) {
            $this->getContainer()->get('logger')->error(self::COMMAND_NAME . ' - ' . $e->getMessage());
        }

        $output->writeln(sprintf('[%s %s] ................... Exiting', date('Y-m-d H:i:s'), self::COMMAND_NAME));
    }
}