<?php

namespace Cmobi\MicroserviceFrameworkBundle\Routing\Matcher\Dumper;

use Cmobi\MicroserviceFrameworkBundle\Routing\MethodCollection;

abstract class MatcherDumper
{
    private $methods;

    public function __construct(MethodCollection $method)
    {
        $this->methods = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
