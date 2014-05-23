<?php namespace Brainwave\Session;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Session\SessionFactory;
use \Brainwave\Support\Services\Interfaces\ServiceProviderInterface;

/**
 * Session Provider.
 *
 * @author Daniel Bannert <d.bannert@anolilab.de>
 */
class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Workbench $app)
    {
        $app['session'] = function ($app) {
            $session = new SessionFactory();
            $session->setSessionHandler($app['settings']['session.handler']);
            $session->start();
            if ($app['settings']['session.encrypt'] === true) {
                $session->decrypt($app['crypt']);
            }

            return $session;
        };
    }

    public function boot(Workbench $app)
    {
    }
}
