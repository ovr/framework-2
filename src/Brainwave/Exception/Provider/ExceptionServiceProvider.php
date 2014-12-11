<?php
namespace Brainwave\Exception\Provider;

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
use Brainwave\Exception\Adapter\Plain as PlainDisplayer;
use Brainwave\Exception\Adapter\Symfony as SymfonyDisplayer;
use Brainwave\Exception\Adapter\Whoops as WhoopsDisplayer;
use Brainwave\Exception\Handler as ExceptionHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

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

    /**
     * Register the exception handler instance.
     *
     * @return void
     */
    public function register(Container $container)
    {
        $this->container = $container;

        $this->registerDisplayers();

        $this->container['exception'] = function ($container) {
            return new ExceptionHandler(
                $container,
                $this->container['logger']->getMonolog(),
                $this->container['settings']['app::debug']
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

        if ($this->container['settings']['app::exception.handler'] === 'whoops') {
            $this->registerWhoopsDebugDisplayer();
        } else {
            $this->registerSymfonyDebugDisplayer();
        }
    }

    /**
     * Register the plain exception displayer.
     *
     * @return void
     */
    protected function registerPlainDisplayer()
    {
        $this->container['exception.plain'] = function () {
            // If the application is running in a console environment, we will just always
            // use the debug handler as there is no point in the console ever returning
            // out HTML. This debug handler always returns JSON from the console env.
            if ($this->container['environment']->runningInConsole()) {
                return $this->container['exception.debug'];
            }

            return new PlainDisplayer();
        };
    }

    /**
     * Register the Whoops exception displayer.
     *
     * @return void
     */
    protected function registerWhoopsDebugDisplayer()
    {
        $this->registerWhoops();

        $this->container['exception.debug'] = function () {
            return new WhoopsDisplayer(
                $this->container['whoops'],
                $this->container['environment']->runningInConsole()
            );
        };
    }

    /**
     * Register the Symfony exception displayer.
     *
     * @return void
     */
    protected function registerSymfonyDebugDisplayer()
    {
        $this->container['exception.debug'] = function () {
            return new SymfonyDisplayer(
                new SymfonyExceptionHandler(),
                $this->shouldReturnJson()
            );
        };
    }

    /**
     * Register the Whoops error display service.
     *
     *
     * @return void
     */
    protected function registerWhoops()
    {
        $this->registerWhoopsHandler();

        $request = $this->container['request'];

        if ($request === null) {
            // This error occurred too early in the application's life
            // and the request instance is not yet available.
            return;
        }

        $this->registerPrettyWhoopsHandlerInfo($request);

        $this->container['whoops'] = function () {
            // We will instruct Whoops to not exit after it displays the exception as it
            // will otherwise run out before we can do anything else. We just want to
            // let the framework go ahead and finish a request on this end instead.
            $whoops = new Run();
            $whoops->allowQuit(false);

            $whoops->writeToOutput(true);
            $whoops->pushHandler($this->container['whoops.handler']);

            if (!$this->shouldReturnJson()) {
                $whoops->pushHandler($this->container['whoops.plain.handler']);
                $whoops->pushHandler($this->container['whoops.handler.info']);
            }

            return $whoops;
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
                return new JsonResponseHandler();
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
        $this->container['whoops.plain.handler'] = function () {
            return new PlainTextHandler($this->container['logger']->getMonolog());
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
     * @return boolean
     */
    protected function requestWantsJson()
    {
        return $this->container['request']->ajax() || $this->container['request']->wantsJson();
    }

    /**
     * Register the "pretty" Whoops handler.
     *
     * @return void
     */
    protected function registerPrettyWhoopsHandler()
    {
        $this->container['whoops.handler'] = function () {
            $handler = new PrettyPageHandler();
            $handler->setEditor($this->container['settings']->get('app::whoops.editor', 'sublime'));

            if ($this->resourcePath() !== null) {
                $handler->addResourcePath($this->resourcePath());
            }

            return $handler;
        };
    }

    /**
     * Get the resource path for Whoops.
     *
     * @return string
     */
    public function resourcePath()
    {
        if (is_dir($path = $this->getResourcePath())) {
            return $path;
        }
    }

    /**
     * Get the Whoops custom resource path.
     *
     * @return string
     */
    protected function getResourcePath()
    {
        $app  = $this->container['app'];
        $base = $app::$paths['path.base'];

        return $base.'/vendor/narrowspark/framework/src/Brainwave/Exception/resources';
    }

    /**
     * Retrieves info on the Narrowspark environment and ships it off
     * to the PrettyPageHandler's data tables:
     *
     * This works by adding a new handler to the stack that runs
     * before the error page, retrieving the shared page handler
     * instance, and working with it to add new data tables
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    protected function registerPrettyWhoopsHandlerInfo(Request $request)
    {
        $this->container['whoops.handler.info'] = function () use ($request) {

            $this->container['whoops.handler']->setPageTitle("We're all going to be fired!");

            $this->container['whoops.handler']->addDataTable('Narrowspark Application', [
                'Version'           => Application::VERSION,
                'Charset'           => $this->container['settings']['app::locale'],
                'Route Class'       => $this->container['settings']['http::route'],
                'Application Class' => get_class($this->container),
            ]);

            $this->container['whoops.handler']->addDataTable('Narrowspark Application (Request)', [
                'URI'          => $request->getUri(),
                'Request URI'  => $request->getRequestUri(),
                'Path Info'    => $request->getPathInfo(),
                'Query String' => $request->getQueryString() ?: '<none>',
                'HTTP Method'  => $request->getMethod(),
                'Script Name'  => $request->getScriptName(),
                'Base Path'    => $request->getBasePath(),
                'Base URL'     => $request->getBaseUrl(),
                'Scheme'       => $request->getScheme(),
                'Port'         => $request->getPort(),
                'Host'         => $request->getHost(),
            ]);

            return $this->container['whoops.handler'];
        };
    }
}
