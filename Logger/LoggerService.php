<?php

namespace Cmobi\MicroserviceFrameworkBundle\Logger;

use Cmobi\MicroserviceFrameworkBundle\Exception\MicroserviceException;
use Psr\Log\LoggerInterface;

class LoggerService
{
    const LOG_FORMAT = ' [KEY:%s] [%s] ';

    private $channel;

    public function __construct(LoggerInterface $logger)
    {
        $this->channel = $logger;
    }

    public function exception(MicroserviceException $exception, $strace = false)
    {
        $message = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'line_info' => $exception->getLineInfo()
        ];
        if ($strace) {
            $message['strace'] = $exception->getTraceAsString();
        }
        try {
            $message = json_encode($message);
        } catch (\Exception $e) {
            $message = serialize($message);
        }
        $this->channel->error($this->parseToLogFormat('exception', $message));
    }

    public function info($message, $key = 'default')
    {
        $this->channel->info($this->parseToLogFormat($key, $message));
    }

    public function emergency($message, $key = 'default')
    {
        $this->channel->emergency($this->parseToLogFormat($key, $message));
    }

    public function alert($message, $key = 'default')
    {
        $this->channel->alert($this->parseToLogFormat($key, $message));
    }

    public function critical($message, $key = 'default')
    {
        $this->channel->critical($this->parseToLogFormat($key, $message));
    }

    public function error($message, $key = 'default')
    {
        $this->channel->error($this->parseToLogFormat($key, $message));
    }

    public function warning($message, $key = 'default')
    {
        $this->channel->warning($this->parseToLogFormat($key, $message));
    }

    public function notice($message, $key = 'default')
    {
        $this->channel->notice($this->parseToLogFormat($key, $message));
    }

    public function debug($message, $key = 'default')
    {
        $this->channel->debug($this->parseToLogFormat($key, $message));
    }

    /**
     * @param $key
     * @param $content
     * @return string
     */
    public function parseToLogFormat($key, $content)
    {
        return sprintf(self::LOG_FORMAT, $key, $content);
    }

    /**
     * @return LoggerInterface
     */
    public function getChannel()
    {
        return $this->channel;
    }
}