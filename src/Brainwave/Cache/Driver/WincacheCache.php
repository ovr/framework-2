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
 * WincacheCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class WincacheCache extends AbstractCache
{
    /**
     * {@inheritdoc}
     */
    public static function isSupported()
    {
        return extension_loaded('wincache');
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return wincache_ucache_clear();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return wincache_ucache_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return wincache_ucache_exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return wincache_ucache_get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        return wincache_ucache_set($key, $var, (int) $ttl);
    }
}
