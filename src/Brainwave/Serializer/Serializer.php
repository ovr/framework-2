<?php
namespace Brainwave\Serializer;

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

use \Brainwave\Serializer\Encoder\ChainCoder;
use \Brainwave\Serializer\Interfaces\SerializerInterface;
use \Brainwave\Serializer\Encoder\Interfaces\EncoderInterface;
use \Brainwave\Serializer\Encoder\Interfaces\DecoderInterface;
use \Brainwave\Serializer\Interfaces\SerializerAwareInterface;
use \Brainwave\Serializer\Normalizer\Interfaces\NormalizerInterface;
use \Brainwave\Serializer\Normalizer\Interfaces\DenormalizerInterface;

/**
 * Serializer serializes and deserializes data
 *
 * objects are turned into arrays by normalizers
 * arrays are turned into various output formats by coders
 *
 * $serializer->serialize($obj, 'xml')
 * $serializer->decode($data, 'xml')
 * $serializer->denormalize($data, 'Class', 'xml')
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Serializer implements SerializerInterface,
NormalizerInterface,
DenormalizerInterface,
EncoderInterface,
DecoderInterface
{
    /**
     * Normalizers
     *
     * @var Instance of SerializerAwareInterface
     */
    protected $normalizers = [];

    /**
     * coder and Decoder
     *
     * @var bool
     */
    protected $coder;

    /**
     * [$normalizerCache description]
     * @var [type]
     */
    protected $normalizerCache   = [];

    /**
     * [$denormalizerCache description]
     * @var [type]
     */
    protected $denormalizerCache = [];

    /**
     * Gets the serializer.
     *
     * @return Serializer\Adapter
     */
    public function getNormalizers()
    {
        return $this->normalizers;
    }

    /**
     * Serialises mixed data as a string.
     *
     * @param  mixed        $data
     * @return string|mixed
     */
    public function setNormalizers(array $normalizers = [])
    {
        foreach ($normalizers as $normalizer) {
            if ($normalizer instanceof SerializerAwareInterface) {
                $normalizer->setSerializer($this);
            }
        }

        $this->normalizers = $normalizers;
    }

    /**
     * Gets the serializer.
     *
     * @return Serializer\Adapter
     */
    public function getcoders()
    {
        return $this->coder;
    }

    /**
     * [setcoders description]
     * @param array $coders [description]
     */
    public function setEncoders(array $coders = [])
    {
        $coders = [];
        $realcoders = [];

        foreach ($coders as $coder) {
            if ($coder instanceof SerializerAwareInterface) {
                $coder->setSerializer($this);
            }
            if ($coder instanceof DecoderInterface) {
                $coders[] = $coder;
            }
            if ($coder instanceof coderInterface) {
                $realcoders[] = $coder;
            }
        }

        $this->coder = new ChainCoder($realcoders, $coders);
    }

    /**
     * {@inheritdoc}
     */
    final public function serialize($data, $format, array $context = [])
    {
        if (!$this->supportsEncoding($format)) {
            throw new \UnexpectedValueException(sprintf('Serialization for the format %s is not supported', $format));
        }

        if ($this->coder->needsNormalization($format)) {
            $data = $this->normalize($data, $format, $context);
        }

        return $this->encode($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    final public function deserialize($data, $type, $format, array $context = [])
    {
        if (!$this->supportsDecoding($format)) {
            throw new \UnexpectedValueException(
                sprintf('Deserialization for the format %s is not supported', $format)
            );
        }

        $data = $this->decode($data, $format, $context);

        return $this->denormalize($data, $type, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }
        if (is_object($data) && $this->supportsNormalization($data, $format)) {
            return $this->normalizeObject($data, $format, $context);
        }
        if ($data instanceof \Traversable) {
            $normalized = [];
            foreach ($data as $key => $val) {
                $normalized[$key] = $this->normalize($val, $format, $context);
            }

            return $normalized;
        }
        if (is_object($data)) {
            return $this->normalizeObject($data, $format, $context);
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->normalize($val, $format, $context);
            }

            return $data;
        }
        throw new \UnexpectedValueException(
            sprintf('An unexpected value could not be normalized: %s', var_export($data, true))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return $this->denormalizeObject($data, $type, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return null !== $this->getNormalizer($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return null !== $this->getDenormalizer($data, $type, $format);
    }

    /**
     * Returns a matching normalizer.
     *
     * @param object $data The object to get the serializer for
     * @param string $format format name, present to give the option to
     *                normalizers to act differently based on formats
     *
     * @return NormalizerInterface|null
     */
    private function getNormalizer($data, $format)
    {
        $class = get_class($data);
        if (isset($this->normalizerCache[$class][$format])) {
            return $this->normalizerCache[$class][$format];
        }

        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof NormalizerInterface &&
                $normalizer->supportsNormalization($data, $format)
            ) {
                $this->normalizerCache[$class][$format] = $normalizer;

                return $normalizer;
            }
        }
    }

    /**
     * Returns a matching denormalizer.
     *
     * @param mixed  $data   data to restore
     * @param string $class  the expected class to instantiate
     * @param string $format format name, present to give the option to
     *                normalizers to act differently based on formats
     *
     * @return DenormalizerInterface|null
     */
    private function getDenormalizer($data, $class, $format)
    {
        if (isset($this->denormalizerCache[$class][$format])) {
            return $this->denormalizerCache[$class][$format];
        }

        foreach ($this->normalizers as $normalizer) {
            if ($normalizer instanceof DenormalizerInterface &&
                $normalizer->supportsDenormalization($data, $class, $format)
            ) {
                $this->denormalizerCache[$class][$format] = $normalizer;

                return $normalizer;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function encode($data, $format, array $context = [])
    {
        return $this->coder->encode($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    final public function decode($data, $format, array $context = [])
    {
        return $this->coder->decode($data, $format, $context);
    }

    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param object $object object to normalize
     * @param string $format format name, present to give the option to normalizers to act differently based on formats
     * @param array $context The context data for this particular normalization
     *
     * @return array|scalar
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function normalizeObject($object, $format, array $context = [])
    {
        if (!$this->normalizers) {
            throw new \LogicException('You must register at least one normalizer to be able to normalize objects.');
        }

        if ($normalizer = $this->getNormalizer($object, $format)) {
            return $normalizer->normalize($object, $format, $context);
        }
        throw new \UnexpectedValueException(
            sprintf(
                'Could not normalize object of type %s, no supporting normalizer found.',
                get_class($object)
            )
        );
    }

    /**
     * Denormalizes data back into an object of the given class
     *
     * @param mixed  $data   data to restore
     * @param string $class  the expected class to instantiate
     * @param string $format format name, present to give the option to normalizers to act differently based on formats
     * @param array $context The context data for this particular denormalization
     *
     * @return object
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function denormalizeObject($data, $class, $format, array $context = [])
    {
        if (!$this->normalizers) {
            throw new \LogicException('You must register at least one normalizer to be able to denormalize objects.');
        }

        if ($normalizer = $this->getDenormalizer($data, $class, $format)) {
            return $normalizer->denormalize($data, $class, $format, $context);
        }
        throw new \UnexpectedValueException(
            sprintf('Could not denormalize object of type %s, no supporting normalizer found.', $class)
        );
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

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return $this->coder->supportsEncoding($format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return $this->coder->supportsDecoding($format);
    }
}
