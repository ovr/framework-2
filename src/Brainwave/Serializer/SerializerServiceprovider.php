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

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Serializer\Serializer;

/**
 * SerializerServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class SerializerServiceProvider implements ServiceProviderInterface
{

    /**
     * Register Serializer
     * @return  \Brainwave\Serializer\Serializer
     */
    public function register(Container $app)
    {
        $normalizers = [];
        $encoders = [];

        $app['serializer'] = $app->factory(function ($app) use ($normalizers, $encoders) {
            $serializer = new Serializer();
            $serializer->setNormalizers($normalizers);
            $serializer->setEncoders($encoders);
            return $serializer;
        });
    }
}
