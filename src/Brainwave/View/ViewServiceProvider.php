<?php
namespace Brainwave\View;

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
use \Brainwave\View\ViewFactory;
use \Pimple\ServiceProviderInterface;
use \Brainwave\View\Engines\EngineResolver;
use \Brainwave\View\Engines\Adapter\Php as PhpEngine;
use \Brainwave\View\Engines\Adapter\Json as JsonEngine;

/**
 * ViewServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    /**
     * Register view
     */
    public function register(Container $app)
    {
        $this->registerEngineResolver($app);
        $this->registerViewFinder($app);
        $this->registerFactory($app);
    }

    /**
     * Register the engine engines instance.
     *
     * @param Container $app
     * @return void
     */
    protected function registerEngineResolver($app)
    {
        $engines = new EngineResolver();

        // Next we will register the various engines with the engines so that the
        // environment can resolve the engines it needs for various views based
        // on the extension of view files. We call a method for each engines.
        foreach (['php' => 'php', 'json' => 'json', 'phtml' => 'php'] as $engineName => $engineClass) {
            $this->{'register'.ucfirst($engineClass).'Engine'}($engines);
        }

        if ($app['settings']['view::compilers'] !== null) {

            foreach ($app['settings']['view::compilers'] as $compilerName => $compilerClass) {
                if ($engineName === $compilerClass[0]) {
                    $this->registercustomEngine(
                        $engineName,
                        call_user_func_array($compilerClass[0], (array) $compilerClass[1]),
                        $engines
                    );
                }
            }

        }
    }

    /**
     * Register custom engine implementation.
     *
     * @param string                                 $engineName
     * @param string                                 $engineClass
     * @param \Brainwave\View\Engines\EngineResolver $engines
     *
     * @return void
     */
    protected function registercustomEngine($engineName, $engineClass, $engines)
    {
        $engines->register($engineName, function () use ($engineClass) {
            return $engineClass;
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Brainwave\View\Engines\EngineResolver $engines
     *
     * @return void
     */
    protected function registerPhpEngine($engines)
    {
        $engines->register('php', function () {
            return new PhpEngine();
        });
    }

    /**
     *
     * Alias for PhpEngine
     *
     */
    protected function registerPhtmlEngine($engines)
    {
        return $this->registerPhpEngine($engines);
    }

    /**
     * Register the Json engine implementation.
     *
     * @param  \Brainwave\View\Engines\EngineResolver $engines
     *
     * @return void
     */
    protected function registerJsonEngine($engines)
    {
        $engines->register('json', function () {
            return new JsonEngine($this->app, $this);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @param Container $app
     * @return void
     */
    protected function registerViewFinder($app)
    {
        $app['view.finder'] = function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        };
    }

    /**
     * Register the view environment.
     *
     * @param Container $app
     * @return void
     */
    protected function registerFactory($app)
    {
        $app['view'] = function ($app) {

            $env = new Factory($app['view.engine.resolver'], $app['view.finder'], $app['events']);

            // We will also set the container instance on this view environment.
            $env->setContainer($app);

            $env->share('app', $app);

            return $env;
        };
    }
}
