<?php
namespace Brainwave\Routing;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
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
use \Brainwave\Routing\UrlGenerator;
use \Pimple\ServiceProviderInterface;

/**
 * RoutingServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class RoutingServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function register(Container $app)
    {
        $this->app = $app;
        $this->registerRouter();
        $this->registerUrlGenerator();
        $this->registerRedirector();
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app['router'] = function ($c) {
            return new Router();
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
            return new Redirector($app['url']);
        };
    }
}
