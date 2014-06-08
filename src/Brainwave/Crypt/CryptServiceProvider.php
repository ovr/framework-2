<?php
namespace Brainwave\Crypt;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Pimple\Container;
use \Brainwave\Crypt\Crypt;
use \Pimple\ServiceProviderInterface;

/**
 * CryptServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class CryptServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['crypt'] = function ($app) {
            return new Crypt(
                $app['settings']->get('crypt.key', '3L43~[[i(98$_[j;3i86[ri.64M2[2[+<)4->yB>6Vv>Rfv0[K$.w={MrDHu@d;'),
                $app['settings']->get('crypt.cipher', MCRYPT_RIJNDAEL_256),
                $app['settings']->get('crypt.mode', 'ctr')
            );
        };
    }
}
