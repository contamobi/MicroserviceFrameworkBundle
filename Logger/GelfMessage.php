<?php

namespace Cmobi\MicroserviceFrameworkBundle\Logger;

use Gelf\Message;

class GelfMessage extends Message
{
    protected $tag;

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    public function toArray()
    {
        $message = array(
            'version'       => $this->getVersion(),
            'host'          => $this->getHost(),
            'tag'           => $this->getTag(),
            'short_message' => $this->getShortMessage(),
            'full_message'  => $this->getFullMessage(),
            'level'         => $this->getSyslogLevel(),
            'timestamp'     => $this->getTimestamp(),
            'facility'      => $this->getFacility(),
            'file'          => $this->getFile(),
            'line'          => $this->getLine()
        );

        // Transform 1.1 deprecated fields to additionals
        // Will be refactored for 2.0, see #23
        if ($this->getVersion() == "1.1") {
            foreach (array('line', 'facility', 'file') as $idx) {
                $message["_" . $idx] = $message[$idx];
                unset($message[$idx]);
            }
        }

        // add additionals
        foreach ($this->getAllAdditionals() as $key => $value) {
            $message["_" . $key] = $value;
        }

        // return after filtering empty strings and null values
        return array_filter($message, function ($message) {
            return is_bool($message) || strlen($message);
        });
    }

}