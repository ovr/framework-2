<?php
namespace Brainwave\Session;

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
use \Brainwave\Session\SessionFactory;

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
        $app['session'] = function ($app) {
            $session = new SessionFactory($app['crypt']);
            $session->setSessionHandler($app['settings']['session.handler']);
            $session->start();
            if ($app['settings']['session.encrypt'] === true) {
                $session->decrypt($app['crypt']);
            }

            return $session;
        };
    }
}
