<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests;

use Cmobi\MicroserviceFrameworkBundle\ProcessManager;
use Cmobi\MicroserviceFrameworkBundle\ServiceLoader;

class ServiceLoaderTest extends BaseTestCase
{
    public function testExtractServiceName()
    {
        $loader = new ServiceLoader('cmobi_test', $this->getProcessManagerMock());

        $name = $loader->extractServiceName(
            'php ../app/console cmobi:service:bootstrap cmobi.rpc_server.test_01 --env=dev'
        );

        $this->assertEquals('cmobi.rpc_server.test', $name);
    }

    /**
     * @return ProcessManager
     */
    protected function getProcessManagerMock()
    {
        $mock = $this->getMockBuilder(ProcessManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}