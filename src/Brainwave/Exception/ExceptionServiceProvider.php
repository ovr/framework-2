<?php
namespace Brainwave\Exception;

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

use \Whoops\Run;
use \Pimple\Container;
use \Brainwave\Support\Arr;
use \Pimple\ServiceProviderInterface;
use \Whoops\Handler\PlainTextHandler;
use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;
use \Brainwave\Exception\Adapter\PlainDisplayer;
use \Brainwave\Exception\Handler as ExceptionHandler;
use \Brainwave\Exception\Adapter\Whoops as WhoopsDisplayer;

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
    protected $container;

    public function register(Container $container)
    {
        $this->container = $container;

        $this->registerDisplayers();

        $container['exception'] = function ($container) {
            return new ExceptionHandler(
                $container,
                $container['logger']->getMonolog(),
                $container['settings']->get('app::debug', true)
            );
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

        $this->container['whoops'] = function ($container) {
            // We will instruct Whoops to not exit after it displays the exception as it
            // will otherwise run out before we can do anything else. We just want to
            // let the framework go ahead and finish a request on this end instead.
            Arr::with($whoops = new Run)->allowQuit(false);

            $whoops->writeToOutput(true);
            $whoops->pushHandler($container['whoops.plain.handler']);
            $whoops->pushHandler($container['whoops.handler']);
            $whoops->pushHandler($container['whoops.handler.info']);

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
        $this->container['exception.plain'] = function ($container) {
            // If the application is running in a console environment, we will just always
            // use the debug handler as there is no point in the console ever returning
            // out HTML. This debug handler always returns JSON from the console env.
            if ($container['environment']->runningInConsole()) {
                return $container['exception.debug'];
            }

            return new PlainDisplayer(
                $container,
                strtolower($container['settings']->get('app::charset', 'en')),
                $container['environment']->runningInConsole()
            );
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

        $this->container['exception.debug'] = function ($container) {
            return new WhoopsDisplayer(
                $container,
                strtolower($container['settings']->get('app::charset', 'en')),
                $container['environment']->runningInConsole()
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
            $this->container['whoops.handler'] = function () {
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
        $this->container['whoops.plain.handler'] = function ($container) {
            return new PlainTextHandler($container['logger']->getMonolog());
        };
    }

    /**
     * Determine if the error provider should return JSON.
     *
     * @return bool
     */
    protected function shouldReturnJson()
    {
        return $this->container['environment']->runningInConsole() || $this->requestWantsJson();
    }

    /**
     * Determine if the request warrants a JSON response.
     *
     * @return bool
     */
    protected function requestWantsJson()
    {
        return $this->container['request']->isAjax() || $this->container['request']->isJson();
    }

    /**
     * Register the "pretty" Whoops handler.
     *
     * @return void
     */
    protected function registerPrettyWhoopsHandler()
    {
        $this->container['whoops.handler'] = function ($container) {
            Arr::with($handler = new PrettyPageHandler)->setEditor('sublime');

            $handler->setResourcesPath(dirname(__FILE__).DS.'Resources');

            return $handler;
        };
    }

    /**
     * Retrieves info on the Narrowspark environment and ships it off
     * to the PrettyPageHandler's data tables:
     *
     * This works by adding a new handler to the stack that runs
     * before the error page, retrieving the shared page handler
     * instance, and working with it to add new data tables
     *
     * @return void
     */
    protected function registerPrettyWhoopsHandlerInfo()
    {
        $this->container['whoops.handler.info'] = function ($container) {
            try {
                $request = $container['request'];
            } catch (\RuntimeException $e) {
                // This error occurred too early in the application's life
                // and the request instance is not yet available.
                return;
            }

            $container['whoops.handler']->setPageTitle("We're all going to be fired!");

            $container['whoops.handler']->addDataTable('Narrowspark Application', [
                'Charset'          => $request->getContentCharset(),
                'Locale'           => $request->getContentCharset() ?: '<none>',
                'Route Class'      => $container['settings']['http::route.class'],
                'Application Class'=> get_class($container)
            ]);

            $container['whoops.handler']->addDataTable('Narrowspark Application (Request)', [
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

            return $container['whoops.handler'];
        };
    }
}
