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
 * MemcachedCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class MemcachedCache extends AbstractCache
{
    /**
     * @var Memcached
     */
    private $memcached;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = array())
    {
        if (!isset($options['memcached']) || !$options['memcached'] instanceof \Memcached) {
            $options['memcached'] = new \Memcached(uniqid());
            $options['memcached']->setOption(\Memcached::OPT_COMPRESSION, false);
            $options['memcached']->addServer('127.0.0.1', 11211);
        }

        $this->setMemcached($options['memcached']);
    }

    /**
     * Sets the Memcached instance to use.
     *
     * @param Memcached $memcached
     */
    public function setMemcached(\Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * Gets the Memcached instance used by the cache.
     *
     * @return Memcached
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported()
    {
        return extension_loaded('Memcached');
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->memcached->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->memcached->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return !!$this->memcached->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        return $this->memcached->set($key, $var, (int) $ttl);
    }
}
