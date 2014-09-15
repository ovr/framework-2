<?php
namespace Brainwave\Cache\Driver;

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

use \Brainwave\Cache\Driver\AbstractCache;

/**
 * MemcacheCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class MemcacheCache extends AbstractCache
{
    /**
     * @var Memcache
     */
    private $memcache;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = array())
    {
        if (!isset($options['memcache']) || !$options['memcache'] instanceof \Memcache) {
            $options['memcache'] = new \Memcache;
            $options['memcache']->connect('localhost', 11211);
        }

        $this->setMemcache($options['memcache']);
    }

    /**
     * Sets the Memcache instance to use.
     *
     * @param Memcache $memcache
     */
    public function setMemcache(\Memcache $memcache)
    {
        $this->memcache = $memcache;
    }

    /**
     * Gets the Memcache instance used by the cache.
     *
     * @return Memcache
     */
    public function getMemcache()
    {
        return $this->memcache;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported()
    {
        return extension_loaded('memcache');
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->memcache->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->memcache->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return !!$this->memcache->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return $this->memcache->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        return $this->memcache->set($key, $var, 0, (int) $ttl);
    }
}
