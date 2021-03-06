<?php
namespace Brainwave\Exception;

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

use \Whoops\Run;
use \Pimple\Container;
use \Brainwave\Support\Arr;
use \Pimple\ServiceProviderInterface;
use \Whoops\Handler\PlainTextHandler;
use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;
use \Brainwave\Exception\ExceptionHandler;
use \Brainwave\Exception\Displayer\PlainDisplayer;
use \Brainwave\Exception\Displayer\WhoopsDisplayer;

/**
 * ExceptionServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class ExceptionServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function register(Container $app)
    {
        $this->app = $app;

        $this->registerDisplayers();

        $app['exception'] = function ($app) {
            return new ExceptionHandler($app);
        };
    }

    /**
     * Register the exception displayers.
     *
     * @return void
     */
    protected function registerDisplayers()
    {
        $this->registerPlainDisplayer();

        $this->registerDebugDisplayer();
    }

    /**
     * Register the Whoops error display service.
     *
     * @return void
     */
    protected function registerWhoops()
    {
        $this->registerWhoopsHandler();
        $this->registerPrettyWhoopsHandlerInfo();

        $this->app['whoops'] = function ($app) {
            // We will instruct Whoops to not exit after it displays the exception as it
            // will otherwise run out before we can do anything else. We just want to
            // let the framework go ahead and finish a request on this end instead.
            Arr::with($whoops = new Run)->allowQuit(false);

            $whoops->writeToOutput(true);
            $whoops->pushHandler($app['whoops.plain.handler']);
            $whoops->pushHandler($app['whoops.handler']);
            $whoops->pushHandler($app['whoops.handler.info']);

            return $whoops;
        };
    }

    /**
     * Register the plain exception displayer.
     *
     * @return void
     */
    protected function registerPlainDisplayer()
    {
        $this->app['exception.plain'] = function ($app) {
            // If the application is running in a console environment, we will just always
            // use the debug handler as there is no point in the console ever returning
            // out HTML. This debug handler always returns JSON from the console env.
            if ($app['environment']->runningInConsole()) {
                return $app['exception.debug'];
            } else {
                return new PlainDisplayer(
                    $app,
                    strtolower($app['settings']->get('app::charset', 'en')),
                    $app['environment']->runningInConsole()
                );
            }
        };
    }

    /**
     * Register the Whoops exception displayer.
     *
     * @return void
     */
    protected function registerDebugDisplayer()
    {
        $this->registerWhoops();

        $this->app['exception.debug'] = function ($app) {
            return new WhoopsDisplayer(
                $app,
                strtolower($app['settings']->get('app::charset', 'en')),
                $app['environment']->runningInConsole()
            );
        };
    }

    /**
     * Register the Whoops handler for the request.
     *
     * @return void
     */
    protected function registerWhoopsHandler()
    {
        if ($this->shouldReturnJson()) {
            $this->app['whoops.handler'] = function () {
                return new JsonResponseHandler;
            };
        } else {
            $this->registerPlainTextHandler();

            $this->registerPrettyWhoopsHandler();
        }
    }

    /**
     * Register the Whoops handler for the request.
     *
     * @return void
     */
    protected function registerPlainTextHandler()
    {
        $this->app['whoops.plain.handler'] = function ($app) {
            return new PlainTextHandler($app['logger']->getMonolog());
        };
    }

    /**
     * Determine if the error provider should return JSON.
     *
     * @return bool
     */
    protected function shouldReturnJson()
    {
        return $this->app['environment']->runningInConsole() || $this->requestWantsJson();
    }

    /**
     * Determine if the request warrants a JSON response.
     *
     * @return bool
     */
    protected function requestWantsJson()
    {
        return $this->app['request']->isAjax() || $this->app['request']->isJson();
    }

    /**
     * Register the "pretty" Whoops handler.
     *
     * @return void
     */
    protected function registerPrettyWhoopsHandler()
    {
        $this->app['whoops.handler'] = function ($app) {
            Arr::with($handler = new PrettyPageHandler)->setEditor('sublime');

            $handler->setResourcesPath(dirname(__FILE__).DS.'WhoopsResources');

            return $handler;
        };
    }

    /**
     * Retrieves info on the Silex environment and ships it off
     * to the PrettyPageHandler's data tables:
     * This works by adding a new handler to the stack that runs
     * before the error page, retrieving the shared page handler
     * instance, and working with it to add new data tables
     *
     * @return void
     */
    protected function registerPrettyWhoopsHandlerInfo()
    {
        $this->app['whoops.handler.info'] = function ($app) {
            // Retrieves info on the Brainwave environment and ships it off
            // to the PrettyPageHandler's data tables:
            // This works by adding a new handler to the stack that runs
            // before the error page, retrieving the shared page handler
            // instance, and working with it to add new data tables
            try {
                $request = $app['request'];
            } catch (\RuntimeException $e) {
                // This error occurred too early in the application's life
                // and the request instance is not yet available.
                return;
            }

            $app['whoops.handler']->setPageTitle("We're all going to be fired!");

            $app['whoops.handler']->addDataTable('Brainwave Application', [
                'Charset'          => $request->getContentCharset(),
                'Locale'           => $request->getContentCharset() ?: '<none>',
                'Route Class'      => $app['settings']['http::route.class'],
                'Application Class'=> get_class($app)
            ]);

            $app['whoops.handler']->addDataTable('Brainwave Application (Request)', [
                'Base URL'    => $request->getUrl(),
                'URI'         => $request->getScriptName(),
                'Request URI' => $request->getPathInfo(),
                'Path'        => $request->getPath(),
                'Query String'=> $request->params() ?: '<none>',
                'HTTP Method' => $request->getMethod(),
                'Script Name' => $request->getScriptName(),
                'Scheme'      => $request->getScheme(),
                'Port'        => $request->getPort(),
                'Protocol'    => $request->getProtocolVersion(),
                'Host'        => $request->getHost(),
            ]);

            return $app['whoops.handler'];
        };
    }
}
