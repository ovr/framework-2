<?php
namespace Brainwave\Collection;

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

use \Brainwave\Crypt\Interfaces\CryptInterface;
use \Brainwave\Collection\Interfaces\CollectionInterface;

/**
 * Collection
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Collection implements CollectionInterface
{
    /**
     * Key-value array of data
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     * @param array $items Pre-populate collection with this key-value array
     */
    public function __construct(array $items = [])
    {
        $this->replace($items);
    }

    /**
     * Set data key to value
     * @param string $key   The data key
     * @param mixed  $value The data value
     * @api
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Get data value with key
     * @param  string $key     The data key
     * @param  mixed  $default The value to return if data key does not exist
     * @return mixed           The data value, or the default value
     * @api
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return $default;
    }

    /**
     * Add data to set
     * @param array $items Key-value array of data to append to this set
     * @api
     */
    public function replace(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Fetch set data
     * @return array This set's key-value data array
     * @api
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Fetch set data keys
     * @return array This set's key-value data array keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Does this set contain a key?
     * @param  string  $key The data key
     * @return boolean
     * @api
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove value with key from this set
     * @param  string $key The data key
     * @api
     */
    public function remove($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Clear all values
     * @api
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Encrypt set
     * @param  CryptInterface $crypt
     * @return void
     * @api
     */
    public function encrypt(CryptInterface $crypt)
    {
        foreach ($this->data as $key => $value) {
            $this->set($key, $crypt->encrypt($value));
        }
    }

    /**
     * Decrypt set
     * @param  CryptInterface $crypt
     * @return void
     * @api
     */
    public function decrypt(CryptInterface $crypt)
    {
        foreach ($this->data as $key => $value) {
            $this->set($key, $crypt->decrypt($value));
        }
    }

    /**
     * Does this set contain a key?
     * @param  string  $key The data key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get data value with key
     * @param  string $key     The data key
     * @return mixed           The data value
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set data key to value
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Remove value with key from this set
     * @param  string $key The data key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get number of items in collection
     * @return int
     * @api
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Get collection iterator
     * @return \ArrayIterator
     * @api
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Get a piece of data from the view.
     *
     * @param  string  $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string  $key
     * @return boolean|null
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
}
