<?php
namespace Brainwave\Routing;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.4-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use Beainwave\Contracts\Routing\RouteStrategy as RouteStrategyContract;
use Brainwave\Contracts\Http\Response as ResponseContract;
use Brainwave\Http\Exception as HttpException;
use Brainwave\Http\JsonResponse;
use Brainwave\Http\Response;
use FastRoute\Dispatcher as FastDispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use Pimple\Conatiner;

/**
 * Dispatcher
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Dispatcher extends GroupCountBasedDispatcher implements RouteStrategyContract
{
    /**
     * Route strategy functionality
     */
    use RouteStrategyTrait;

    /**
     * @var \Pimple\Conatiner
     */
    protected $container;

    /**
     * @var array
     */
    protected $routes;

    /**
     * Constructor
     *
     * @param array $routes
     * @param array $data
     */
    public function __construct(Conatiner $container, array $routes, array $data)
    {
        $this->container = $container;
        $this->routes    = $routes;

        parent::__construct($data);
    }

    /**
     * Match and dispatch a route matching the given http method and uri
     *
     * @param string $method
     * @param string $uri
     *
     * @return array
     */
    public function dispatch($method, $uri)
    {
        $match = parent::dispatch($method, $uri);

        switch ($match[0]) {
            case FastDispatcher::NOT_FOUND:
                return $this->handleNotFound();

            case FastDispatcher::METHOD_NOT_ALLOWED:
                $allowed  = (array) $match[1];

                return $this->handleNotAllowed($allowed);

            case FastDispatcher::FOUND:
            default:
                $handler  = (isset($this->routes[$match[1]]['callback'])) ?
                            $this->routes[$match[1]]['callback'] :
                            $match[1];

                $strategy = $this->routes[$match[1]]['strategy'];
                $vars     = (array) $match[2];

                return $this->handleFound($handler, $strategy, $vars);
        }
    }

    /**
     * Handle dispatching of a found route
     *
     * @param string|\Closure                                     $handler
     * @param integer|\Brainwave\Contracts\Routing\CustomStrategy $strategy
     * @param array                                               $vars
     *
     * @return ResponseContract|array
     *
     * @throws RuntimeException
     */
    protected function handleFound($handler, $strategy, array $vars = [])
    {
        if (is_null($this->getStrategy())) {
            $this->setStrategy($strategy);
        }

        $controller = $this->isController($handler);

        // handle getting of response based on strategy
        if (is_integer($strategy)) {
            return $this->getResponseOnStrategy($controller, $strategy, $vars);
        }

        // we must be using a custom strategy
        return $strategy->dispatch($controller, $vars);
    }

    /**
     * Check if handler is a controller
     *
     * @param string|\Closure $handler
     *
     * @return \Brainwave\Contracts\Http\Response
     *
     * @throws \RuntimeException
     */
    public function isController($handler)
    {
        $controller = null;

        // figure out what the controller is
        if (($handler instanceof \Closure) || (is_string($handler) && is_callable($handler))) {
            $controller = $handler;
        }

        if (is_string($handler) && strpos($handler, '::') !== false) {
            $controller = explode('::', $handler);
        }

        // if controller method wasn't specified, throw exception.
        if (!$controller) {
            throw new \RuntimeException('A class method must be provided as a controller. ClassName::methodName');
        }

        return $controller;
    }

    /**
     * Handle getting of response based on strategy
     *
     * @param \Brainwave\Contracts\Http\Response $controller
     * @param int                                $strategy
     * @param array                              $vars
     *
     * @return ResponseContract
     */
    protected function getResponseOnStrategy($controller, $strategy, $vars)
    {
        switch ($strategy) {
            case RouteStrategyContract::URI_STRATEGY:
                $response = $this->handleUriStrategy($controller, $vars);
                break;
            case RouteStrategyContract::RESTFUL_STRATEGY:
                $response = $this->handleRestfulStrategy($controller, $vars);
                break;
            case RouteStrategyContract::REQUEST_RESPONSE_STRATEGY:
            default:
                $response = $this->handleRequestResponseStrategy($controller, $vars);
                break;
        }

        return $response;
    }

    /**
     * Invoke a controller action
     *
     * @param string|\Closure $controller
     * @param array           $vars
     *
     * @return ResponseContract
     */
    public function invokeController($controller, array $vars = [])
    {
        if (is_array($controller)) {
            $controller = [
                $this->container->get($controller[0]),
                $controller[1],
            ];
        }

        return call_user_func_array($controller, array_values($vars));
    }

    /**
     * Handles response to Request -> Response Strategy based routes
     *
     * @param string|\Closure $controller
     * @param array           $vars
     *
     * @return ResponseContract
     */
    protected function handleRequestResponseStrategy($controller, array $vars = [])
    {
        $response = $this->invokeController($controller, [
            $this->container['request'],
            $this->container['response'],
            $vars
        ]);

        if ($response instanceof ResponseContract) {
            return $response;
        }

        throw new \RuntimeException(
            'When using the Request -> Response Strategy your controller'
            .'must return an instance of [Brainwave\Contracts\Http\Response]'
        );
    }

    /**
     * Handles response to Restful Strategy based routes
     *
     * @param string|\Closure $controller
     * @param array           $vars
     *
     * @return array
     */
    protected function handleRestfulStrategy($controller, array $vars = [])
    {
        try {
            $response = $this->invokeController($controller, [
                $this->container['request'],
                $vars
            ]);

            if ($response instanceof JsonResponse) {
                return $response;
            }

            if (is_array($response) || $response instanceof \ArrayObject) {
                return new JsonResponse($response);
            }

            throw new \RuntimeException(
                'Your controller action must return a valid response for the Restful Strategy. '.
                'Acceptable responses are of type: [Array], [ArrayObject] and [Brainwave\Http\JsonResponse]'
            );
        } catch (HttpException\HttpException $e) {
            return $e->getJsonResponse();
        }
    }

    /**
     * Handles response to URI Strategy based routes
     *
     * @param string|\Closure $controller
     * @param array           $vars
     *
     * @return ResponseContract
     */
    protected function handleUriStrategy($controller, array $vars)
    {
        $response = $this->invokeController($controller, $vars);

        if ($response instanceof ResponseContract) {
            return $response;
        }

        try {
            $response = new Response($response);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to build Response from controller return value', 0, $e);
        }

        return $response;
    }

    /**
     * Handle a not found route
     *
     * @return ResponseContract|array
     */
    protected function handleNotFound()
    {
        $exception = new HttpException\NotFoundException();

        if ($this->getStrategy() === RouteStrategyContract::RESTFUL_STRATEGY) {
            return $exception->getJsonResponse();
        }

        throw $exception;
    }

    /**
     * Handles a not allowed route
     *
     * @param array $allowed
     *
     * @return array
     */
    protected function handleNotAllowed(array $allowed)
    {
        $exception = new HttpException\MethodNotAllowedException($allowed);

        if ($this->getStrategy() === RouteStrategyContract::RESTFUL_STRATEGY) {
            return $exception->getJsonResponse();
        }

        throw $exception;
    }
}
