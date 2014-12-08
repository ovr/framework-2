<?php
namespace Brainwave\Encrypter\Provider;

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

use Brainwave\Encrypter\Encrypter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * EncrypterServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class EncrypterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['encrypter'] = function ($container) {
            return new Encrypter(
                $container['rand.generator'],
                $container['settings']->get(
                    'app::crypt.key',
                    '3L43~[[i(98$_[j;3i86[ri.64M2[2[+<)4->yB>6Vv>Rfv0[K$.w={MrDHu@d;'
                ),
                $container['settings']->get('app::crypt.cipher', MCRYPT_RIJNDAEL_256),
                $container['settings']->get('app::crypt.mode', 'cbc')
            );
        };
    }
}
