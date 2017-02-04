<?php

namespace  Cmobi\MicroserviceFrameworkBundle\Transport;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

interface MsfResponseInterface
{
    /**
     * @return ParameterBagInterface
     */
    public function getAttributes();
}