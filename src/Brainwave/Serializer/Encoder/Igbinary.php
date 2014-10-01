<?php
namespace Brainwave\Serializes\Encoder;

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

use \Brainwave\Serializes\Interfaces\SerializesInterface;

/**
 * Igbinary    Serializes cache data using the IgBinary extension.
 *
 * @see https://github.com/IgBinary/IgBinary
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Igbinary implements SerializesInterface
{
    /**
     * Serialises mixed data as a string.
     *
     * @param  mixed        $data
     * @return string|mixed
     */
    public function serialize($data)
    {
        return \igbinary_serialize($data);
    }

    /**
     * Unserialises a string representation as mixed data.
     *
     * @param  string       $str
     * @return mixed|string
     */
    public function unserialize($str)
    {
        return \igbinary_unserialize($str);
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

        return @substr_count($str, "\000", 0, 3) == 3;
    }
}
