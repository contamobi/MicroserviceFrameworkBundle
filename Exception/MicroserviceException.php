<?php

namespace Cmobi\MicroserviceFrameworkBundle\Exception;

class MicroserviceException extends \Exception
{
    private $lineInfo;

    public function __construct($message, $code = 0, $method = 'null', $line = 'null', \Exception $previous = null)
    {
        $this->lineInfo = sprintf('%s:%s', $method, $line);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getLineInfo()
    {
        return $this->lineInfo;
    }
}