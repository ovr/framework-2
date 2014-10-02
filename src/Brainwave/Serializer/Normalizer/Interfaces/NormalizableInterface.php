<?php
namespace Brainwave\Serializer\Normalizer\Interfaces;

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

use \Brainwave\Serializer\Normalizer\Interfaces\NormalizerInterface;

/**
 * NormalizableInterface
 *
 * Defines the most basic interface a class must implement to be normalizable
 *
 * If a normalizer is registered for the class and it doesn't implement
 * the Normalizable interfaces, the normalizer will be used instead
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface NormalizableInterface
{
    /**
     * Normalizes the object into an array of scalars|arrays.
     *
     * It is important to understand that the normalize() call should normalize
     * recursively all child objects of the implementor.
     *
     * @param NormalizerInterface $normalizer The normalizer is given so that you
     *   can use it to normalize objects contained within this object.
     * @param string|null $format The format is optionally given to be able to normalize differently
     *   based on different output formats.
     * @param array $context Options for normalizing this object
     *
     * @return array|scalar
     */
    public function normalize(
        NormalizerInterface $normalizer,
        $format = null,
        array $context = array()
    );
}
