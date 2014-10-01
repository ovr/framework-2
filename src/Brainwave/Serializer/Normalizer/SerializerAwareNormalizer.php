<?php
namespace Brainwave\Serializes\Normalizer;

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

use \Brainwave\Serializes\Interfaces\SerializerInterface;
use \Brainwave\Serializes\Interfaces\SerializerAwareInterface;

/**
 * SerializerAwareNormalizer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
abstract class SerializerAwareNormalizer implements SerializerAwareInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

     /**
     * Sets the owning Serializer object
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
