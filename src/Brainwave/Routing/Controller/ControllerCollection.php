<?php
namespace Brainwave\Routing\Controller;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Http\Request;
use \Brainwave\Routing\Route;
use \Brainwave\Routing\Router;
use \Brainwave\Routing\Controller\Controller;

/**
 * ControllerCollection
 *
 * Builds Brainwave controllers.
 *
 * It acts as a staging area for routes. You are able to set the route name
 * until flush() is called, at which point all controllers are frozen and
 * converted to a RouteCollection.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class ControllerCollection
{
    protected $controllers = [];
    protected $defaultRoute;
    protected $defaultRouter;

    /**
     * Constructor.
     */
    public function __construct(Route $defaultRoute, Router $defaultRouter)
    {
        $this->defaultRoute = $defaultRoute;
        $this->defaultRouter = $defaultRouter;
    }

    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * Maps a pattern to a callable.
     *
     * You can optionally specify HTTP methods that should be matched.
     *
     *
     * @return \Brainwave\Routing\Controller\Controller
     */
    public function match($args)
    {
        $pattern = array_shift($args);
        $to = array_pop($args);

        $route = clone $this->defaultRoute;
        $route->setPattern($pattern);
        $route->setCallable($to);

        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        $this->controllers[] = $controller = new Controller($route);

        return $controller;
    }

    /**
     * Add route without HTTP method
     * @return Controller
     */
    public function map()
    {
        $args = func_get_args();
        return $this->match($args);
    }

    /**
     * Add GET route
     * @return Route
     * @api
     */
    public function get()
    {
        $args = func_get_args();
        return $this->match($args)->via(Request::METHOD_GET, Request::METHOD_HEAD);
    }

    /**
     * Add POST route
     * @return Route
     * @api
     */
    public function post()
    {
        $args = func_get_args();
        return $this->match($args)->via(Request::METHOD_POST);
    }

    /**
     * Add PUT route
     * @return Route
     * @api
     */
    public function put()
    {
        $args = func_get_args();
        return $this->match($args)->via(Request::METHOD_PUT);
    }

    /**
     * Add PATCH route
     * @return Route
     * @api
     */
    public function patch()
    {
        $args = func_get_args();
        return $this->match($args)->via(Request::METHOD_PATCH);
    }

    /**
     * Add DELETE route
     * @return Route
     * @api
     */
    public function delete()
    {
        $args = func_get_args();
        return $this->match($args)->via(Request::METHOD_DELETE);
    }

    /**
     * Add OPTIONS route
     * @return Route
     * @api
     */
    public function options()
    {
        $args = func_get_args();
        return $this->match($args)->via(Request::METHOD_OPTIONS);
    }

    /**
     * Add route for any HTTP method
     * @return Route
     * @api
     */
    public function any()
    {
        $args = func_get_args();

        return $this->match($args)->via("ANY");
    }

    /**
     * Persists and freezes staged controllers.
     *
     * @param string $prefix
     */
    public function flush($prefix = '')
    {
        foreach ($this->controllers as $controller) {
            if (!$name = $controller->getRouteName()) {
                $name = $controller->generateRouteName($prefix);
                $controller->bind($name);
            }

            $this->defaultRouter->map($controller->getRoute());
        }
    }
}
