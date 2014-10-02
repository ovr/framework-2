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
 * EncoderInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface EncoderInterface
{
    /**
     * Serialize data into the given format
     *
     * @param mixed  $data   Data to encode
     * @param string $format Format name
     * @param array  $context options that normalizers/encoders have access to.
     *
     * @return scalar
     *
     * @throws \UnexpectedValueException
     */
    public function encode($data, $format, array $context = []);

    /**
     * Checks whether the serializer can encode to given format
     *
     * @param string $format format name
     *
     * @return bool
     */
    public function supportsEncoding($format);
}
