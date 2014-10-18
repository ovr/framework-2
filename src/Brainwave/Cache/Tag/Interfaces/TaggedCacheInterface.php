<?php
namespace Brainwave\Cache\Tag\Interfaces;

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

/**
 * TaggedCacheInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface TaggedCacheInterface
{
    /**
     * Invalidate all items in the cache
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function flush();

    /**
     * Delete an item
     *
     * @param mixed $key The key of the item to delete.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function delete($key);

    /**
     * Checks if APC key exists
     *
     * @param mixed $key The key of the item to retrieve.
     * @return bool Returns TRUE if the key exists, otherwise FALSE.
     */
    public function exists($key);

    /**
     * Fetch a stored variable from the cache
     *
     * @param mixed $key The key used to store the value
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
     * @return void
     */
    public function set($key, $value, $minutes);

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer   $value
     * @return void
     */
    public function increment($key, $value = 1);

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer   $value
     * @return void
     */
    public function decrement($key, $value = 1);

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix();
}
