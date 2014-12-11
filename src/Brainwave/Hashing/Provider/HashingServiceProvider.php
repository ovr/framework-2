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
    protected $container;

    public function register(Container $container)
    {
        $this->container = $container;

        $this->registerRand();
        $this->registerRandGenerator();
        $this->registerHashGenerator();

        $this->registerPassword();
    }

    protected function registerHashGenerator()
    {
        $this->container['hash'] = function ($container) {
            return new HashGenerator($container['rand.generator']);
        };
    }

    protected function registerRand()
    {
        $this->container['rand'] = function () {
            return new RandomLib();
        };
    }

    protected function registerRandGenerator()
    {
        $this->container['rand.generator'] = function ($container) {
            $generatorStrength = ucfirst(
                $container['settings']->get(
                    'app::hash.generator.strength',
                    'Medium'
                )
            );

            $generator = "get{$generatorStrength}StrengthGenerator";

            return $this->container['rand']->$generator();
        };
    }

    protected function registerPassword()
    {
        $this->container['password'] = function () {
            return new Password();
        };
    }
}
