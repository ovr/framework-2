<?php
namespace Brainwave\Contracts\Routing;

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

use \Brainwave\Contracts\Routing\Route as RouteContract;

/**
 * RouteContractr
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Router
{
    /**
     * @return RouteContractInterface|null
     */
    public function getCurrentRoute();

    /**
     * @return void
     */
    public function map(RouteContract $route);

    /**
     * @return string
     */
    public function urlFor($name, $params = []);

    /**
     * @return void
     */
    public function addNamedRoute($name, RouteContract $route);

    /**
     * @return boolean
     */
    public function hasNamedRoute($name);

    /**
     * @return RouteContractInterface|null
     */
    public function getNamedRoute($name);

    /**
     * @return \ArrayIterator
     */
    public function getNamedRoutes();
}
