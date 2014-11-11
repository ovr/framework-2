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
use \Brainwave\Encrypter\Encrypter;
use \RandomLib\Factory as RandomLib;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Encrypter\HashGenerator;

/**
 * CryptServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class EncrypterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $this->registerRand($app);
        $this->registerRandGenerator($app);

        $app['encrypter'] = function ($app) {
            return new Encrypter(
                $app['encrypter.rand.generator'],
                $app['settings']->get(
                    'app::crypt.key',
                    '3L43~[[i(98$_[j;3i86[ri.64M2[2[+<)4->yB>6Vv>Rfv0[K$.w={MrDHu@d;'
                ),
                $app['settings']->get('app::crypt.cipher', MCRYPT_RIJNDAEL_256),
                $app['settings']->get('app::crypt.mode', 'ctr')
            );
        };

        $this->registerHashGenerator($app);
    }

    protected function registerHashGenerator($app)
    {
        $app['encrypter.hash'] = function ($app) {
            return new HashGenerator($app['encrypter'], $app['encrypter.rand.generator']);
        };
    }

    protected function registerRand($app)
    {
        $app['encrypter.rand'] = function ($app) {
            return new RandomLib();
        };
    }

    protected function registerRandGenerator($app)
    {
        $app['encrypter.rand.generator'] = function ($app) {
            $generatorStrength = ucfirst(
                $app['settings']->get(
                    'app::crypt.generator.strength',
                    'Medium'
                )
            );

            $generator = "get{$generatorStrength}StrengthGenerator";

            return $app['encrypter.rand']->$generator();
        };
    }
}
