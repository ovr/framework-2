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
 * ApcCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class ApcCache extends TaggableStore implements DriverInterface
{
    /**
     * Indicates if APCu is supported.
     *
     * @var bool
     */
    protected $apcu = false;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new APC store.
     *
     * @param  string  $prefix
     * @return void
     */
    public function __construct($prefix = '')
    {
        $this->apcu = function_exists('apcu_fetch');
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported()
    {
        return extension_loaded('apc') ?
        'APC exist: '.extension_loaded('apc') :
        'APCu exist: '.function_exists('apcu_fetch');
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->apcu ?
        apcu_fetch($this->prefix.$key) :
        apc_fetch($this->prefix.$key);

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
     * @return array|bool
     */
    public function set($key, $value, $minutes)
    {
        return $this->apcu ?
        apcu_store($this->prefix.$key, $value, $minutes * 60) :
        apc_store($this->prefix.$key, $value, $minutes * 60);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->apcu ?
        apcu_inc($this->prefix.$key, $value) :
        apc_inc($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->apcu ?
        apcu_dec($this->prefix.$key, $value) :
        apc_dec($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return array|bool
     */
    public function forever($key, $value)
    {
        return $this->set($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return array|bool
     */
    protected function forget($key)
    {
        return $this->apcu ?
        apcu_delete($this->prefix.$key) :
        apc_delete($this->prefix.$key);
    }

    /**
     * [getMultiple description]
     *
     * @param  array $keys
     * @return array
     */
    public function getMultiple($keys)
    {
        $exists = false;

        $cacheValues = $this->apcu ?
        apcu_fetch($this->prefix.$key, $exists) :
        apc_fetch($this->prefix.$key, $exists);

        $ret = [];
        foreach ($cacheValues as $key => $value) {
            // @todo - identify the value when a cache item is not found.
            $ret[$key] = new CacheItem($key, $value, true);
        }

        return $ret;
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
        $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
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
