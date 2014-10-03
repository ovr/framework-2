<?php
namespace Brainwave\Cache\Driver;

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

use \Brainwave\Cache\Tag\TaggableStore;
use \Brainwave\Cache\Driver\Interfaces\DriverInterface;

/**
 * WincacheCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class WincacheCache extends TaggableStore implements DriverInterface
{
    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return extension_loaded('wincache');
    }

    /**
     * Create a new WinCache store.
     *
     * @param  string  $prefix
     * @return DriverInterface
     */
    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        $value = wincache_ucache_get($this->prefix.$key);

        if ($value !== false) {
            return $value;
        }
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function set($key, $value, $minutes)
    {
        wincache_ucache_set($this->prefix.$key, $value, $minutes * 60);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer   $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return wincache_ucache_inc($this->prefix.$key, $value);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer   $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return wincache_ucache_dec($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        return $this->set($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return void
     */
    public function forget($key)
    {
        wincache_ucache_delete($this->prefix.$key);
    }

    /**
     * [getMultiple description]
     *
     * @param  array $keys
     * @return array
     */
    public function getMultiple($keys)
    {
        //todo
    }

    /**
     * [setMultiple description]
     *
     * @param  array      $keys
     * @param  null       $ttl
     * @return array|bool
     */
    public function setMultiple($keys, $ttl = null)
    {
        return $this->set($keys, null, $tll);
    }

    /**
     * [removeMultiple description]
     *
     * @param  array      $keys
     * @return array|void
     */
    public function removeMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        wincache_ucache_clear();
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
