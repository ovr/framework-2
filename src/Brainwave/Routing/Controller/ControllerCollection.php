<?php
namespace Brainwave\Routing\Controller;

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
use \Brainwave\Routing\RouteCollection;
use \Brainwave\Routing\Controller\Controller;
use \Brainwave\Routing\Controller\ControllerCollection;

/**
 * ControllerCollection
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class ControllerCollection extends RouteCollection
{
    /**
     * All registred controllers
     *
     * @var array
     */
    protected $controllers = [];

    /**
     * Router instance
     *
     * @var \Brainwave\Routing\Router
     */
    protected $defaultRouter;

    /**
     * Controller route prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * ControllerCollection
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->defaultRouter = $container['router'];
    }

    /**
     * Get all controllers
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * Maps a pattern to a callable.
     *
     * You can optionally specify HTTP methods that should be matched.
     *
     * @return \Brainwave\Routing\Controller\Controller
     */
    public function match($args)
    {
        $route = parent::match($args);

        $this->controllers[] = $controller = new Controller($route);

        return $controller;
    }

    /**
     * Mounts controllers under the given route prefix.
     *
     * @param string               $prefix      The route prefix
     * @param ControllerCollection $controllers A ControllerCollection instance
     */
    public function mount($prefix, ControllerCollection $controllers)
    {
        $controllers->prefix = $prefix;

        $this->controllers[] = $controllers;
    }

    /**
     * Persists and freezes staged controllers.
     *
     * @param string $prefix
     * @return RouteCollection A RouteCollection instance
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

    /**
     * Call Route functions and controller function
     *
     * @param  string $method
     * @param  array $arguments
     * @return RouteCollection A RouteCollection instance
     */
    public function __call($method, array $arguments)
    {
        $defaultRoute = $this->container['routes.factory'];

        if (!method_exists($defaultRoute, $method)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Method "%s::%s" does not exist.',
                    get_class($defaultRoute),
                    $method
                )
            );
        }

        call_user_func_array(array($defaultRoute, $method), $arguments);

        foreach ($this->controllers as $controller) {
            if ($controller instanceof Controller) {
                call_user_func_array(array($controller, $method), $arguments);
            }
        }

        return $this;
    }
}
