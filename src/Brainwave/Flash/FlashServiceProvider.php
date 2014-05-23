<?php namespace Brainwave\Flash;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Brainwave\Workbench\Workbench;
use Brainwave\Support\Services\Interfaces\ServiceProviderInterface;

/**
 * Flash Provider.
 *
 * @author Daniel Bannert <d.bannert@anolilab.de>
 */
class FlashServiceProvider implements ServiceProviderInterface
{
    public function register(Workbench $app)
    {
        $app['flash'] = function ($app) {
            $flash = new Flash($app['session'], $app['settings']['session.flash_key']);
            if ($app['settings']['view'] instanceof ViewInterface) {
                $app['view']->set('flash', $flash->getMessages());
            }

            return $flash;
        };
    }

    public function boot(Workbench $app)
    {
    }
}
