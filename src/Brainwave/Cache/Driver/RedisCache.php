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

use \Brainwave\Cache\Driver\AbstractCache;

/**
 * RedisCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class RedisCache extends AbstractCache
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['redis']) || !$options['redis'] instanceof \Redis) {
            $options['redis'] = new \Redis;
            $options['redis']->connect('127.0.0.1');
        }

        $this->setRedis($options['redis']);
    }

    /**
     * Sets the Redis instance to use.
     *
     * @param Redis $redis
     */
    public function setRedis(\Redis $redis)
    {
        $redis->setOption(\Redis::OPT_SERIALIZER, $this->getSerializerValue());
        $this->redis = $redis;
    }

    /**
     * Gets the Redis instance used by the cache.
     *
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported()
    {
        return extension_loaded('redis');
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->redis->flushDB();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->redis->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return !!$this->redis->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return $this->redis->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        if ($ttl > 0) {
            return $this->redis->setex($key, (int) $ttl, $var);
        }

        return $this->redis->set($key, $var);
    }

    /**
     * Returns the serializer constant to use. If Redis is compiled with
     * igbinary support, that is used. Otherwise the default PHP serializer is
     * used.
     *
     * @return integer One of the Redis::SERIALIZER_* constants
     */
    protected function getSerializerValue()
    {
        return defined('Redis::SERIALIZER_IGBINARY') ? \Redis::SERIALIZER_IGBINARY : \Redis::SERIALIZER_PHP;
    }
}
