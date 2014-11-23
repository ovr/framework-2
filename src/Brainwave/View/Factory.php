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
use \Brainwave\Support\Str;
use \Brainwave\Support\Arr;
use \Brainwave\View\Engines\EngineResolver;
use \Brainwave\Contracts\View\Factory as FactoryContract;
use \Brainwave\Contracts\Support\Arrayable as ArrayableContracts;

/**
 * ViewFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Factory implements FactoryContract
{
    /**
     * Container
     *
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * The engines instance.
     *
     * @var \Brainwave\View\Engines\EngineResolver
     */
    protected $engines;

    /**
     * The view finder implementation.
     *
     * @var \Brainwave\View\Interafaces\ViewFinderInterface
     */
    protected $finder;

    /**
     * The event dispatcher instance.
     *
     * @var \Brainwave\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The engine implementation.
     *
     * @var \Brainwave\Contracts\View\Engines
     */
    protected $engine;

    /**
     * ViewFactoryResolver
     *
     * @var \Closure
     */
    protected $viewFactoryResolver;

    /**
     * Debug
     *
     * @var string
     */
    protected $debug;

    /**
     * Register a view extension.
     *
     * @var array
     */
    protected $extensions = [
        'php'   => 'php',
        'php'   => 'phtml',
        'html'  => 'html'
    ];

    /**
     * All registered custom engines
     *
     * @var array
     */
    protected $customEngines = [];

    /**
     * Resolve the engine instance
     *
     * @var \Brainwave\View\Engines\EngineResolver
     */
    protected $engineResolver;

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Constructor
     *
     * @param \Pimple\Container                               $app
     * @param \Brainwave\View\Engines\EngineResolver          $engines
     * @param \Brainwave\View\Interafaces\ViewFinderInterface $finder
     * @param \Brainwave\Contracts\Events\Dispatcher          $events
     */
    public function __construct(
        Container $app,
        EngineResolver $engines,
        ViewFinderInterface $finder,
        Dispatcher $events
    ) {
        $this->app     = $app;
        $this->engines = $engines;
        $this->finder  = $finder;
        $this->events  = $events;

        //
        $this->customEngines = $this->app['settings']->get('view::engine', 'plates');

        //
        $this->registerEngineResolver();

        //Initialize set with these items
        parent::__construct($this->registerItems());
    }

    /**
     * Register all data.
     *
     * @return items
     */
    protected function registerItems()
    {
        if ($this->app['settings']->get('view::items', null) !== null) {
            $data = array_merge($this->app['settings']['view::items'], $this->gatherData());
        } else {
            $data = $this->gatherData();
        }

        return $data instanceof ArrayableContracts ? $data->toArray() : $data;
    }

    /**
     * Register the engine engines instance.
     *
     * @return void
     */
    protected function registerEngineResolver()
    {
        $engines = $this->engine;

        // Next we will register the various engines with the engines so that the
        // environment can resolve the engines it needs for various views based
        // on the extension of view files. We call a method for each engines.
        $engines = array_merge(['php' => 'php', 'json' => 'json'], $this->customEngines);

        foreach ($engines as $engineName => $engineClass) {
            if ($engineName === 'php' || $engineName === 'json') {
                $this->{'register'.ucfirst($engineClass).'Engine'}($engines);
            } elseif ($this->app['settings']->get('view::compiler', null) !== null) {

                foreach ($this->app['settings']->get('view::compiler', []) as $compilerName => $compilerClass) {
                    if ($engineName === $compilerClass) {
                        $this->registercustomEngine(
                            $engineName,
                            $engineClass($compilerClass($this->app['settings']->get('view::cache', null))),
                            $engines
                        );
                    }
                }

            } else {
                $this->registercustomEngine($engineName, $engineClass, $engines);
            }
        }
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
        $eClass = new $engineClass($this->app);

        $engines->register($engineName, function () use ($eClass) {
            return $eClass;
        });
    }

    /**
     * Get the appropriate view engine for the given path.
     *
     * @param  string $path
     *
     * @return \Brainwave\View\Engines\Interfaces\EngineInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getEngineFromPath($path)
    {
        if (!$extension = $this->getExtension($path)) {
            throw new \InvalidArgumentException("Unrecognized extension in file: $path");
        }

        $engine = $this->extensions[$extension];

        return $this->engines->resolve($engine);
    }

    /**
     * Determine if a given view exists.
     *
     * @param  string $view
     *
     * @return bool
     */
    public function exists($view)
    {
        try {
            $this->finder->find($view);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Add a listener to the event dispatcher.
     *
     * @param  string   $name
     * @param  \Closure $callback
     * @param  integer  $priority
     *
     * @return void
     */
    protected function addEventListener($name, $callback, $priority = null)
    {
        if (is_null($priority)) {
            $this->events->listen($name, $callback);
        } else {
            $this->events->listen($name, $callback, $priority);
        }
    }

    /**
     * Register a valid view extension and its engine.
     *
     * @param  string   $extension
     * @param  string   $engine
     * @param  \Closure $resolver
     *
     * @return void
     */
    public function addExtension($extension, $engine, $resolver = null)
    {
        $this->finder->addExtension($extension);

        if (isset($resolver)) {
            $this->engines->register($engine, $resolver);
        }

        unset($this->extensions[$extension]);

        $this->extensions = array_merge(array($extension => $engine), $this->extensions);
    }

    /**
     * Get the extension to engine bindings.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get the extension used by the view file.
     *
     * @param  string $path
     *
     * @return string
     */
    protected function getExtension($path)
    {
        $extensions = array_keys($this->extensions);

        return Arr::arrayFirst($extensions, function ($key, $value) use ($path) {
            return Str::endsWith($path, $value);
        });
    }

    /**
     * Get the engine resolver instance.
     *
     * @return \Brainwave\View\Engines\EngineResolver
     */
    public function getEngineResolver()
    {
        return $this->engines;
    }

    /**
     * Get the view finder instance.
     *
     * @return \Brainwave\View\Interfaces\ViewFinderInterface
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Set the view finder instance.
     *
     * @param  \Brainwave\View\InterfaceViewFinderInterface $finder
     *
     * @return void
     */
    public function setFinder(ViewFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Brainwave\Contracts\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param  \Brainwave\Contracts\Events\Dispatcher
     *
     * @return void
     */
    public function setDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Gets a variable.
     *
     * @return array
     */
    public function gatherData()
    {
        return array_merge($this->data, $this->shared);
    }

    /**
     * Share a piece of data across all views.
     *
     * @param mixed $name
     * @param mixed $data the data
     *
     * @return self
     */
    public function share($name, $data = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$name] = $data;
        }

        return $this;
    }

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Get all of the registered named views in environment.
     *
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }
}
