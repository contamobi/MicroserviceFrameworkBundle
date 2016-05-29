<?php

namespace Cmobi\MicroserviceFrameworkBundle\Routing\Matcher;

use Cmobi\MicroserviceFrameworkBundle\Transport\MsfRequestInterface;

interface MethodMatcherInterface
{
    /**
     * Tries to match METHOD with a set of routes.
     *
     * @param string $path
     * @return array
     */
    public function match($path);

    /**
     * Sets the request context.
     *
     * @param MsfRequestInterface $context The context
     */
    public function setContext(MsfRequestInterface $context);

    /**
     * Gets the request context.
     *
     * @return MsfRequestInterface The context
     */
    public function getContext();
}