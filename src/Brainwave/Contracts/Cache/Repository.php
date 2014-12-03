<?php
namespace Brainwave\Contracts\Cache;

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
 * Repository
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Repository
{
    /**
     * Fetch a stored variable from the cache
     *
     * @param string $key The key used to store the value
     *
     * @return mixed The stored variable
     */
    public function get($key);

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return void
     */
    public function forget($key);

    /**
     * Store variable in the cache
     *
     * @param string  $key     The key to use to set the value
     * @param mixed   $value   The variable to set
     * @param integer $minutes
     *
     * @return void
     */
    public function set($key, $value, $minutes);

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string   $key
     * @param integer  $minutes
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function remember($key, $minutes, \Closure $callback);

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string   $key
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function rememberForever($key, \Closure $callback);

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported();
}
