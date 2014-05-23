<?php namespace Brainwave\Event;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Event\EventManager;
use \Brainwave\Workbench\Workbench;
use \Brainwave\Support\Services\Interfaces\ServiceProviderInterface;

/**
 * Event Provider.
 *
 * @author Daniel Bannert <d.bannert@anolilab.de>
 */
class EventServiceProvider implements ServiceProviderInterface
{
    public function register(Workbench $app)
    {
        $app['event'] = function ($app) {
            $event = new EventManager();
            return $event;
        };
    }

    public function boot(Workbench $app)
    {
    }
}
