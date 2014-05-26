<?php
namespace Brainwave\Support\Facades;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use Brainwave\Support\Facades;

/**
 * Route
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Route extends Facades
{
	protected static function getFacadeAccessor() { return 'router'; }

    public static function map()
    {
    	return call_user_func_array(array(self::$brainwave, 'map'), func_get_args());
    }

    public static function get()
    {
    	return call_user_func_array(array(self::$brainwave, 'get'), func_get_args());
    }

    public static function post()
    {
    	return call_user_func_array(array(self::$brainwave, 'post'), func_get_args());
    }

    public static function put()
    {
    	return call_user_func_array(array(self::$brainwave, 'put'), func_get_args());
    }

    public static function patch()
    {
    	return call_user_func_array(array(self::$brainwave, 'patch'), func_get_args());
    }

    public static function delete()
    {
    	return call_user_func_array(array(self::$brainwave, 'delete'), func_get_args());
    }

    public static function options()
    {
    	return call_user_func_array(array(self::$brainwave, 'options'), func_get_args());
    }

    public static function group()
    {
    	return call_user_func_array(array(self::$brainwave, 'group'), func_get_args());
    }

    public static function any()
    {
    	return call_user_func_array(array(self::$brainwave, 'any'), func_get_args());
    }

    public static function inject()
    {
        //TODO
    }
}
