<?php
namespace Brainwave\Cache\Driver\Interfaces;

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
 * DriverInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface DriverInterface
{
    /**
     * Invalidate all items in the cache
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function flush();

    /**
     * Fetch a stored variable from the cache
     *
     * @param string $key The key used to store the value
     * @return mixed The stored variable
     */
    public function get($key);

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value);

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return void
     */
    public function forget($key);

    /**
     * Store variable in the cache
     *
     * @param mixed $key The key to use to set the value
     * @param mixed $value The variable to set
     */
    public function set($key, $value, $minutes);

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer   $value
     * @return int|bool
     */
    public function increment($key, $value = 1);

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer   $value
     * @return int|bool
     */
    public function decrement($key, $value = 1);

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported();

    /**
     * [getMultiple description]
     *
     * @param  array $keys
     * @return array
     */
    public function getMultiple($keys);

    /**
     * [setMultiple description]
     *
     * @param  array      $keys
     * @param  null       $ttl
     * @return array|bool
     */
    public function setMultiple($keys, $ttl = null);

    /**
     * [removeMultiple description]
     *
     * @param  array      $keys
     * @return array|void
     */
    public function removeMultiple($keys);

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix();
}
