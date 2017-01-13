<?php

namespace Combi\MicroserviceFrameworkBundle\Command;

use Cmobi\RabbitmqBundle\Queue\QueueBuilderInterface;
use Cmobi\RabbitmqBundle\Queue\QueueInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BootstrapServiceCommand extends ContainerAwareCommand
{
    private $name;

    protected function configure()
    {
        $this->name = 'cmobi:service:bootstrap';
        $this->setName($this->name)
            ->setDescription('Run single service')
            ->addArgument('service', InputArgument::REQUIRED, 'Symfony service name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceName = $input->getArgument('service');

        if (! $this->getContainer()->has($serviceName)) {
            throw new \Exception('Service not found.');
        }
        $serviceBuilder = $this->getContainer()->get($serviceName);

        if (! $serviceBuilder instanceof QueueBuilderInterface) {
            throw new \Exception('Unsupported service builder.');
        }
        /** @var QueueInterface $queue */
        $queue = $serviceBuilder->getQueue();
        $queue->start();

        $output->writeln(sprintf('[%s %s] ................... Exiting', date('Y-m-d H:i:s'), $this->name));
    }
}