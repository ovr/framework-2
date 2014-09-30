<?php
namespace Brainwave\Support\Serializer\Interfaces;

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

/**
 * SerializerInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface SerializerInterface
{
    /**
     * Serializes data in the appropriate format
     *
     * @param mixed  $data    any data
     * @param string $format  format name
     * @param array  $context options normalizers/encoders have access to
     *
     * @return string
     */
    public function serialize($data, $format, array $context = array());

    /**
     * Deserializes data into the given type.
     *
     * @param mixed  $data
     * @param string $type
     * @param string $format
     * @param array  $context
     *
     * @return object
     */
    public function deserialize($data, $type, $format, array $context = array());

    /**
     * Checks if the input is a serialized string representation.
     *
     * @param  string   $str
     * @return boolean
     */
    public function isSerialized($str);
}
