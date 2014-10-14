<?php
namespace Brainwave\Routing;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Http\Request;
use \Brainwave\Workbench\Workbench;

/**
 * RouteFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class RouteFactory
{
    /**
     * The application instance
     * @var \Brainwave\Workbench\Workbench
     */
    protected $app;

    /**
     * Route factory callable
     * @var \Closure
     */
    protected $resolver;

    /**
     * @var integer Counts the number of available routes.
     */
    private $routeCount = 0;

    /**
     * Constructor
     * @param  \Brainwave\Workbench\Workbench  $app
     * @param  \Closure   $factory
     */
    public function __construct(Workbench $app, \Closure $resolver)
    {
        $this->app = $app;
        $this->resolver = $resolver;
    }

    /**
     * Create a new Route instance
     * @param string $pattern
     * @param mixed $callable
     * @return \Brainwave\Routing\Interfaces\RouteInterface
     */
    public function make($pattern, $callable)
    {
        if (is_string($callable)) {
            $callable = $this->resolveHandlerCallback($callable);
        }

        return call_user_func($this->resolver, $pattern, $callable);
    }

    /**
     * Add GET|POST|PUT|PATCH|DELETE route
     *
     * Adds a new route to the router with associated callable. This
     * route will only be invoked when the HTTP request's method matches
     * this route's method.
     *
     * ARGUMENTS:
     *
     * First:       string  The URL pattern (REQUIRED)
     * In-Between:  mixed   Anything that returns TRUE for `is_callable` (OPTIONAL)
     * Last:        mixed   Anything that returns TRUE for `is_callable` (REQUIRED)
     *
     * The first argument is required and must always be the
     * route pattern (ie. '/books/:id').
     *
     * The last argument is required and must always be the callable object
     * to be invoked when the route matches an HTTP request.
     *
     * You may also provide an unlimited number of in-between arguments;
     * each interior argument must be callable and will be invoked in the
     * order specified before the route's callable is invoked.
     *
     * USAGE:
     *
     * Route::get('/foo'[, middleware, middleware, ...], callable);
     *
     * @param  array
     * @return Route
     */
    protected function mapRoute($args)
    {
        $pattern = array_shift($args);
        $callable = $this->app['resolver']->build(array_pop($args));

        $route = $this->make($pattern, $callable);

        $this->routeCount++;
        $route->setName((string)$this->routeCount);

        $this->app['router']->map($route);
        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        return $route;
    }

    /**
     * Add route without HTTP method
     * @return Route
     */
    public function map()
    {
        $args = func_get_args();
        return $this->mapRoute($args);
    }

    /**
     * Add GET route
     * @return Route
     * @api
     */
    public function get()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_GET, Request::METHOD_HEAD);
    }

    /**
     * Add POST route
     * @return Route
     * @api
     */
    public function post()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_POST);
    }

    /**
     * Add PUT route
     * @return Route
     * @api
     */
    public function put()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_PUT);
    }

    /**
     * Add PATCH route
     * @return Route
     * @api
     */
    public function patch()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_PATCH);
    }

    /**
     * Add DELETE route
     * @return Route
     * @api
     */
    public function delete()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_DELETE);
    }

    /**
     * Add OPTIONS route
     * @return Route
     * @api
     */
    public function options()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_OPTIONS);
    }

    /**
     * Add route for any HTTP method
     * @return Route
     * @api
     */
    public function any()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via("ANY");
    }

    /**
     * Define a callback that uses a given service name or class name
     *
     * @param  string $callable
     * @return \Closure
     */
    protected function resolveHandlerCallback($callable)
    {
        list($service, $method) = $this->parseCallable($callable);
        $factory = $this;

        return function () use ($factory, $service, $method) {

            $handler = $factory->resolveHandlerInstance($service);

            $args = func_get_args();

            return call_user_func_array(array($handler, $method), $args);
        };
    }

    /**
     *
     * @param  string $callable
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function parseCallable($callable)
    {
        if (!preg_match('!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!', $callable, $matches)) {
            throw new \InvalidArgumentException("Invalid callable '{$callable}' specified.");
        }

        return [$matches[1], $matches[2]];
    }

    /**
     *
     * @param  string $service
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function resolveHandlerInstance($service)
    {
        if (isset($this->app[$service])) {
            return $this->app[$service];
        }

        if (class_exists($service)) {
            try {
                return new $service;
            } catch (\Exception $e) {

            }
        }

        throw new \InvalidArgumentException(
            "The specified '{$service}' route handler is an undefined service or 
            the controller could not be instantiated."
        );
    }
}
