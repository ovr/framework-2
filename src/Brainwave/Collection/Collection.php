<?php
namespace Brainwave\Collection;

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

use \Brainwave\Support\Arr;
use \Brainwave\Contracts\Support\Jsonable as JsonableContract;
use \Brainwave\Contracts\Support\Arrayable as ArrayableContract;
use \Brainwave\Contracts\Encrypter\Encrypter as EncrypterContract;
use \Brainwave\Contracts\Collection\Collection as CollectionContract;

/**
 * Collection
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Collection implements
    \ArrayAccess,
    ArrayableContract,
    \Countable,
    \IteratorAggregate,
    JsonableContract,
    \JsonSerializable,
    CollectionContract
{
    /**
     * Key-value array of data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array $items Pre-populate collection with this key-value array
     */
    public function __construct(array $items = [])
    {
        $this->replace($items);
    }

    /**
     * Set data key to value
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param  mixed $items
     *
     * @return static
     */
    public static function makeNew($items = null)
    {
        return new static ($items);
    }

    /**
     * Get data value with key
     *
     * @param  string $key     The data key
     * @param  mixed  $default The value to return if data key does not exist
     *
     * @return mixed           The data value, or the default value
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
     *
     * @param array $items Key-value array of data to append to this set
     */
    public function replace(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Fetch set data
     *
     * @return array This set's key-value data array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Collapse the collection items into a single array.
     *
     * @return static
     */
    public function collapse()
    {
        $results = array();

        foreach ($this->data as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            }

            $results = array_merge($results, $values);
        }

        return new static ($results);
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param  mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        if ($value instanceof \Closure) {
            return !is_null($this->first($value));
        }

        return in_array($value, $this->data);
    }

    /**
     * Diff the collection with the given items.
     *
     * @param  \Brainwave\Collection\Collection|
     *         ArrayableContract|
     *         array $items
     *
     * @return static
     */
    public function diff($items)
    {
        return new static (array_diff($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Execute a callback over each item.
     *
     * @param  \Closure $callback
     *
     * @return $this
     */
    public function each(\Closure $callback)
    {
        array_map($callback, $this->data);

        return $this;
    }

    /**
     * Fetch a nested element of the collection.
     *
     * @param  string $key
     *
     * @return static
     */
    public function fetch($key)
    {
        return new static (array_fetch($this->data, $key));
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  \Closure $callback
     *
     * @return static
     */
    public function filter(\Closure $callback)
    {
        return new static (array_filter($this->data, $callback));
    }

    /**
     * Get the first item from the collection.
     *
     * @param  \Closure $callback
     * @param  mixed    $default
     *
     * @return mixed|null
     */
    public function first(\Closure $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return count($this->data) > 0 ? Arr::head($this->data) : null;
        }

        return Arr::arrayFirst($this->data, $callback, $default);
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @return static
     */
    public function flatten()
    {
        return new static (array_flatten($this->data));
    }

    /**
     * Flip the items in the collection.
     *
     * @return static
     */
    public function flip()
    {
        return new static (array_flip($this->data));
    }

    /**
     * Fetch set data keys
     *
     * @return array This set's key-value data array keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Get the last item from the collection.
     *
     * @return mixed|null
     */
    public function last()
    {
        return count($this->data) > 0 ? end($this->data) : null;
    }

    /**
     * Get an array with the values of a given key.
     *
     * @param  string $value
     * @param  string $key
     *
     * @return array
     */
    public function lists($value, $key = null)
    {
        return Arr::arrayPluck($this->data, $value, $key);
    }

    /**
     * Does this set contain a key?
     *
     * @param  string $key The data key
     *
     * @return boolean
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove value with key from this set
     *
     * @param string $key The data key
     */
    public function remove($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Clear all values
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Group an associative array by a field or Closure value.
     *
     * @param  callable|string $groupBy
     *
     * @return static
     */
    public function groupBy($groupBy)
    {
        $results = array();

        foreach ($this->data as $key => $value) {
            $results[$this->getGroupbyKey($groupBy, $key, $value)][] = $value;
        }

        return new static ($results);
    }

    /**
     * Get the "group by" key value.
     *
     * @param  callable|string $groupBy
     * @param  string          $key
     * @param  mixed           $value
     *
     * @return string
     */
    protected function getGroupbyKey($groupBy, $key, $value)
    {
        if (!is_string($groupBy) && is_callable($groupBy)) {
            return $groupBy($value, $key);
        }

        return Arr::dataGet($value, $groupBy);
    }

    /**
     * Key an associative array by a field.
     *
     * @param  string $keyBy
     *
     * @return static
     */
    public function keyBy($keyBy)
    {
        $results = [];

        foreach ($this->data as $item) {
            $key = Arr::dataGet($item, $keyBy);

            $results[$key] = $item;
        }

        return new static ($results);
    }

    /**
     * Run a map over each of the items.
     *
     * @param  \Closure $callback
     *
     * @return static
     */
    public function map(\Closure $callback)
    {
        return new static (array_map($callback, $this->data, array_keys($this->data)));
    }

    /**
     * Push an item onto the beginning of the collection.
     *
     * @param  mixed $value
     *
     * @return void
     */
    public function prepend($value)
    {
        array_unshift($this->data, $value);
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param  \Brainwave\Collection\Collection $value
     *
     * @return void
     */
    public function push($value)
    {
        $this->data[] = $value;
    }

    /**
     * Merge the collection with the given items.
     *
     * @param  \Brainwave\Collection\Collection|
     *         ArrayableContract|
     *         array $items
     *
     * @return static
     */
    public function merge($items)
    {
        return new static (array_merge($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed|null
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * Pulls an item from the collection.
     *
     * @param  mixed $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::arrayPull($this->data, $key, $default);
    }

    /**
     * Put an item in the collection by key.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return void
     */
    public function put($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param  \Closure|mixed $callback
     *
     * @return static
     */
    public function reject($callback)
    {
        if ($callback instanceof \Closure) {
            return $this->filter(function ($item) use ($callback) {
                return !$callback($item);
            });
        }

        return $this->filter(function ($item) use ($callback) {
            return $item != $callback;
        });
    }

    /**
     * Reverse items order.
     *
     * @return static
     */
    public function reverse()
    {
        return new static (array_reverse($this->data));
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     *
     * @param  mixed $value
     * @param  bool  $strict
     *
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        return array_search($value, $this->data, $strict);
    }

    /**
     * Get and remove the first item from the collection.
     *
     * @return mixed|null
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * Shuffle the items in the collection.
     *
     * @return $this
     */
    public function shuffle()
    {
        shuffle($this->data);

        return $this;
    }

    /**
     * Slice the underlying collection array.
     *
     * @param  int   $offset
     * @param  int   $length
     * @param  bool  $preserveKeys
     *
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static (array_slice($this->data, $offset, $length, $preserveKeys));
    }

    /**
     * Chunk the underlying collection array.
     *
     * @param  int  $size
     * @param  bool $preserveKeys
     *
     * @return static
     */
    public function chunk($size, $preserveKeys = false)
    {
        $chunks = new static;

        foreach (array_chunk($this->data, $size, $preserveKeys) as $chunk) {
            $chunks->push(new static ($chunk));
        }

        return $chunks;
    }

    /**
     * Sort through each item with a callback.
     *
     * @param  \Closure $callback
     *
     * @return self
     */
    public function sort(\Closure $callback)
    {
        uasort($this->data, $callback);

        return $this;
    }

    /**
     * Sort the collection using the given Closure.
     *
     * @param  \Closure|string $callback
     * @param  int             $options
     * @param  bool            $descending
     *
     * @return self
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = array();

        if (is_string($callback)) {
            $callback = $this->valueRetriever($callback);
        }

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($this->data as $key => $value) {
            $results[$key] = $callback($value);
        }

        $descending ? arsort($results, $options)
        : asort($results, $options);

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->data[$key];
        }

        $this->data = $results;

        return $this;
    }

    /**
     * Sort the collection in descending order using the given Closure.
     *
     * @param  \Closure|string $callback
     * @param  int             $options
     *
     * @return self
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Splice portion of the underlying collection array.
     *
     * @param  int   $offset
     * @param  int   $length
     * @param  mixed $replacement
     *
     * @return static
     */
    public function splice($offset, $length = 0, $replacement = array())
    {
        return new static (array_splice($this->data, $offset, $length, $replacement));
    }

    /**
     * Get the sum of the given values.
     *
     * @param  \Closure $callback
     *
     * @return mixed
     */
    public function sum($callback)
    {
        if (is_string($callback)) {
            $callback = $this->valueRetriever($callback);
        }

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result += $callback($item);

        }, 0);
    }

    /**
     * Take the first or last {$limit} items.
     *
     * @param  int $limit
     *
     * @return static
     */
    public function take($limit = null)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Transform each item in the collection using a callback.
     *
     * @param  \Closure $callback
     *
     * @return $this
     */
    public function transform(\Closure $callback)
    {
        $this->data = array_map($callback, $this->data);

        return $this;
    }

    /**
     * Return only unique items from the collection array.
     *
     * @return static
     */
    public function unique()
    {
        return new static (array_unique($this->data));
    }

    /**
     * Arr::head the keys on the underlying array.
     *
     * @return static
     */
    public function values()
    {
        $this->data = array_values($this->data);

        return $this;
    }

    /**
     * Get a value retrieving callback.
     *
     * @param  string  $value
     *
     * @return \Closure
     */
    protected function valueRetriever($value)
    {
        return function ($item) use ($value) {
            return Arr::dataGet($item, $value);
        };
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof ArrayableContract ? $value->toArray() : $value;

        }, $this->data);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Set Encrypter
     *
     * @param  EncrypterContract $crypt
     *
     * @return void
     */
    public function encrypt(EncrypterContract $crypt)
    {
        foreach ($this->data as $key => $value) {
            $this->set($key, $crypt->encrypt($value));
        }
    }

    /**
     * Set Decrypter
     *
     * @param  EncrypterContract $crypt
     *
     * @return void
     */
    public function decrypt(EncrypterContract $crypt)
    {
        foreach ($this->data as $key => $value) {
            $this->set($key, $crypt->decrypt($value));
        }
    }

    /**
     * Does this set contain a key?
     *
     * @param  string $key The data key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get data value with key
     *
     * @param  string $key The data key
     * @return mixed       The data value
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set data key to value
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Remove value with key from this set
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param  \Brainwave\Collection\Collection|
     *         ArrayableContract|
     *         array $items
     *
     * @return static
     */
    public function intersect($items)
    {
        return new static (array_intersect($this->data, $this->getArrayableItems($items)));
    }

    /**
     * Concatenate values of a given key as a string.
     *
     * @param  mixed  $value
     * @param  string $glue
     *
     * @return string
     */
    public function implode($value, $glue = null)
    {
        if (is_null($glue)) {
            return $this->lists($value);
        }

        return implode($glue, $this->lists($value));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param  callable $callback
     * @param  integer  $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->data, $callback, $initial);
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Get a CachingIterator instance.
     *
     * @param  int $flags
     *
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = \CachingIterator::CALL_TOSTRING)
    {
        return new \CachingIterator($this->getIterator(), $flags);
    }

    /**
     * Get collection iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Get a piece of data from the view.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string $key
     *
     * @return boolean|null
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  \Brainwave\Collection\Collection|
     *         ArrayableContract|
     *         array $items
     *
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        } elseif ($items instanceof ArrayableContract) {
            $items = $items->toArray();
        }

        return $items;
    }
}
