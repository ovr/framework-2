<?php
namespace Brainwave\Routing;

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
use \Brainwave\Routing\Router;
use \Brainwave\Routing\Redirector;
use \Brainwave\Workbench\Workbench;
use \Brainwave\Routing\RouteFactory;
use \Brainwave\Routing\UrlGenerator;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Routing\Controller\ControllerCollection;
use \Brainwave\Workbench\Interfaces\BootableProviderInterface;

/**
 * RoutingServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class RoutingServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    protected $app;

    public function register(Container $app)
    {
        $this->app = $app;

        $this->registerRouter();
        $this->registerRedirector();
        $this->registerControllers();
        $this->registerRouteFactory();
        $this->registerUrlGenerator();
        $this->registerRouteResolver();
        $this->registerControllersFactory();
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app['router'] = function ($app) {
            return new Router($app);
        };
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app['url'] = function ($app) {
            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $routes = $app['router']->getAllRoutes();

            return new UrlGenerator($routes, $app['request']);
        };
    }

    /**
     * Register the Redirector service.
     *
     * @return void
     */
    protected function registerRedirector()
    {
        $this->app['redirect'] = function ($app) {
            return new Redirector($app['url'], $app);
        };
    }

    /**
     * Register the Route factory resolver service.
     *
     * @return void
     */
    protected function registerRouteResolver()
    {
        $this->app['routes.resolver'] = function ($app) {
            $options = [
                'routeClass'    => $app['settings']['http::route.class'],
                'caseSensitive' => $app['settings']['http::route.case_sensitive'],
                'routeEscape'   => $app['settings']['http::route.escape']
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
    protected function registerRouteFactory()
    {
        $this->app['routes.factory'] = function ($app) {
            return new RouteFactory($app, $app['routes.resolver'], $app['controller.factory']);
        };
    }

    /**
     * Register Controllers Factory service.
     *
     * @return void
     */
    protected function registerControllersFactory()
    {
        $this->app['controller.factory'] = function ($class) {
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
    protected function registerControllers()
    {
        $this->app['controllers'] = function ($app) {
            return new ControllerCollection($app);
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
    public function boot(Workbench $app)
    {
        $app['files']->getRequire($app::$paths['path'].'/Http/routes.php');
    }
}
