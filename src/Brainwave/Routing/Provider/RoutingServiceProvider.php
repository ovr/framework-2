<?php
namespace Brainwave\Routing\Provider;

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
 */

use Brainwave\Application\Application;
use Brainwave\Contracts\Application\BootableProvider as BootableProviderContract;
use Brainwave\Routing\RouteCollection;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * RoutingServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class RoutingServiceProvider implements ServiceProviderInterface, BootableProviderContract
{
    public function register(Container $container)
    {
        $container['route'] = function ($container) {

            return new RouteCollection(
                $container,
                new Std(),
                new GroupCountBased()
            );
        };
    }

    /**
     * Load The Application Routes
     *
     * The Application routes are kept separate from the application starting
     * just to keep the file a little cleaner. We'll go ahead and load in
     * all of the routes now and return the application to the callers.
     *
    */
    public function boot(Application $app)
    {
        $app['files']->getRequire($app::$paths['path'].'/Http/routes.php');
    }
}
