<?php
namespace Brainwave\Cache\Adapter;

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

use \Brainwave\Cache\Store\TaggableStore;
use \Brainwave\Contracts\Cache\Adapter as AdapterContract;

/**
 * MemcacheCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class MemcacheCache extends TaggableStore implements AdapterContract
{
    /**
     * The Memcache instance.
     *
     * @var \Memcache
     */
    private $memcache;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Time of a stored item
     *
     * @var array
     */
    protected $minutes = [];

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return extension_loaded('memcache');
    }

    /**
     * Create a new Memcache connection.
     *
     * @param  array $servers
     *
     * @return \Memcache
     *
     * @throws \RuntimeException
     */
    public static function connect(array $servers)
    {
        $memcached = static::getMemcache();

        // For each server in the array, we'll just extract the configuration and add
        // the server to the Memcache connection. Once we have added all of these
        // servers we'll verify the connection is successful and return it back.
        foreach ($servers as $server) {
            $memcached->addServer(
                $server['host'],
                $server['port'],
                $server['weight']
            );
        }

        if ($memcached->getVersion() === false) {
            throw new \RuntimeException("Could not establish Memcache connection.");
        }

        return $memcached;
    }

    /**
     * Get a new Memcache instance.
     *
     * @return \Memcache
     */
    protected static function getMemcache()
    {
        return new \Memcache;
    }

    /**
     * Create a new file cache store instance.
     *
     * @param \Memcache $memcache
     * @param string    $prefix
     */
    public function __construct(\Memcache $memcache, $prefix = '')
    {
        $this->memcache = $memcache;
        $this->prefix = strlen($prefix) > 0 ? $prefix.':' : '';
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcache->get($this->prefix.$key);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $minutes
     *
     * @return boolean
     */
    public function put($key, $value, $minutes)
    {
        $this->minutes[$key] = $minutes;

        $this->memcache->set($this->prefix.$key, $value, $minutes * 60);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer $value
     *
     * @return integer
     */
    public function increment($key, $value = 1)
    {
        return $this->memcache->increment($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer $value
     *
     * @return integer
     */
    public function decrement($key, $value = 1)
    {
        return $this->memcache->decrement($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return boolean
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function forget($key)
    {
        return $this->memcache->delete($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return boolean
     */
    public function flush()
    {
        return $this->memcache->flush();
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

    /**
     * Get the stored time of a item
     *
     * @param  string $key
     *
     * @return int
     */
    public function getStoredItemTime($key)
    {
        return $this->minutes[$key];
    }
}
