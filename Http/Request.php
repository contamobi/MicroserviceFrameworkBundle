<?php

namespace Cmobi\MicroserviceFrameworkBundle\Http;

use Cmobi\MicroserviceFrameworkBundle\Transport\MsfRequestInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class Request extends HttpRequest implements MsfRequestInterface
{
}