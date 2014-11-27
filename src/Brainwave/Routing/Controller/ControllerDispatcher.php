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

use \Brainwave\Http\Request;
use \Brainwave\Routing\Route;
use \Brainwave\Routing\Router;

/**
 * ControllerDispatcher
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class ControllerDispatcher
{
    /**
     * Resolve the controller instance to be dispatched
     * @var \Closure
     */
    protected $resolver;

    /**
     * Controller method used for dispatch
     * @var string
     */
    protected $method;

    /**
     * ControllerDispatcher
     *
     */
    public function __construct(\Closure $resolver, $method)
    {
        $this->resolver = $resolver;
        $this->method = $method;
    }

    /**
     * Invoke Route functions
     *
     * @return \Brainwave\Routing\Interfaces\RouteInterface
     */
    public function __invoke()
    {
        $args = func_get_args();

        $instance = call_user_func($this->resolver);

        return call_user_func_array(array($instance, $this->method), $args);
    }
}
