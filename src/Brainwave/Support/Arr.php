<?php
namespace Brainwave\Support;

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

use \ArrayAccess;

/**
 * Arr
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Arr
{
    /**
     * Add an element to an array if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  string   $value
     * @return array
     */
    public static function arrayAdd($array, $key, $value)
    {
        if (!isset($array[$key])) {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Build a new array using a callback.
     *
     * @param  array  $array
     * @param  \Closure  $callback
     * @return array
     */
    public static function arrayBuild($array, \Closure $callback)
    {
        $results = [];

        foreach ($array as $key => $value) {
            list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

            $results[$innerKey] = $innerValue;
        }

        return $results;
    }

    /**
     * Swap two elements between positions
     *
     * @param  array $array  array to swap
     * @param  string $swapA
     * @param  string $swapB
     * @return array
     */
    public static function arraySwap($array, $swapA, $swapB)
    {
        list($array[$swapA], $array[$swapB]) = [$array[$swapB], $array[$swapA]];
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array  $array
     * @return array
     */
    public static function arrayDivide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    public static function arrayDot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, array_dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

     /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array  $array
     * @param  string[]  $keys
     * @return array
     */
    public static function arrayExcept($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }

    /**
     * Fetch a flattened array of a nested array element.
     *
     * @param  array   $array
     * @param  string  $key
     * @return array
     */
    public static function arrayFetch($array, $key)
    {
        foreach (explode('.', $key) as $segment) {
            $results = [];

            foreach ($array as $value) {
                $value = (array) $value;

                $results[] = $value[$segment];
            }

            $array = array_values($results);
        }

        return array_values($results);
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array    $array
     * @param  \Closure  $callback
     * @param  mixed    $default
     * @return mixed
     */
    public static function arrayFirst($array, $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return self::value($default);
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array    $array
     * @param  Closure  $callback
     * @param  mixed    $default
     * @return mixed
     */
    public static function arrayLast($array, $callback, $default = null)
    {
        return self::arrayFirst(array_reverse($array), $callback, $default);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @return array
     */
    public static function arrayFlatten($array)
    {
        $return = [];

        array_walk_recursive($array, function ($x) use (&$return) {
            $return[] = $x;
        });

        return $return;
    }

    /**
     * Remove an array item from a given array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @return void
     */
    public static function arrayForget(&$array, $key)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || ! is_array($array[$key])) {
                return;
            }

            $array =& $array[$key];
        }

        unset($array[array_shift($keys)]);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function arrayGet($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || ! array_key_exists($segment, $array)) {
                return self::value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

     /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    public static function arrayOnly($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param  array   $array
     * @param  string  $value
     * @param  string  $key
     * @return array
     */
    public static function arrayPluck($array, $value, $key = null)
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = is_object($item) && !($item instanceof ArrayAccess) ? $item->{$value} : $item[$value];

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_object($item) && !($item instanceof ArrayAccess) ? $item->{$key} : $item[$key];

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @return mixed
     */
    public static function arrayPull(&$array, $key)
    {
        $value = arrayGet($array, $key);

        self::arrayForget($array, $key);

        return $value;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function arraySet(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Filter the array using the given Closure.
     *
     * @param  array  $array
     * @param  \Closure  $callback
     * @return array
     */
    public static function arrayWhere($array, \Closure $callback)
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Check structure of an array.
     * This method checks the structure of an array (only the first layer of it) against
     * a defined set of rules.
     *
     * @param array $array
     *   Array to check.
     *
     * @param array $structure
     *   Expected array structure. Defined for example like this:
     *   array(
     *     'string' => array(
     *       'callback' => 'strlen',
     *       'params'   => array('%val'),
     *       'match'    => 3,
     *     ),
     *     'not allowed' = false, // Only makes sense with $strict = false
     *     'needed'      = true,
     *   ),
     *
     * @param bool $strict
     *   If strict is set to false we will allow keys that's not defined in the structure.
     *
     * @return bool
     *   Returns true on match, and false on mismatch.
     */
    public static function arrayCheck($array, $structure, $strict = true)
    {
        $success = true;
        /* First compare the size of the two arrays. Return error if strict is enabled. */
        if (sizeof($array) != sizeof($structure) && $strict === true) {
            //Array does not match defined structure
            return false;
        }

        /* Loop trough all the defined keys defined in the structure. */
        foreach ($structure as $key => $callbackArray) {
            if (isset($array[$key])) {
                /* The key exists in the array we are checking. */

                if (is_array($callbackArray) && isset($callbackArray['callback'])) {
                    /* We have a callback. */

                    /* Replace %val with the acutal value of the key. */
                    $callbackArray['params'] = str_replace('%val', $array[$key], $callbackArray['params']);

                    if (
                        call_user_func_array(
                            $callbackArray['callback'],
                            $callbackArray['params']
                        ) !== $callbackArray['match']) {
                        /**
                         * Call the *duh* callback. If this returns false throw error,
                         * or an axe.
                         */
                        // Array does not match defined structure
                        // The '.$key.' key did not pass the '.$callbackArray['callback'].' callback');
                        $success = false;
                    }
                } elseif ($callbackArray === false) {
                    // We don't have a callback, but we have found a disallowed key.
                    // Array does not match defined structure. '.$key.' is not allowed
                    $success = false;
                }
            } else {
                // The key don't exist in the array we are checking.
                if ($callbackArray !== false) {
                    // As long as this is not a disallowed key, sound the general alarm.
                    // Array does not match defined structure. '.$key.' not defined
                    $success = false;
                }
            }
        }
        return $success;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return self::value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return self::value($default);
                }

                $target = $target->{$segment};
            } else {
                return self::value($default);
            }
        }

        return $target;
    }

    /**
     * Flatten a nested array to a separated key
     *
     * @param  array  $array
     * @param  string $separator
     * @return array
     */
    public static function flattenArray(array $array, $separator)
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = array_merge($flattened, static::flattenArray($value, $key.$separator));
            } else {
                $flattened[trim($separator.$key, $separator)] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array  $array
     * @return mixed
     */
    public static function head($array)
    {
        return reset($array);
    }

    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     * @return mixed
     */
    public static function last($array)
    {
        return end($array);
    }

    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param  string  $pattern
     * @param  array   $replacements
     * @param  string  $subject
     * @return string
     */
    public static function pregReplaceSub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, function ($match) use (&$replacements) {
            return array_shift($replacements);

        }, $subject);
    }

    /**
     * A shorter way to run a match on the array's keys rather than the values
     *
     * @param  string  $pattern
     * @param  array   $input
     * @param  integer $flags
     * @return array
     */
    public static function pregGrepKeys($pattern, array $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * Return the given object. Useful for chaining.
     *
     * @param  $object
     * @return void
     */
    public static function with($object)
    {
        return $object;
    }

    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}
