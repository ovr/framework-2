<?php namespace Brainwave\Crypt;

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
 * Crypt Provider.
 *
 * @author Daniel Bannert <d.bannert@anolilab.de>
 */
class CryptServiceProvider implements ServiceProviderInterface
{
    public function register(Workbench $app)
    {
        $app['crypt'] = function ($app) {
            return new Crypt(
                $app['settings']['crypt.key'],
                $app['settings']['crypt.cipher'],
                $app['settings']['crypt.mode']
            );
        };
    }

    public function boot(Workbench $app)
    {
    }
}
