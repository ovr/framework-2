<?php
namespace Brainwave\Serializes\Normalizer\Interfaces;

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

use \Brainwave\Serializes\Normalizer\Interfaces\DenormalizerInterface;

/**
 * DenormalizableInterface
 *
 * Defines the most basic interface a class must implement to be denormalizable
 *
 * If a denormalizer is registered for the class and it doesn't implement
 * the Denormalizable interfaces, the normalizer will be used instead
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface DenormalizableInterface
{
    /**
     * Denormalizes the object back from an array of scalars|arrays.
     *
     * It is important to understand that the denormalize() call should denormalize
     * recursively all child objects of the implementor.
     *
     * @param DenormalizerInterface $denormalizer The denormalizer is given so that you
     *   can use it to denormalize objects contained within this object.
     * @param array|scalar $data   The data from which to re-create the object.
     * @param string|null  $format The format is optionally given to be able to denormalize differently
     *   based on different input formats.
     * @param array        $context options for denormalizing
     */
    public function denormalize(
        DenormalizerInterface $denormalizer,
        $data,
        $format = null,
        array $context = array()
    );
}
