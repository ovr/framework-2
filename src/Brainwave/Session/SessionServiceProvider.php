<?php
namespace Brainwave\Session;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Pimple\Container;
use \Brainwave\Support\Str;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Session\SessionManager;
use \Brainwave\Session\SegmentFactory;
use \Brainwave\Session\CsrfTokenFactory;

/**
 * SessionServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['deleteCookie'] = null;
        $app['session'] = function ($app) {
            $session = new SessionManager(
                new SegmentFactory,
                new CsrfTokenFactory($app['encrypter']),
                new Str,
                $_COOKIE,
                $app['deleteCookie']
            );

            $session->start();

            return $session;
        };
    }
}
