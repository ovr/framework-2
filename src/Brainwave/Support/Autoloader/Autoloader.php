<?php
namespace Brainwave\Support\Autoloader;

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

/**
 * Autoloader
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Autoloader
{
    /**
     * The registered directories.
     *
     * @var array
     */
    protected static $directories = [];

    /**
     * Indicates if a Autoloader has been registered.
     *
     * @var bool
     */
    protected static $registered = false;

    /**
     * Load the given class file.
     *
     * @param  string  $class
     * @return boolean|null
     */
    public static function load($class)
    {
        $class = self::normalizeClass($class);

        foreach (self::getDirectories() as $directory) {
            if (file_exists($path = $directory.DIRECTORY_SEPARATOR.$class)) {
                require_once $path;
                return true;
            }
        }

        return false;
    }

    /**
     * Get the normal file name for a class.
     *
     * @param  string  $class
     * @return string
     */
    public static function normalizeClass($class)
    {
        if ($class[0] == '\\') {
            $class = substr($class, 1);
        }

        return str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class).'.php';
    }

    /**
     * Register the given class loader on the auto-loader stack.
     *
     * @return void
     */
    public static function register()
    {
        if (!self::$registered) {
            self::$registered = spl_autoload_register(
                array('\Brainwave\Support\Autoloader\Autoloader', 'load')
            );
        }
    }

    /**
     * Add directories to the class loader.
     *
     * @param  string|array  $directories
     * @return void
     */
    public static function addDirectories($directories)
    {
        self::$directories = array_unique(array_merge(self::$directories, (array) $directories));
    }

    /**
     * Remove directories from the class loader.
     *
     * @param  string|array  $directories
     * @return void
     */
    public static function removeDirectories($directories = null)
    {
        if (is_null($directories)) {
            self::$directories = [];
        } else {
            self::$directories = array_diff(self::$directories, (array) $directories);
        }
    }

    /**
     * Gets all the directories registered with the loader.
     *
     * @return array
     */
    public static function getDirectories()
    {
        return self::$directories;
    }
}
