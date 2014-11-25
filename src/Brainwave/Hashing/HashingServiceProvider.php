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
    public function register(Container $app)
    {
        $this->registerRand($app);
        $this->registerRandGenerator($app);
        $this->registerHashGenerator($app);

        $this->registerPassword($app);
    }

    protected function registerHashGenerator($app)
    {
        $app['hash'] = function ($app) {
            return new HashGenerator($app['rand.generator']);
        };
    }

    protected function registerRand($app)
    {
        $app['rand'] = function () {
            return new RandomLib();
        };
    }

    protected function registerRandGenerator($app)
    {
        $app['rand.generator'] = function ($app) {
            $generatorStrength = ucfirst(
                $app['settings']->get(
                    'app::crypt.generator.strength',
                    'Medium'
                )
            );

            $generator = "get{$generatorStrength}StrengthGenerator";

            return $app['rand']->$generator();
        };
    }

    protected function registerPassword($app)
    {
        $app['password'] = function () {
            return new Password();
        };
    }
}
