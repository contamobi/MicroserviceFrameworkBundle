<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\Broker;

use Cmobi\MicroserviceFrameworkBundle\Healthcheck\HealthCheckerService;
use Cmobi\MicroserviceFrameworkBundle\Healthcheck\HealthCheckServiceInterface;
use Cmobi\MicroserviceFrameworkBundle\Tests\BaseTestCase;

class HealthCheckerServiceTest extends BaseTestCase
{
    public function testAddMultipleHealthServices()
    {
        $healthChecker = new HealthCheckerService();
        $healthChecker->add($this->getHealthServiceMock(1));
        $healthChecker->add($this->getHealthServiceMock(1));
        $healthChecker->add($this->getHealthServiceMock(1));
        $healthChecker->add($this->getHealthServiceMock(1));
        $healthChecker->add($this->getHealthServiceMock(1));

        $this->assertCount(5, $healthChecker->getServices());
    }

    public function testAddNotExpectedParameter()
    {
        $this->setExpectedException(\TypeError::class);
        $healthChecker = new HealthCheckerService();
        $healthChecker->add(new \StdClass());
    }

    public function testLoadServicesWithSuccessStatus()
    {
        $healthChecker = new HealthCheckerService();
        $healthChecker->add($this->getHealthServiceMock(1));
        $healthChecker->add($this->getHealthServiceMock(1));
        $healthChecker->load();

        $this->assertEquals(HealthCheckerService::STATUS_OK, $healthChecker->getStatus());
    }

    public function testLoadServicesWithDegradedStatus()
    {
        $healthChecker = new HealthCheckerService();
        $healthChecker->add($this->getHealthServiceMock(1));
        $healthChecker->add($this->getHealthServiceMock(0));
        $healthChecker->load();

        $this->assertEquals(HealthCheckerService::STATUS_DEGRADED, $healthChecker->getStatus());
    }

    public function testLoadServicesWIthErrorStatus()
    {
        $healthChecker = new HealthCheckerService();
        $healthChecker->add($this->getHealthServiceMock(0));
        $healthChecker->add($this->getHealthServiceMock(0));
        $healthChecker->load();

        $this->assertEquals(HealthCheckerService::STATUS_ERROR, $healthChecker->getStatus());
    }

    /**
     * @param $status
     * @return HealthCheckServiceInterface
     */
    protected function getHealthServiceMock($status)
    {
        $mock = $this->getMockBuilder(HealthCheckServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('check')
            ->willReturn($status);

        return $mock;
    }
}