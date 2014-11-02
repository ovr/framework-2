<?php
namespace Brainwave\Workbench\Facades;

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

use \Brainwave\Workbench\StaticalProxyManager;

/**
 * Route
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class Route extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'router';
    }

    public static function map()
    {
        return call_user_func_array([self::$app['route'], 'map'], func_get_args());
    }

    public static function get()
    {
        return call_user_func_array([self::$app['route'], 'get'], func_get_args());
    }

    public static function post()
    {
        return call_user_func_array([self::$app['route'], 'post'], func_get_args());
    }

    public static function put()
    {
        return call_user_func_array([self::$app['route'], 'put'], func_get_args());
    }

    public static function patch()
    {
        return call_user_func_array([self::$app['route'], 'patch'], func_get_args());
    }

    public static function delete()
    {
        return call_user_func_array([self::$app['route'], 'delete'], func_get_args());
    }

    public static function options()
    {
        return call_user_func_array([self::$app['route'], 'options'], func_get_args());
    }

    public static function group()
    {
        return call_user_func_array([self::$app['route'], 'group'], func_get_args());
    }

    public static function any()
    {
        return call_user_func_array([self::$app['route'], 'any'], func_get_args());
    }

    public static function pattern(array $array)
    {
        $route = self::$app['route'];
        $route::setDefaultConditions($array);
    }
}
