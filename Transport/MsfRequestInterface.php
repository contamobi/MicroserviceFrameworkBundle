<?php

namespace Cmobi\MicroserviceFrameworkBundle\Transport;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

interface MsfRequestInterface
{
    /**
     * @return ParameterBagInterface
     */
    public function getAttributes();
}