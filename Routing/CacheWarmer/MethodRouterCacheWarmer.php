<?php

namespace Cmobi\MicroserviceFrameworkBundle\Routing\CacheWarmer;

use Cmobi\MicroserviceFrameworkBundle\Routing\MethodRouter;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;


class MethodRouterCacheWarmer implements CacheWarmerInterface
{
    protected $router;

    public function __construct(MethodRouter $router)
    {
        $this->router = $router;
    }

    public function warmUp($cacheDir)
    {
        if ($this->router instanceof WarmableInterface) {
            $this->router->warmUp($cacheDir);
        }
    }

    public function isOptional()
    {
        return true;
    }
}
