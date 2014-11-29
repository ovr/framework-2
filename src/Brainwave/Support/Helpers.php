<?php
namespace Brainwave\Support;

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

    use \Brainwave\Application\StaticalProxyManager;

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
     * Escape HTML entities in a string.
     *
     * @param  string $value
     *
     * @return string
     */
    public static function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Get the root Facade application instance.
     *
     * @param  string $make
     *
     * @return mixed
     */
    public static function app($make = null)
    {
        if (!is_null($make)) {
            return self::app()->make($make);
        }

        return StaticalProxyManager::getFacadeApplication();
    }

    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     *
     * @return string
     */
    public static function appPath($path = '')
    {
        return self::app('path').($path ? '/'.$path : $path);
    }

    /**
     * Get the path to the storage folder.
     *
     * @param  string $path
     *
     * @return string
     */
    public static function storagePath($path = '')
    {
        return self::app('path.storage').($path ? '/'.$path : $path);
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object $class
     *
     * @return string
     */
    public static function classBasename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param  string $search
     * @param  array  $replace
     * @param  string $subject
     *
     * @return string
     */
    public static function strReplaceArray($search, array $replace, $subject)
    {
        foreach ($replace as $value) {
            $subject = preg_replace('/'.$search.'/', $value, $subject, 1);
        }

        return $subject;
    }

    /**
     * Returns all traits used by a class, it's subclasses and trait of their traits
     *
     * @param  string $class
     *
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

    /**
     * A timing safe equals comparison.
     *
     * To prevent leaking length information, it is important
     * that user input is always used as the second parameter.
     * Based on code by Anthony Ferrara.
     * @see http://blog.ircmaxell.com/2012/12/seven-ways-to-screw-up-bcrypt.html
     *
     * @param string $safe The internal (safe) value to be checked
     * @param string $user The user submitted (unsafe) value
     *
     * @return boolean True if the two strings are identical.
     */
    public static function timingSafe($safe, $user)
    {
        /* Prevent issues if string length is 0. */
        $safe .= chr(0);
        $user .= chr(0);

        $safeLen = strlen($safe);
        $userLen = strlen($user);

        /* Set the result to the difference between the lengths. */
        $result = $safeLen - $userLen;

        for ($i = 0; $i < $userLen; $i++) {
            $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }

    /**
     * You can call private/protected methods with getClosure
     *
     * @param  object $object Class
     * @param  string $method private/protected method
     * @param  array  $args
     *
     * @return mixed
     */
    public static function callPrivateMethod($object, $method, array $args = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $closure = $reflection->getMethod($method)->getClosure($object);

        return call_user_func_array($closure, $args);
    }

    /**
     * Return the given object. Useful for chaining.
     *
     * @param  $object
     *
     * @return object
     */
    public static function with($object)
    {
        return $object;
    }
}
