<?php

namespace Cmobi\MicroserviceFrameworkBundle;

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
        // TODO: Implement handle() method.
    }
}