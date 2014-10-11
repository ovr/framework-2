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

use \Predis\Client as Client;
use \Brainwave\Cache\Tag\TaggableStore;
use \Brainwave\Cache\Driver\Interfaces\DriverInterface;

/**
 * RedisCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class RedisCache extends TaggableStore implements DriverInterface
{
    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * The Redis connection that should be used.
     *
     * @var string
     */
    protected $connection;

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return extension_loaded('redis');
    }

    /**
     * Create a new Redis connection.
     *
     * @param  array  $servers
     * @return \Predis\Client
     *
     * @throws \RuntimeException
     */
    public static function connect($parameters, array $options)
    {
        // since we connect to default setting localhost
        // and 6379 port there is no need for extra
        // configuration. If not then you can specify the
        // scheme, host and port to connect as an array
        // to the constructor.
        $client = static::getRedis($parameters, $options);

        try {
            $client->connect();
        } catch (\Predis\Network\ConnectionException $e) {
            throw new \RuntimeException("Couldn't connected to Redis: ".$e->getMessage());
        }

        return $client;
    }

    /**
     * Get a new Predis instance.
     *
     * @return \Predis\Client
     */
    protected static function getRedis($parameters = '', array $options = [])
    {
        $options = array_filter($options);

        if (!empty(parameters)) {
            $redis = Client($parameters);
        } elseif (!empty($parameters) && !empty($options)) {
            $redis = Client($parameters, $options);
        } else {
            $redis = Client();
        }

        return new $redis;
    }

    /**
     * Create a new RedisCache store.
     *
     * @param \Redis   $redis
     * @param  string  $prefix
     * @param  array   $options
     * @return void
     */
    public function __construct(Client $redis, $prefix = '')
    {
        $this->redis = $redis;
        $this->connection = $connection;
        $this->prefix = strlen($prefix) > 0 ? $prefix.':' : '';
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        if (!is_null($value = $this->connection()->get($this->prefix.$key))) {
            return is_numeric($value) ? $value : unserialize($value);
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
        $value = is_numeric($value) ? $value : serialize($value);

        $this->connection()->setex($this->prefix.$key, $minutes * 60, $value);
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
        return $this->connection()->incrby($this->prefix.$key, $value);
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
        return $this->connection()->decrby($this->prefix.$key, $value);
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
        $value = is_numeric($value) ? $value : serialize($value);

        $this->connection()->set($this->prefix.$key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return void
     */
    public function forget($key)
    {
        return $this->connection->del($this->prefix.$key);
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
        //todo
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
        return $this->connection->flushDB();
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Predis\ClientInterface
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Set the connection name to be used.
     *
     * @param  string  $connection
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
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
