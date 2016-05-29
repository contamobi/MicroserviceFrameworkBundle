<?php

namespace Cmobi\MicroserviceFrameworkBundle;


use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\MicroserviceFrameworkExtension;

class MicroserviceFrameworkBundle extends Bundle
{
    public function getContainerExtension()
    {
        new MicroserviceFrameworkExtension();
    }
}