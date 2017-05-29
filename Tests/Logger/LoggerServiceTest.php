<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\Logger;

use Cmobi\MicroserviceFrameworkBundle\Exception\MicroserviceException;
use Cmobi\MicroserviceFrameworkBundle\Logger\LoggerService;
use Cmobi\MicroserviceFrameworkBundle\Tests\BaseTestCase;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

class LoggerServiceTest extends BaseTestCase
{
    public function testGetLoggerDefaultChannel()
    {
        $this->assertInstanceOf(
            ConsoleHandler::class,
            $this->getContainer()->get('cmobi_msf.logger.default')->getHandlers()[0]
        );
    }

    public function testLoggerService()
    {
        $this->assertInstanceOf(LoggerService::class, $this->getContainer()->get('cmobi_msf.logger'));
    }

    public function testLogException()
    {
        $obj = new \StdClass();
        $obj->message = '';
        $service = new LoggerService($this->getLoggerMock($obj));
        $service->exception(new MicroserviceException('message from exception.'), true);

        $this->assertTrue((bool)preg_match('/message from exception/', $obj->message));
    }

    /**
     * @param \StdClass $obj
     * @return Logger
     */
    private function getLoggerMock(\StdClass $obj)
    {
        $mock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('error')
            ->willReturnCallback(function ($message) use ($obj) { $obj->message = $message; });

        return $mock;
    }
}