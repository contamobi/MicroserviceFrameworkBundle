<?php

namespace Cmobi\MicroserviceFrameworkBundle\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class StdoutHandler extends StreamHandler
{
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct('php://stdout', $level, $bubble);
    }
}