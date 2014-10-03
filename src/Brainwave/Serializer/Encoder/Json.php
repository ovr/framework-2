<?php
namespace Brainwave\Serializer\Encoder;

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

use \Brainwave\Serializer\Encoder\Interfaces\EncoderInterface;
use \Brainwave\Serializer\Encoder\Interfaces\DecoderInterface;

/**
 * Json    Serializer data using the native PHP Json extension.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Json implements EncoderInterface, DecoderInterface
{
    /**
     * Serialize data into the given format
     *
     * @param mixed  $data   Data to encode
     * @param string $format Format name
     * @param array  $context options that normalizers/encoders have access to.
     *
     * @return string
     *
     * @throws UnexpectedValueException
     */
    public function serialize($data, $format, array $context = array())
    {
        return json_encode($data);
    }

    /**
     * Unserialize a string into PHP data.
     *
     * @param scalar $data      Data to decode
     * @param string $format    Format name
     * @param array  $context   options that decoders have access to.
     *
     * The format parameter specifies which format the data is in; valid values
     * depend on the specific implementation. Authors implementing this interface
     * are encouraged to document which formats they support in a non-inherited
     * phpdoc comment.
     *
     * @return mixed
     *
     * @throws UnexpectedValueException
     */
    public function unserialize($data, $format, array $context = array())
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

    /**
     * Resolves json_last_error message.
     *
     * @return string
     */
    public static function getLastErrorMessage()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }
}
