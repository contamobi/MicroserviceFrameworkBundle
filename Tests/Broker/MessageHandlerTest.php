<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\Broker;

use Cmobi\MicroserviceFrameworkBundle\Broker\MessageHandler;
use Cmobi\MicroserviceFrameworkBundle\Tests\BaseTestCase;

class MessageHandlerTest extends BaseTestCase
{
    public function testHandle()
    {
        $messageHandler = new MessageHandler();

        $this->assertTrue(true);
    }
}