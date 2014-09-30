<?php
namespace Brainwave\Support\Serializes;

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

use \Brainwave\Support\Serializes\Interfaces\SerializesInterface;

/**
 * Stringset    Serializes cache data using a stringset (appendset) such as:
 * 'tag1 tag2 -tag2 -tag3'
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Stringset implements SerializesInterface
{
    /**
     * Holds this string dirtiness.
     * @var integer
     */
    protected $dirtiness;

    /**
     * Serialises mixed data as a string.
     *
     * @param  mixed        $data
     * @return string|mixed
     *
     * e.g. ['a','c'] => 'a b '
     */
    public function serialize($keys, $op = '')
    {
        $str = '';
        foreach ((array) $keys as $key) {
            $str .= "${op}${key} ";
        }

        return $str != '' ? $str : null;
    }

    /**
     * Unserialises a string representation as mixed data.
     *
     * @param  string       $str
     * @return mixed|string
     *
     * e.g. 'a b c -b -x ' => ['a','c'];
     * Sets the dirtiness level (counts the negative entries).
     */
    public function unserialize($str)
    {
        $add    = array();
        $remove = array();
        foreach (explode(' ', trim($str)) as $key) {
            if (isset($key[0])) {
                if ($key[0] == '-') {
                    $remove[] = substr($key, 1);
                } else {
                    $add[] = $key;
                }
            }
        }

        $this->dirtiness = count($remove);

        $items = array_values(array_diff($add, $remove));

        return empty($items) ? null : $items;
    }

     /**
     * Checks if the input is a serialized string representation.
     *
     * @param  string   $str
     * @return boolean
     */
    public function isSerialized($str)
    {
        if (!is_string($str)) {
            return false;
        }

        return (boolean) preg_match('/^.*\s$/', $str);
    }

    /**
     * Returns the dirtness level of the userialized string.
     *
     * @return integer
     */
    public function getDirtiness()
    {
        return $this->dirtiness;
    }
}
