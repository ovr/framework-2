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
 * XcacheCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class XcacheCache extends AbstractCache
{
    /**
     * {@inheritdoc}
     */
    public static function isSupported()
    {
        return extension_loaded('xcache');
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (ini_get('xcache.admin.enable_auth')) {
            throw new \BadMethodCallException('To use all features of \Brainwave\Cache\Driver\XcacheCache, you must set "xcache.admin.enable_auth" to "Off" in your php.ini.');
        }

        return xcache_clear_cache(XC_TYPE_VAR, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return xcache_unset($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return xcache_isset($key);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return $this->exists($key) ? unserialize(xcache_get($key)) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        return xcache_set($key, serialize($var), (int) $ttl);
    }
}
