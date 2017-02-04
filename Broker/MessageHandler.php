<?php

namespace Cmobi\MicroserviceFrameworkBundle\Broker;

use Cmobi\MicroserviceFrameworkBundle\Controller\ControllerResolver;
use Cmobi\MicroserviceFrameworkBundle\Message\Request;
use Cmobi\MicroserviceFrameworkBundle\Message\Response;
use Cmobi\RabbitmqBundle\Queue\QueueServiceInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

class MessageHandler implements QueueServiceInterface
{
    private $controllerResolver;
    private $router;

    public function __construct(ControllerResolver $controllerResolver, Router $router)
    {
        $this->controllerResolver = $controllerResolver;
        $this->router = $router;
    }

    /**
     * @param AMQPMessage $message
     *
     * @return string
     */
    public function handle(AMQPMessage $message)
    {
        $responses = [];
        try {
            $body = $message->body;
            $requests = json_decode($body, true);
            $routes = $this->getRouter()->getRouteCollection()->all();

            foreach ($requests as $requestsArr) {

                if (! is_array($requestsArr)) {
                    throw new \Exception(sprintf('Invalid request [%s]', serialize($requestsArr)));
                }
                $request = new Request();
                $request->fromArray($requestsArr);
                $responses[] = $this->callMethod($routes, $request);
            }
        } catch (\Exception $e) {
            $response = [
                'id' => uniqid(),
                'jsonrpc' => Response::VERSION,
                'error' => $e->getMessage()
            ];
            $responses[] = $response;
        }

        return $responses;
    }

    /**
     * @TODO improve this method
     * @param array $routes
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function callMethod(array $routes, Request $request)
    {
        foreach ($routes as $route) {

            if ($route instanceof Route) {

                if ($route->hasOption('method')) {

                    if ($route->getOption('method') === $request->getMethod()) {
                        $response = new Response($request->getId());
                        try {
                            $controller = $this->getControllerResolver()->getController($request);
                            $arguments = $this->getControllerResolver()->getArguments($request, $controller);
                            $origResponse = call_user_func($controller, $arguments);

                            if (! $origResponse instanceof HttpResponse) {
                                $response->setError(sprintf('Invalid response [%s]', serialize($origResponse)));
                            }
                            $response->fromArray([
                                'id' => $request->getId(),
                                'result' => $origResponse->getContent(),
                                'error' => $response->getError()
                            ]);
                        } catch (\Exception $e) {
                            $response->setError(sprintf('Failed call method [%s]', $e->getMessage()));
                        }
                        return $response->toArray();
                    }
                }
            }
        }
        throw new \Exception('Method not found.');
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return ControllerResolver
     */
    public function getControllerResolver()
    {
        return $this->controllerResolver;
    }
}