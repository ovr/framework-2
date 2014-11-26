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

use \Brainwave\Contracts\Cache\Adapter as AdapterContract;

/**
 * ArrayCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class ArrayCache implements AdapterContract
{
    /**
     * The array of stored values.
     *
     * @var array $storage
     */
    private $storage = [];

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return true;
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
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $minutes
     *
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer $value
     *
     * @return int
     */
    public function increment($key, $value = 1)
    {
        $this->storage[$key] = $this->storage[$key] + $value;

        return $this->storage[$key];
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer $value
     *
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        $this->storage[$key] = $this->storage[$key] - $value;

        return $this->storage[$key];
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function forget($key)
    {
        unset($this->storage[$key]);
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        $this->storage = array();
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return '';
    }
}
