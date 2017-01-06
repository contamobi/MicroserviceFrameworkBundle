<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Cmobi\MicroserviceFrameworkBundle\DependencyInjection\MicroserviceFrameworkExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MicroserviceFrameworkBundle extends Bundle
{
    public function getContainerExtension()
    {
        new MicroserviceFrameworkExtension();
    }
}