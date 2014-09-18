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

use \Brainwave\Routing\Route;
use \Brainwave\Routing\Exception\ControllerFrozenException;

/**
 * Controller
 *
 * A wrapper for a controller, mapped to a route.
 *
 * __call() forwards method-calls to Route, but returns instance of Controller
 * listing Route's methods below, so that IDEs know they are valid
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Controller
{
    private $route;
    private $routeName;
    private $isFrozen = false;

    /**
     * Constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Gets the controller's route.
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Gets the controller's route name.
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Sets the controller's route.
     *
     * @param string $routeName
     *
     * @return Controller $this The current Controller instance
     */
    public function bind($routeName)
    {
        if ($this->isFrozen) {
            throw new ControllerFrozenException(
                sprintf('Calling %s on frozen %s instance.', __METHOD__, __CLASS__)
            );
        }

        $this->routeName = $routeName;
        $this->route->setPattern($routeName);

        return $this;
    }

    public function __call($method, $arguments)
    {
        if (!method_exists($this->route, $method)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Method "%s::%s" does not exist.',
                    get_class($this->route),
                    $method
                )
            );
        }

        call_user_func_array(array($this->route, $method), $arguments);

        return $this;
    }

    /**
     * Freezes the controller.
     *
     * Once the controller is frozen, you can no longer change the route name
     */
    public function freeze()
    {
        $this->isFrozen = true;
    }

    public function generateRouteName($prefix)
    {
        if ($this->route->getPattern() == '/') {
            $pattern = $prefix.$this->route->getPattern();
        } else {
            $pattern = $prefix.$this->route->getPattern().'(/)';
        }

        $routeName = $pattern;
        $routeName = str_replace(array('|', '-'), '/', $routeName);
        $routeName = preg_replace('/[^a-z0-9A-Z_()+:\/.]+/', '', $routeName);

        return $routeName;
    }
}
