<?php
namespace Brainwave\Hashing\Provider;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.4-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use Brainwave\Hashing\Generator as HashGenerator;
use Brainwave\Hashing\Password;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RandomLib\Factory as RandomLib;

/**
 * HashingServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class HashingServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $this->registerRand($container);
        $this->registerRandGenerator($container);
        $this->registerHashGenerator($container);

        $this->registerPassword($container);
    }

    /**
     * @param Container $container
     */
    protected function registerHashGenerator($container)
    {
        $container['hash'] = function ($container) {
            return new HashGenerator($container['rand.generator']);
        };
    }

    /**
     * @param Container $container
     */
    protected function registerRand($container)
    {
        $container['rand'] = function () {
            return new RandomLib();
        };
    }

    /**
     * @param Container $container
     */
    protected function registerRandGenerator($container)
    {
        $container['rand.generator'] = function ($container) {
            $generatorStrength = ucfirst(
                $container['settings']->get(
                    'app::crypt.generator.strength',
                    'Medium'
                )
            );

            $generator = "get{$generatorStrength}StrengthGenerator";

            return $container['rand']->$generator();
        };
    }

    /**
     * @param Container $container
     */
    protected function registerPassword($container)
    {
        $container['password'] = function () {
            return new Password();
        };
    }
}
