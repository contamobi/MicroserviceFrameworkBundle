<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\Broker;

use Cmobi\MicroserviceFrameworkBundle\Broker\MessageHandler;
use Cmobi\MicroserviceFrameworkBundle\Controller\ControllerResolver;
use Cmobi\MicroserviceFrameworkBundle\Tests\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class MessageHandlerTest extends BaseTestCase
{
    public function testHandle()
    {
        $messageHandler = new MessageHandler($this->getControllerResolverMock(), $this->getRouterMock());

        $this->assertTrue(true);
    }

    /**
     * @return ControllerResolver
     */
    private function getControllerResolverMock()
    {
        return $this->getMockBuilder(ControllerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Router
     */
    private function getRouterMock()
    {
        return $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}