<?php
namespace Brainwave\Middleware;

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

use \Brainwave\Contracts\Middleware\Middleware as MiddlewareContract;

/**
 * Middleware
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
abstract class Middleware implements MiddlewareContract
{
    /**
     * Reference to the primary Application instance
     *
     * @var \Brainwave\Application\Application
     */
    protected $container;

    /**
     * Reference to the next downstream middleware
     *
     * @var \Brainwave\Middleware\Middleware|\Brainwave\Application\Application
     */
    protected $next;

    /**
     * Set Application
     *
     * This method injects the primary Brainwave Application instance into
     * this middleware.
     *
     * @param  \Brainwave\Application\Application $containerlication
     */
    final public function setApplication($containerlication)
    {
        $this->container= $containerlication;
    }

    /**
     * Get Application
     *
     * This method retrieves the Application previously injected
     * into this middleware.
     *
     * @return \Brainwave\Application\Application
     */
    final public function getApplication()
    {
        return $this->container;
    }

    /**
     * Set next middleware
     *
     * This method injects the next downstream middleware into
     * this middleware so that it may optionally be called
     * when Applicationropriate.
     *
     * @param \Brainwave\Application\Application|\Brainwave\Middleware\Middleware
     */
    final public function setNextMiddleware($nextMiddleware)
    {
        $this->next = $nextMiddleware;
    }

    /**
     * Get next middleware
     *
     * This method retrieves the next downstream middleware
     * previously injected into this middleware.
     *
     * @return Middleware
     */
    final public function getNextMiddleware()
    {
        return $this->next;
    }

    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     */
    abstract public function call();
}
