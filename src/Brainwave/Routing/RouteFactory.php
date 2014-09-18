<?php
namespace Brainwave\Routing;

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
            throw new \InvalidArgumentException("Invalid callable '$callable' specified.");
        }

        return array($matches[1], $matches[2]);
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
            "The specified '$service' route handler is an undefined service or 
            the controller could not be instantiated."
        );
    }
}
