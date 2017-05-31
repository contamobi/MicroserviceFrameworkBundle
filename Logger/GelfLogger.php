<?php

namespace Cmobi\MicroserviceFrameworkBundle\Logger;

use Gelf\Logger;
use Gelf\PublisherInterface;

class GelfLogger extends Logger
{
    private $defaultTag;

    public function __construct(
        $defaultTag,
        PublisherInterface $publisher = null,
        $facility = null
    ) {
        $this->defaultTag = $defaultTag;
        parent::__construct($publisher, $facility);
    }

    /**
     * Initializes message-object
     *
     * @param mixed $level
     * @param mixed $message
     * @param array $context
     * @param null $tag
     * @return GelfMessage
     */
    protected function initMessage($level, $message, array $context, $tag = null)
    {
        $tag = is_null($tag) ? $this->defaultTag : $tag;
        // assert that message is a string, and interpolate placeholders
        $message = (string) $message;
        $context = $this->initContext($context);
        $message = self::interpolate($message, $context);

        // create message object
        $messageObj = new GelfMessage();
        $messageObj->setTag($tag);
        $messageObj->setLevel($level);
        $messageObj->setShortMessage($message);
        $messageObj->setFacility($this->facility);

        foreach ($context as $key => $value) {
            $messageObj->setAdditional($key, $value);
        }

        return $messageObj;
    }

    private static function interpolate($message, array $context)
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}