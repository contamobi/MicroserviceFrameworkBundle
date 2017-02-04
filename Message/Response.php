<?php

namespace  Cmobi\MicroserviceFrameworkBundle\Message;

use Cmobi\MicroserviceFrameworkBundle\Transport\MsfResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Response implements MsfResponseInterface
{
    const VERSION = '2.0';

    public $id;
    public $attributes;
    public $error;

    public function __construct($id = null, array $attributes = [], $error = null)
    {
        $this->id = $id;
        $this->error = $error;
        $this->attributes = new ParameterBag($attributes);
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @return null|array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->attributes->get($key);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $rpc = [
            'id' => $this->id,
            'jsonrpc' => self::VERSION,
            'result' => $this->attributes->all()
        ];
        if ($this->error) {
            $rpc['error'] = $this->error;
            unset($rpc['result']);
        }
        return $rpc;
    }

    /**
     * @param array $response
     * @return $this
     */
    public function fromArray(array $response)
    {
        $this->validate($response);
        $this->id = $response['id'];

        if (isset($response['error'])) {
            $this->error = $response['error'];
        }
        if (isset($response['result'])) {
            $result = $response['result'];

            if (!is_array($result)) {
                $result = [$result];
            }
            $this->attributes = new ParameterBag($result);
        }
        return $this;
    }

    /**
     * @param array $response
     * @throws \Exception
     */
    public function validate(array $response)
    {
        if (
            (!isset($response['id']) || !isset($response['jsonrpc']))
            || (!isset($response['result']) && !isset($response['error']))
        ) {
            throw new \Exception('Failed parse response');
        }
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = new ParameterBag($attributes);
    }

    /**
     * @return ParameterBagInterface
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}