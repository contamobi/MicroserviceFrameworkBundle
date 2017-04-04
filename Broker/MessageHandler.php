<?php

namespace Cmobi\MicroserviceFrameworkBundle\Broker;

use Cmobi\MicroserviceFrameworkBundle\Controller\ControllerResolver;
use Cmobi\MicroserviceFrameworkBundle\Message\Request;
use Cmobi\MicroserviceFrameworkBundle\Message\Response;
use Cmobi\RabbitmqBundle\Queue\QueueServiceInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

class MessageHandler implements QueueServiceInterface
{
    use ContainerAwareTrait;

    private $controllerResolver;

    public function __construct(ControllerResolver $controllerResolver)
    {
        $this->controllerResolver = $controllerResolver;
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

            if (! is_array($requests)) {
                throw new \Exception(sprintf('Invalid request [%s]', $body));
            }

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

        return json_encode($responses);
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
                        $content = [];
                        try {
                            $parameters = $this->getRouter()->match($route->getPath());
                            $parameters = [
                                '_controller' => $parameters['_controller'],
                                '_route' => $parameters['_route']
                            ];
                            array_merge($parameters, $request->getParams());
                            $request->attributes->add($parameters);
                            $controller = $this->getControllerResolver()->getController($request);
                            $arguments = $this->getControllerResolver()->getArguments($request, $controller);
                            $origResponse = call_user_func_array($controller, $arguments);

                            if (! $origResponse instanceof HttpResponse) {
                                $response->setError(sprintf('Invalid response [%s]', serialize($origResponse)));
                            } else {
                                $content = $origResponse->getContent();
                            }

                            if (is_string($content)) {
                                $content = json_decode($origResponse->getContent(), true);

                                if (! is_array($content)) {
                                    $content = $origResponse->getContent();
                                }
                            }
                            $response->fromArray([
                                'id' => $request->getId(),
                                'jsonrpc' => Response::VERSION,
                                'result' => $content,
                                'error' => $response->getError()
                            ]);
                        } catch (\Exception $e) {
                            if(is_object(json_decode($e->getMessage()))) {
                                $response->setError($e->getMessage());
                            } else {
                                $response->setError(sprintf('Failed call method [%s]', $e->getMessage()));
                            }
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
        return $this->getContainer()->get('router');
    }

    /**
     * @return ControllerResolver
     */
    public function getControllerResolver()
    {
        return $this->controllerResolver;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
