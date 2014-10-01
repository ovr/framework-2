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

use \Brainwave\Serializes\Encoder\Interfaces\EncoderInterface;
use \Brainwave\Serializes\Encoder\Interfaces\DecoderInterface;

/**
 * ChainCoder    Decoder delegating the decoding to a chain of decoders.
 *               Encoder delegating the decoding to a chain of encoders.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class ChainCoder implements EncoderInterface, DecoderInterface
{
    /**
     * [$encoders description]
     * @var array
     */
    protected $encoders = [];

    /**
     * [$encoderByFormat description]
     * @var array
     */
    protected $encoderByFormat = [];

    /**
     * [$decoders description]
     * @var array
     */
    protected $decoders = [];

    /**
     * [$decoderByFormat description]
     * @var array
     */
    protected $decoderByFormat = [];

    /**
     * [__construct description]
     * @param array $encoders [description]
     * @param array $decoders [description]
     */
    public function __construct(array $encoders = [], array $decoders = [])
    {
        $this->encoders = $encoders;
        $this->decoders = $decoders;
    }

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
    final public function encode($data, $format, array $context = [])
    {
        return $this->getEncoder($format)->encode($data, $format, $context);
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
     * @throws \UnexpectedValueException
     */
    final public function decode($data, $format, array $context = [])
    {
        return $this->getDecoder($format)->decode($data, $format, $context);
    }

    /**
     * Checks whether the deserializer can decode from given format.
     *
     * @param string $format format name
     *
     * @return bool
     */
    public function supportsDecoding($format)
    {
        try {
            $this->getDecoder($format);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the serializer can encode to given format
     *
     * @param string $format format name
     *
     * @return bool
     */
    public function supportsEncoding($format)
    {
        try {
            $this->getEncoder($format);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Gets the decoder supporting the format.
     *
     * @param string $format
     *
     * @return DecoderInterface
     * @throws RuntimeException if no decoder is found
     */
    private function getDecoder($format)
    {
        if (isset($this->decoderByFormat[$format])
            && isset($this->decoders[$this->decoderByFormat[$format]])
        ) {
            return $this->decoders[$this->decoderByFormat[$format]];
        }

        foreach ($this->decoders as $i => $decoder) {
            if ($decoder->supportsDecoding($format)) {
                $this->decoderByFormat[$format] = $i;

                return $decoder;
            }
        }

        throw new \RuntimeException(sprintf('No decoder found for format "%s".', $format));
    }

    /**
     * Checks whether the normalization is needed for the given format.
     *
     * @param string $format
     *
     * @return bool
     */
    public function needsNormalization($format)
    {
        $encoder = $this->getEncoder($format);

        if (!$encoder instanceof NormalizationAwareInterface) {
            return true;
        }

        if ($encoder instanceof self) {
            return $encoder->needsNormalization($format);
        }

        return false;
    }

    /**
     * Gets the encoder supporting the format.
     *
     * @param string $format
     *
     * @return EncoderInterface
     * @throws \RuntimeException if no encoder is found
     */
    private function getEncoder($format)
    {
        if (isset($this->encoderByFormat[$format])
            && isset($this->encoders[$this->encoderByFormat[$format]])
        ) {
            return $this->encoders[$this->encoderByFormat[$format]];
        }

        foreach ($this->encoders as $i => $encoder) {
            if ($encoder->supportsEncoding($format)) {
                $this->encoderByFormat[$format] = $i;

                return $encoder;
            }
        }

        throw new \RuntimeException(sprintf('No encoder found for format "%s".', $format));
    }
}
