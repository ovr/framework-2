<?php namespace Brainwave\Cache\Driver;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Cache\Driver\AbstractCache;

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
