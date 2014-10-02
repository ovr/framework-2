<?php
namespace Brainwave\Serializer\Normalizer;

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

use \Brainwave\Serializer\Normalizer\SerializerAwareNormalizer;
use \Brainwave\Serializer\Normalizer\Interfaces\NormalizerInterface;
use \Brainwave\Serializer\Normalizer\Interfaces\DenormalizerInterface;

/**
 * CustomNormalizer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class GetSetMethodNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    protected $callbacks = array();
    protected $ignoredAttributes = array();
    protected $camelizedAttributes = array();

    /**
     * Set normalization callbacks.
     *
     * @param callable[] $callbacks help normalize the result
     *
     * @throws InvalidArgumentException if a non-callable callback is set
     *
     * @return GetSetMethodNormalizer
     */
    public function setCallbacks(array $callbacks)
    {
        foreach ($callbacks as $attribute => $callback) {
            if (!is_callable($callback)) {
                throw new InvalidArgumentException(
                    sprintf('The given callback for attribute "%s" is not callable.', $attribute)
                );
            }
        }
        $this->callbacks = $callbacks;

        return $this;
    }

    /**
     * Set ignored attributes for normalization
     *
     * @param array $ignoredAttributes
     *
     * @return GetSetMethodNormalizer
     */
    public function setIgnoredAttributes(array $ignoredAttributes)
    {
        $this->ignoredAttributes = $ignoredAttributes;

        return $this;
    }

    /**
     * Set attributes to be camelized on denormalize
     *
     * @param array $camelizedAttributes
     *
     * @return GetSetMethodNormalizer
     */
    public function setCamelizedAttributes(array $camelizedAttributes)
    {
        $this->camelizedAttributes = $camelizedAttributes;

        return $this;
    }

   /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param object $object object to normalize
     * @param string $format format the normalization result will be encoded as
     * @param array $context Context options for the normalizer
     *
     * @return array|scalar
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethods = $reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC);

        $attributes = array();
        foreach ($reflectionMethods as $method) {
            if ($this->isGetMethod($method)) {
                $attributeName = lcfirst(substr($method->name, 0 === strpos($method->name, 'is') ? 2 : 3));

                if (in_array($attributeName, $this->ignoredAttributes)) {
                    continue;
                }

                $attributeValue = $method->invoke($object);
                if (array_key_exists($attributeName, $this->callbacks)) {
                    $attributeValue = call_user_func($this->callbacks[$attributeName], $attributeValue);
                }
                if (null !== $attributeValue && !is_scalar($attributeValue)) {
                    if (!$this->serializer instanceof NormalizerInterface) {
                        throw new \LogicException(
                            sprintf(
                                'Cannot normalize attribute "%s" because injected serializer is not a normalizer',
                                $attributeName
                            )
                        );
                    }
                    $attributeValue = $this->serializer->normalize($attributeValue, $format);
                }

                $attributes[$attributeName] = $attributeValue;
            }
        }

        return $attributes;
    }

    /**
     * Denormalizes data back into an object of the given class
     *
     * @param mixed  $data   data to restore
     * @param string $class  the expected class to instantiate
     * @param string $format format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @return object
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (is_array($data) || is_object($data) && $data instanceof \ArrayAccess) {
            $normalizedData = $data;
        } elseif (is_object($data)) {
            $normalizedData = array();

            foreach ($data as $attribute => $value) {
                $normalizedData[$attribute] = $value;
            }
        } else {
            $normalizedData = array();
        }

        $reflectionClass = new \ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor) {
            $constructorParameters = $constructor->getParameters();

            $params = array();
            foreach ($constructorParameters as $constructorParameter) {
                $paramName = lcfirst($this->formatAttribute($constructorParameter->name));

                if (isset($normalizedData[$paramName])) {
                    $params[] = $normalizedData[$paramName];
                    // don't run set for a parameter passed to the constructor
                    unset($normalizedData[$paramName]);
                } elseif ($constructorParameter->isOptional()) {
                    $params[] = $constructorParameter->getDefaultValue();
                } else {
                    throw new RuntimeException(
                        'Cannot create an instance of '.$class.
                        ' from serialized data because its constructor requires '.
                        'parameter "'.$constructorParameter->name.
                        '" to be present.'
                    );
                }
            }

            $object = $reflectionClass->newInstanceArgs($params);
        } else {
            $object = new $class();
        }

        foreach ($normalizedData as $attribute => $value) {
            $setter = 'set'.$this->formatAttribute($attribute);

            if (method_exists($object, $setter)) {
                $object->$setter($value);
            }
        }

        return $object;
    }

    /**
     * Format attribute name to access parameters or methods
     * As option, if attribute name is found on camelizedAttributes array
     * returns attribute name in camelcase format
     *
     * @param string $attributeName
     * @return string
     */
    protected function formatAttribute($attributeName)
    {
        if (in_array($attributeName, $this->camelizedAttributes)) {
            return preg_replace_callback(
                '/(^|_|\.)+(.)/',
                function ($match) {
                    return ('.' === $match[1] ? '_' : '').strtoupper($match[2]);
                },
                $attributeName
            );
        }

        return $attributeName;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed  $data   Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $this->supports(get_class($data));
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer
     *
     * @param mixed  $data   Data to denormalize from.
     * @param string $type   The class to which the data should be denormalized.
     * @param string $format The format being deserialized from.
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->supports($type);
    }

    /**
     * Checks if the given class has any get{Property} method.
     *
     * @param string $class
     *
     * @return bool
     */
    private function supports($class)
    {
        $class = new \ReflectionClass($class);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($this->isGetMethod($method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a method's name is get.* or is.*, and can be called without parameters.
     *
     * @param \ReflectionMethod $method the method to check
     *
     * @return bool whether the method is a getter or boolean getter.
     */
    private function isGetMethod(\ReflectionMethod $method)
    {
        $methodLength = strlen($method->name);

        return (
            ((0 === strpos($method->name, 'get') && 3 < $methodLength) ||
            (0 === strpos($method->name, 'is') && 2 < $methodLength)) &&
            0 === $method->getNumberOfRequiredParameters()
        );
    }
}
