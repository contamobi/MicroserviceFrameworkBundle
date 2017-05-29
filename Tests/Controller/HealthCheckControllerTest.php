<?php

namespace Cmobi\MicroserviceFrameworkBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthCheckControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/health-check');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}