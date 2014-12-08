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

use Brainwave\View\Engines\Adapter\Json as JsonEngine;
use Brainwave\View\Engines\Adapter\Php as PhpEngine;
use Brainwave\View\Engines\EngineResolver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
     * Container instance
     *
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * Register view
     */
    public function register(Container $container)
    {
        $this->container = $container;
        $this->registerEngineResolver($container);
        $this->registerViewFinder($container);
        $this->registerFactory($container);
    }

    /**
     * Register the engine engines instance.
     *
     * @param Container $container
     *
     * @return void
     */
    protected function registerEngineResolver($container)
    {
        $engines = new EngineResolver();

        // Next we will register the various engines with the engines so that the
        // environment can resolve the engines it needs for various views based
        // on the extension of view files. We call a method for each engines.
        foreach (['php' => 'php', 'json' => 'json', 'phtml' => 'php'] as $engineName => $engineClass) {
            $this->{'register'.ucfirst($engineClass).'Engine'}($engines);
        }

        if ($container['settings']['view::compilers'] !== null) {
            foreach ($container['settings']['view::compilers'] as $compilerName => $compilerClass) {
                if ($compilerName === $compilerClass[0]) {
                    $this->registercustomEngine(
                        $compilerName,
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
     * @param \Brainwave\View\Engines\EngineResolver $engines
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
     * Alias for PhpEngine
     *
     * @method registerPhpEngine
     */
    protected function registerPhtmlEngine($engines)
    {
        $this->registerPhpEngine($engines);
    }

    /**
     * Register the Json engine implementation.
     *
     * @param \Brainwave\View\Engines\EngineResolver $engines
     *
     * @return void
     */
    protected function registerJsonEngine($engines)
    {
        $engines->register('json', function () {
            return new JsonEngine($this->container, $this);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @param Container $container
     *
     * @return void
     */
    protected function registerViewFinder($container)
    {
        $container['view.finder'] = function ($container) {
            return new ViewFinder($container['files'], $container['config']['view.paths']);
        };
    }

    /**
     * Register the view environment.
     *
     * @param Container $container
     *
     * @return void
     */
    protected function registerFactory($container)
    {
        $container['view'] = function ($container) {

            $env = new Factory($container['view.engine.resolver'], $container['view.finder'], $container['events']);

            // We will also set the container instance on this view environment.
            $env->setContainer($container);

            $env->share('app', $container);

            return $env;
        };
    }
}
