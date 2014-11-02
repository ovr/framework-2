<?php
namespace Brainwave\Config;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Support\Arr;
use \Brainwave\Config\Interfaces\ConfigurationHandlerInterface;

/**
 * ConfigurationHandler
 * A default Configuration class which provides app configuration values stored as nested arrays,
 * which can be accessed and stored using dot separated keys.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class ConfigurationHandler implements ConfigurationHandlerInterface
{
    /**
     * Cache of previously parsed keys
     *
     * @var array
     */
    protected $keys = [];

    /**
     * Storage array of values
     *
     * @var array
     */
    protected $values = [];

    /**
     * Expected nested key separator
     *
     * @var string
     */
    protected $separator = '.';

    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set
     *
     * @param array $values
     * @required
     */
    public function setArray(array $values = [])
    {
        $this->values = $this->mergeArrays($this->values, $values);
    }

    /**
     * Set Separator
     *
     * @param string $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Get Separator
     *
     * @return string
     */
    public function getSeparator()
    {
        $this->separator;
    }

    /**
     * Get all values as nested array
     *
     * @return array
     */
    public function getAllNested()
    {
        return $this->values;
    }

    /**
     * Get all values as flattened key array
     *
     * @return array
     */
    public function getAllFlat()
    {
        return Arr::flattenArray($this->values, $this->separator);
    }

    /**
     * Get all flattened array keys
     *
     * @return array
     */
    public function getKeys()
    {
        $flattened = Arr::flattenArray($this->values, $this->separator);
        return array_keys($flattened);
    }

    /**
     * Get a value from a nested array based on a separated key
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->getValue($key, $this->values);
    }

    /**
     * Set nested array values based on a separated key
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public function offsetSet($key, $value)
    {
        $this->setValue($key, $value, $this->values);
    }

    /**
     * Check an array has a value based on a separated key
     *
     * @param  string  $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return (bool)$this->getValue($key, $this->values);
    }

    /**
     * Remove nested array value based on a separated key
     *
     * @param  string  $key
     */
    public function offsetUnset($key)
    {
        $keys = $this->parseKey($key);
        $array = &$this->values;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                return;
            }

            $array =& $array[$key];
        }

        unset($array[array_shift($keys)]);
    }

    /**
     * Parse a separated key and cache the result
     *
     * @param  string $key
     * @return array
     */
    protected function parseKey($key)
    {
        if (!isset($this->keys[$key])) {
            $this->keys[$key] = explode($this->separator, $key);
        }

        return $this->keys[$key];
    }

    /**
     * Get a value from a nested array based on a separated key
     *
     * @param  string $key
     * @param  array  $array
     * @return mixed
     */
    protected function getValue($key, array $array = [])
    {
        $keys = $this->parseKey($key);

        while (count($keys) > 0 and !is_null($array)) {
            $key = array_shift($keys);
            $array = isset($array[$key]) ? $array[$key] : null;
        }

        return $array;
    }

    /**
     * Set nested array values based on a separated key
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  array   $array
     * @return array
     */
    protected function setValue($key, $value, array &$array = [])
    {
        $keys = $this->parseKey($key, $this->separator);
        $pointer = &$array;

        while (count($keys) > 0) {
            $key = array_shift($keys);
            $pointer[$key] = (isset($pointer[$key]) ? $pointer[$key] : []);
            $pointer = &$pointer[$key];
        }

        $pointer = $value;
        return $array;
    }

    /**
     * Merge arrays with nested keys into the values store
     * Usage: $this->mergeArrays(array $array [, array $...])
     *
     * @return array
     */
    protected function mergeArrays()
    {
        $arrays = func_get_args();
        $merged = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                $merged = $this->setValue($key, $value, $merged);
            }
        }

        return $merged;
    }
}
