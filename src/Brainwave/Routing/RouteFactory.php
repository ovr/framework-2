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

use \Pimple\Container;
use \Brainwave\Routing\Controller\ControllerDispatcher;

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
     *
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * Route factory callable
     *
     * @var \Closure
     */
    protected $routeResolver;

    /**
     * Controller factory callable
     *
     * @var \Closure
     */
    protected $controllerResolver;

    /**
     * RouteFactory
     *
     * @param Container $app
     * @param \Closure   $routeResolver
     * @param \Closure   $controllerResolver
     */
    public function __construct(Container $app, \Closure $routeResolver, \Closure $controllerResolver)
    {
        $this->app = $app;
        $this->routeResolver = $routeResolver;
        $this->controllerResolver = $controllerResolver;
    }

    /**
     * Create a new Route instance
     *
     * @param string $pattern
     * @param mixed $callable
     * @return \Brainwave\Routing\Interfaces\RouteInterface
     */
    public function make($pattern, $callable)
    {
        if ($this->referenceToController($callable)) {
            $callable = $this->makeControllerCallback($callable);
        }

        return call_user_func($this->routeResolver, $pattern, $callable);
    }

    /**
     * Determine if the callable is a reference to a controller.
     *
     * @param string    $callable
     * @return bool
     */
    protected function referenceToController($callable)
    {
        if (is_callable($callable)) {
            return false;
        }

        $pattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

        return is_string($callable) && preg_match($pattern, $callable);
    }

    /**
     * Define a callback that uses a given reference to a service or class name
     *
     * @param  string $callable
     * @return ControllerDispatcher
     */
    protected function makeControllerCallback($callable)
    {
        list($service, $method) = explode(':', $callable, 2);
        $factory = $this;

        return new ControllerDispatcher(function () use ($factory, $service) {
            return $factory->resolveControllerInstance($service);
        }, $method);
    }

    /**
     * Resolve the controller instance used by the route to handle the request
     *
     * @param  string $service
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function resolveControllerInstance($service)
    {
        if (isset($this->app[$service])) {
            return $this->app[$service];
        }

        if (class_exists($service)) {
            try {
                return call_user_func($this->controllerResolver, $service);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("The controller '$service' could not be instantiated.");
            }
        }

        throw new \InvalidArgumentException("The controller reference '$service' is undefined.");
    }
}
