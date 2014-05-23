<?php namespace Brainwave\Database;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RedBean_Facade as R;
use \Brainwave\Workbench\Workbench;
use \Brainwave\Support\Services\Interfaces\ServiceProviderInterface;

class RedBeanServiceProvider implements ServiceProviderInterface
{

    public function register(Workbench $app)
    {
        $app['db'] = $app->share(function () use ($app) {
            $options = array(
                'dsn'      => null,
                'username' => null,
                'password' => null,
                'frozen'   => false,
            );
            if (null !== $app->config('db.options')) {
                $options = array_replace($options, $app->config('db.options'));
            }
            R::setup(
                $options['dsn'],
                $options['username'],
                $options['password'],
                $options['frozen']
            );
        });

    }

    public function boot(Workbench $app)
    {
    }
}
