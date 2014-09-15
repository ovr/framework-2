<?php
namespace Brainwave\Cache\Interfaces;

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

/**
 * CacheInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface CacheInterface
{
    /**
     * Invalidate all items in the cache
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     **/
    public function clear();

    /**
     * Delete an item
     *
     * @param mixed $key The key of the item to delete.
     * @return bool Returns TRUE on success or FALSE on failure.
     **/
    public function delete($key);

    /**
     * Checks if APC key exists
     *
     * @param mixed $key The key of the item to retrieve.
     * @return bool Returns TRUE if the key exists, otherwise FALSE.
     **/
    public function exists($key);

    /**
     * Fetch a stored variable from the cache
     *
     * @param mixed $key The key used to store the value
     * @return mixed The stored variable
     **/
    public function fetch($key);

    /**
     * Store variable in the cache
     *
     * @param mixed $key The key to use to store the value
     * @param mixed $var The variable to store
     * @param int $ttl The expiration time, defaults to 0.
     **/
    public function store($key, $var = null, $ttl = 0);

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     **/
    public static function isSupported();
}
