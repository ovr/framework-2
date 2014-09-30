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
 * Json    Serializes data using the native PHP Json extension.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Json implements SerializesInterface
{
    /**
     * Serialises mixed data as a string.
     *
     * @param  mixed        $data
     * @return string|mixed
     */
    public function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * Unserialises a string representation as mixed data.
     *
     * @param  string       $str
     * @return mixed|string
     */
    public function unserialize($str)
    {
        return json_decode($str);
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

        return (boolean) (json_decode($str) !== null);
    }
}
