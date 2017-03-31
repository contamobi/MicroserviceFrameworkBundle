<?php

namespace Cmobi\MicroserviceFrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceStatusCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'cmobi:microservice:status';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Status of services')
            ->addArgument('service', InputArgument::OPTIONAL, 'Symfony service name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceName = null;

        if ($input->hasArgument('service')) {
            $serviceName = $input->getArgument('service');
        }
        $loader = $this->getContainer()->get('cmobi_msf.service.loader');
        $statusList = $loader->status($serviceName);

        $output->writeln(sprintf(
            '[%s] ................... Status [ service | status ]',
            self::COMMAND_NAME
        ));
        $output->writeln('###################################################################################################');
        $output->writeln($statusList);
        $output->writeln('###################################################################################################');
    }
}