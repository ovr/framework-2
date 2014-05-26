<?php
namespace Brainwave\View;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Collection\Collection;
use \Brainwave\View\Engines\PhpEngine;
use \Brainwave\View\Engines\JsonEngine;
use \Brainwave\View\Engines\EngineResolver;
use \Brainwave\View\Interfaces\ViewInterface;
use \Brainwave\View\Interfaces\ArrayableInterface;
use \Brainwave\View\Interfaces\ViewFactoryInterface;

/**
 * ViewFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class ViewFactory extends Collection implements ViewInterface, ViewFactoryInterface
{
     /**
     * App
     * @var \Brainwave\Workbanch\Workbanch
     */
    protected $app;

    /**
     * [$engine description]
     * @var [type]
     */
    protected $engine;

    /**
     * viewFactoryResolver
     * @var \Closure
     */
    protected $viewFactoryResolver;

    /**
     * [$debug description]
     * @var [type]
     */
    protected $debug;

    /**
     * Register a view extension.
     * @var array
     */
    protected $extensions;

    /**
     * [$items description]
     * @var array
     */
    protected $data = array();

    /**
     * [$customeEngines description]
     * @var array
     */
    protected $customeEngines = array();

    /**
     * [$engineResolver description]
     * @var [type]
     */
    protected $engineResolver;

    /**
     * Constructor
     * @param  \Brainwave\Workbench\Workbench  $app
     * @param  \Closure   $factory
     */
    public function __construct(Workbench $app)
    {
        //App
        $this->app = $app;

        //
        if (!is_null($this->app->config('view.engine'))) {
            $this->customeEngines = $this->app->config('view.engine');
        }

        //
        $this->engine = $this->engineResolver(new EngineResolver());

        //Set extension
        $this->extensions = (!is_null($this->app->config('view.extensions'))) ? $this->app->config('view.extensions') : '.php';

        //
        $this->registerEngineResolver();

        //Initialize set with these items
        parent::__construct($this->registerItems());
    }

    /**
     * Get registered extensions.
     * @return array
     */
    protected function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Register all data
     * @return items
     */
    protected function registerItems()
    {
        if (!is_null($this->app->config('view.items'))) {
            $data = array_merge($this->app->config('view.items'), $this->getData());
        } else {
            $data = $this->getData();
        }

        return $data instanceof ArrayableInterface ? $data->toArray() : $data;
    }

    /**
     * Register the engine resolver instance.
     * @return void
     */
    protected function registerEngineResolver()
    {
        $resolver = $this->engine;

        // Next we will register the various engines with the resolver so that the
        // environment can resolve the engines it needs for various views based
        // on the extension of view files. We call a method for each engines.
        $engines = array_merge(array('php' => 'php', 'json' => 'json'), $this->customeEngines);

        foreach ($engines as $engineName => $engineClass) {
            if ($engineName === 'php' || $engineName === 'json') {
                $this->{'register'.ucfirst($engineClass).'Engine'}($resolver);
            } elseif (!is_null($this->app->config('view.compiler'))) {
                foreach ($this->app->config('view.compiler') as $compilerName => $compilerClass) {
                    if ($engineName === $compilerClass) {
                        $this->registerCustomeEngine($engineName, $engineClass($compilerClass($this->app->config('view.cache'))), $resolver);
                    }
                }
            } else {
                $this->registerCustomeEngine($engineName, $engineClass, $resolver);
            }
        }
    }

    /**
     * Register the PHP engine implementation.
     * @param  \Brainwave\View\Engines\EngineResolver  $resolver
     * @return void
     */
    protected function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () { return new PhpEngine(); });
    }

    /**
     * Register the Json engine implementation.
     * @param  \Brainwave\View\Engines\EngineResolver  $resolver
     * @return void
     */
    protected function registerJsonEngine($resolver)
    {
        $resolver->register('json', function () { return new JsonEngine($this->app, $this); });
    }

    /**
     * Register custome engine implementation.
     * @param $engineName
     * @param $engineClass
     * @param \Brainwave\View\Engines\EngineResolver $resolver
     * @return void
     */
    protected function registerCustomeEngine($engineName, $engineClass, $resolver)
    {
        $eClass = new $engineClass($this->app);
        $resolver->register($engineName, function () use ($eClass) { return $eClass; });
    }

    /**
     * Display template
     * This method echoes the rendered template to the current output buffer
     * @param  string $template Pathname of template file relative to templates directory
     * @api
     */
    public function make($engine = 'php', $template = null)
    {
        echo $this->fetch($engine, $template);
    }

    /**
     * Fetch template
     *
     * This method returns the rendered template. This is useful if you need to capture
     * a rendered template into a variable for futher processing.
     * @var    string $template Pathname of template file relative to templates directory
     * @return string           The rendered template
     * @api
     */
    public function fetch($engine = 'php', $template = null)
    {
        return $this->render($engine, $template);
    }

    /**
     * Get the evaluated contents of the view.
     * @var    string $template Pathname of template file relative to templates directory
     * @return string
     */
    protected function render($engine = 'php', $template = null)
    {
        if (is_string($template) && $engine == 'php' && $engine != 'json') {

            $explodeTemplate = explode('|', $template, 2);

            if (!empty($explodeTemplate[0]) && !empty($explodeTemplate[1])) {
                foreach ($this->app->config('view.template.paths') as $pathName => $path) {
                    if (trim($explodeTemplate[0]) == $pathName) {
                        $templatePath = preg_replace('/([^\/]+)$/', '$1/', $path);
                    }
                }
                $path = preg_replace('/([^\/]+)$/', '$1/', $templatePath) . trim($explodeTemplate[1]) . $this->getExtensions();
            } else {
                $path = $this->app->config('view.default.template.path') . $template . $this->getExtensions();
            }
        } elseif ($engine == 'json') {
            $path = $template;
        } else {
            $path = $template . $this->getExtensions();
        }

        //Replace data
        $this->replace($this->getData());

        $engineR = $this->engine->resolve($engine);

        return $engineR->set($path)->get($this->all());
    }

    /**
     * Add a piece of data to the view.
     * @param  string|array  $key
     * @param  mixed   $value
     * @return \Brainwave\View\ViewFactory
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Get a piece of data from the view.
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value = null)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     * @param  string  $key
     * @return bool
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Dynamically bind parameters to the view.
     * @param  string  $method
     * @param  array   $parameters
     * @return \Brainwave\View\ViewFactory
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (starts_with($method, 'with')) {
            return $this->with(snake_case(substr($method, 4)), $parameters[0]);
        }

        throw new \BadMethodCallException("Method [$method] does not exist on view.");
    }

    /**
     * EngineResolver
     * @param EngineResolver $resolver new instance of EngineResolver
     */
    protected function engineResolver(EngineResolver $resolver)
    {
        return $resolver;
    }

    /**
     * Gets a variable.
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Assign a variable to the template.
     * @param mixed $name
     * @param mixed $data the data
     * @return self
     */
    public function setData($name, $data = null)
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
}
