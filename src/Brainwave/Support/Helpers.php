<?php
namespace Brainwave\Support;

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

/**
 * Helpers
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Helpers
{
    /**
     * Get the root Facade application instance.
     *
     * @param  string  $make
     * @return mixed
     */
    public static function app($make = null)
    {
        if (!is_null($make)) {
            return self::app()->make($make);
        }

        return \Brainwave\Workbench\StaticalProxyManager::getFacadeApplication();
    }

    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    public static function appPath($path = '')
    {
        return self::app('path').($path ? '/'.$path : $path);
    }

    /**
     * Get the path to the storage folder.
     *
     * @param   string  $path
     * @return  string
     */
    public static function storagePath($path = '')
    {
        return self::app('path.storage').($path ? '/'.$path : $path);
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    public static function classBasename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Returns all traits used by a class, it's subclasses and trait of their traits
     *
     * @param  string  $class
     * @return array
     */
    public static function classUsesRecursive($class)
    {
        $results = [];

        foreach (array_merge([$class => $class], class_parents($class)) as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}
