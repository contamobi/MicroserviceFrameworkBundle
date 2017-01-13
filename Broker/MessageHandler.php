<?php

namespace Cmobi\MicroserviceFrameworkBundle\Broker;

use Cmobi\RabbitmqBundle\Queue\QueueServiceInterface;
use PhpAmqpLib\Message\AMQPMessage;

class MessageHandler implements QueueServiceInterface
{
    /**
     * @param AMQPMessage $message
     *
     * @return string
     */
    public function handle(AMQPMessage $message)
    {
        return '';
    }
}