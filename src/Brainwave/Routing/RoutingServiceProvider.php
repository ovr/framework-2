<?php
namespace Brainwave\Routing;

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

use \Pimple\Container;
use \Brainwave\Routing\Router;
use \Brainwave\Routing\Redirector;
use \Brainwave\Routing\RouteFactory;
use \Brainwave\Routing\UrlGenerator;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Application\Application;
use \Brainwave\Routing\Controller\ControllerCollection;
use \Brainwave\Contracts\Application\BootableProvider as BootableProviderContract;

/**
 * RoutingServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class RoutingServiceProvider implements ServiceProviderInterface, BootableProviderContract
{
    public function register(Container $container)
    {
        $this->registerRouter($container);
        $this->registerRedirector($container);
        $this->registerControllers($container);
        $this->registerRouteFactory($container);
        $this->registerUrlGenerator($container);
        $this->registerRouteResolver($container);
        $this->registerControllersFactory($container);
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter(Container $container)
    {
        $container['router'] = function ($container) {
            return new Router($container);
        };
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator(Container $container)
    {
        $container['url'] = function ($container) {
            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $routes = $container['router']->getAllRoutes();

            return new UrlGenerator($routes, $container['request']);
        };
    }

    /**
     * Register the Redirector service.
     *
     * @return void
     */
    protected function registerRedirector(Container $container)
    {
        $container['redirect'] = function ($container) {
            return new Redirector($container['url'], $container);
        };
    }

    /**
     * Register the Route factory resolver service.
     *
     * @return void
     */
    protected function registerRouteResolver(Container $container)
    {
        $container['routes.resolver'] = function ($container) {
            $options = [
                'routeClass'    => $container['settings']['http::route.class'],
                'caseSensitive' => $container['settings']['http::route.case_sensitive'],
                'routeEscape'   => $container['settings']['http::route.escape']
            ];

            return function ($pattern, $callable) use ($options) {
                return new $options['routeClass'](
                    $pattern,
                    $callable,
                    $options['caseSensitive'],
                    $options['routeEscape']
                );
            };
        };
    }

    /**
     * Register Route service.
     *
     * @return void
     */
    protected function registerRouteFactory(Container $container)
    {
        $container['routes.factory'] = function ($container) {
            return new RouteFactory($container, $container['routes.resolver'], $container['controller.factory']);
        };
    }

    /**
     * Register Controllers Factory service.
     *
     * @return void
     */
    protected function registerControllersFactory(Container $container)
    {
        $container['controller.factory'] = function ($class) {
            return function ($class) {
                return new $class;
            };
        };
    }

    /**
     * Register Controllers service.
     *
     * @return void
     */
    protected function registerControllers(Container $container)
    {
        $container['controllers'] = function ($container) {
            return new ControllerCollection($container);
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
    public function boot(Application $container)
    {
        $container['files']->getRequire($container::$paths['path'].'/Http/routes.php');
    }
}
