<?php

namespace Cmobi\MicroserviceFrameworkBundle\Controller;

use Cmobi\MicroserviceFrameworkBundle\Transport\MsfRequestInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ControllerResolver
{
    use ContainerAwareTrait;

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function getController(MsfRequestInterface $request)
    {
        if (!$controller = $request->getAttributes()->get('_controller')) {
            if ($this->getContainer()->has('logger')) {
                $this->getContainer()->get('logger')->warning(
                    'Unable to look for the controller as the "_controller" parameter is missing.'
                );
            }
            return false;
        }

        if (is_array($controller)) {
            return $controller;
        }

        if (is_object($controller)) {
            if (method_exists($controller, '__invoke')) {
                return $controller;
            }
            throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', get_class($controller), $request->getPathInfo()));
        }

        if (false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return $this->instantiateController($controller);
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }
        $callable = $this->createController($controller);

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', $controller, $request->getPathInfo()));
        }
        return $callable;
    }

    public function getArguments(MsfRequestInterface $request, $controller)
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }
        return $this->doGetArguments($request, $controller, $r->getParameters());
    }

    protected function doGetArguments(MsfRequestInterface $request, $controller, array $parameters)
    {
        $attributes = $request->getAttributes()->all();
        $arguments = [];

        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)) {
                if (PHP_VERSION_ID >= 50600 && $param->isVariadic() && is_array($attributes[$param->name])) {
                    $arguments = array_merge($arguments, array_values($attributes[$param->name]));
                } else {
                    $arguments[] = $attributes[$param->name];
                }
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }
                throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
            }
        }
        return $arguments;
    }

    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }
        list($class, $method) = explode('::', $controller, 2);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }
        return [$this->instantiateController($class), $method];
    }

    protected function instantiateController($class)
    {
        $controller = new $class();

        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }
        return $controller;
    }
}