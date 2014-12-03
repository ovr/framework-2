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

use Brainwave\Contracts\Events\Dispatcher;
use Brainwave\Contracts\View\Factory as FactoryContract;
use Brainwave\Support\Arr;
use Brainwave\Support\Collection;
use Brainwave\Support\Str;
use Brainwave\View\Engines\EngineResolver;
use Pimple\Container;

/**
 * Factory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Factory extends Collection implements FactoryContract
{
    /**
     * Container
     *
     * @var \Pimple\Container
     */
    protected $container;

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
        'phtml' => 'php',
        'html'  => 'html',
        'json'  => 'json',
    ];

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Constructor
     *
     * @param \Brainwave\View\Engines\EngineResolver $engines
     * @param ViewFinderInterface                    $finder
     * @param \Brainwave\Contracts\Events\Dispatcher $events
     */
    public function __construct(
        EngineResolver $engines,
        ViewFinderInterface $finder,
        Dispatcher $events
    ) {
        $this->engines = $engines;
        $this->finder  = $finder;
        $this->events  = $events;

        if ($this->container['settings']['view::items'] !== null) {
            $this->data = array_merge($this->container['settings']['view::items'], $this->data, $this->shared);
        } else {
            $this->data = array_merge($this->data, $this->shared);
        }
    }

    /**
     * Get the appropriate view engine for the given path.
     *
     * @param string $path
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
     * @param string $view
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
     * @param string   $name
     * @param \Closure $callback
     * @param integer  $priority
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
     * @param string   $extension
     * @param string   $engine
     * @param \Closure $resolver
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
     * @param string $path
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
     * @param ViewFinderInterface $finder
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
     * Get the pimple container instance.
     *
     * @return \Pimple\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
    /**
     * Set the pimple container instance.
     *
     * @param \Pimple\Container $container
     *
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Share a piece of data across all views.
     *
     * @param string $name
     * @param mixed  $data the data
     *
     * @return self
     */
    public function share($name, $data = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->shared[$k] = $v;
            }
        } else {
            $this->shared[$name] = $data;
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
