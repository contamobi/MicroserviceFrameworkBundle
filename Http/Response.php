<?php

namespace  Cmobi\MicroserviceFrameworkBundle\Http;

use Cmobi\MicroserviceFrameworkBundle\Transport\MsfResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Response extends HttpResponse implements MsfResponseInterface
{
}