<?php

namespace Cmobi\MicroserviceFrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceListCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'cmobi:microservice:list';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('List Running microservices');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = $this->getContainer()->get('cmobi_msf.service.loader');
        $process = $loader->listServices();

        $output->writeln(sprintf(
            '[%s %s] ................... Process list [pid  | service job]',
            date('Y-m-d H:i:s'), self::COMMAND_NAME)
        );
        $output->writeln('###################################################################################################');
        $output->writeln($process);
        $output->writeln('###################################################################################################');
    }
}