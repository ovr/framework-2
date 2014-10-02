<?php
namespace Brainwave\Serializer\Encoder\Interfaces;

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
 * DecoderInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface DecoderInterface
{
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
     * @throws \UnexpectedValueException
     */
    public function decode($data, $format, array $context = []);

    /**
     * Checks whether the deserializer can decode from given format.
     *
     * @param string $format format name
     *
     * @return bool
     */
    public function supportsDecoding($format);
}
