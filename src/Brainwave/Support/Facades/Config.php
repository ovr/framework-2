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
 * Config
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Config extends Facades
{
    protected static function getFacadeAccessor() { return self::$brainwave; }

    public static function setArray($name)
    {
        return self::$brainwave->config($name);
    }

    public static function set($name, $value = null)
    {
        return self::$brainwave->config($name, $value);
    }

    public static function get($name)
    {
        return self::$brainwave->config($name);
    }

    public static function loadFile($parser, $file)
    {
        return self::$brainwave->loadConfig($parser, $file);
    }
}
