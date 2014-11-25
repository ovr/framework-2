<?php
namespace Brainwave\Encrypter;

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

use \Pimple\Container;
use \Brainwave\Hashing\Password;
use \RandomLib\Factory as RandomLib;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Hashing\Generator as HashGenerator;

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

    protected function registerHashGenerator($container)
    {
        $container['hash'] = function ($container) {
            return new HashGenerator($container['rand.generator']);
        };
    }

    protected function registerRand($container)
    {
        $container['rand'] = function () {
            return new RandomLib();
        };
    }

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

    protected function registerPassword($container)
    {
        $container['password'] = function () {
            return new Password();
        };
    }
}
