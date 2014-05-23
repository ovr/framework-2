<?php namespace Brainwave\Routing;

use \Brainwave\Workbench\Workbench;

/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     2.3.5
 * @package     Slim
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Route Factory
 *
 * ...
 *
 * @package Slim
 * @author  Josh Lockhart
 * @since   3.0.0
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
            "The specified '$service' route handler is an undefined service or the controller could not be instantiated."
        );
    }
}
