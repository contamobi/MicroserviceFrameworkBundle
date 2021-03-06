<?php

namespace Cmobi\MicroserviceFrameworkBundle\Routing\Matcher;

use Cmobi\MicroserviceFrameworkBundle\Routing\Method;
use Cmobi\MicroserviceFrameworkBundle\Routing\MethodCollection;
use Cmobi\MicroserviceFrameworkBundle\Transport\MsfRequestInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class MethodMatcher implements MethodMatcherInterface
{
    protected $context;
    protected $methods;
    protected $allow = [];

    public function __construct(MethodCollection $methods, MsfRequestInterface $context)
    {
        $this->methods = $methods;
        $this->context = $context;
    }

    public function match($path)
    {
        $this->allow = [];

        if ($ret = $this->matchCollection($path, $this->methods)) {
            return $ret;
        }

        if (0 < count($this->allow)) {
            throw new MethodNotAllowedException(array_unique($this->allow));
        }
        throw new ResourceNotFoundException(sprintf('No routes found for "%s".', $path));
    }

    public function matchCollection($path, MethodCollection $methods)
    {
        foreach ($methods as $name => $method) {

            if ($method->getName() !== $path) {
                continue;
            }

            return $this->getAttributes($method, $name, $method->getOptions());
        }

        return [];
    }

    protected function getAttributes(Method $method, $name, array $attributes)
    {
        $attributes['_method'] = $name;

        return $this->mergeDefaults($attributes, $method->getDefaults());
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(MsfRequestInterface $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    protected function mergeDefaults($params, $defaults)
    {
        foreach ($params as $key => $value) {
            if (!is_int($key)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}