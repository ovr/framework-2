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
use \Brainwave\Support\Serializes\Interfaces\SerializerAwareInterface;

/**
 * Igbinary    Serializes cache data using the IgBinary extension.
 *
 * @see https://github.com/IgBinary/IgBinary
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Serializer implements SerializesInterface, SerializerAwareInterface
{
    /**
     * @var Serializer\Adapter
     */
    protected $serializer;

    /**
     * [__construct description]
     * @param [type] $name [description]
     */
    public function __construct($name)
    {
        $this->setSerializer($name);
    }

    /**
     * Sets the serializer.
     *
     * @param string $serializer
     */
    protected function setSerializer($serializer)
    {
        if (null === $name) {
            $this->serializer = null;
        } else {
            $classname = __NAMESPACE__ . '\Serializer\\';
            $classname .= ucfirst(strtolower($name));
            $this->serializer = new $classname();
        }
    }

    /**
     * Gets the serializer.
     *
     * @return Serializer\Adapter
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Serialises mixed data as a string.
     *
     * @param  mixed        $data
     * @return string|mixed
     */
    public function serialize($data)
    {

    }

    /**
     * Unserialises a string representation as mixed data.
     *
     * @param  string       $str
     * @return mixed|string
     */
    public function unserialize($str)
    {

    }

    /**
     * Checks if the input is a serialized string representation.
     *
     * @param  string   $str
     * @return boolean
     */
    public function isSerialized($str)
    {

    }
}
