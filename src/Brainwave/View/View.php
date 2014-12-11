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
 */

use Brainwave\Contracts\Support\ArrayableInterfaces;
use Brainwave\Contracts\View\View as ViewContract;
use Brainwave\Support\Collection;
use Brainwave\Support\Str;
use Brainwave\View\Engines\EngineInterface;

/**
 * View
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class View extends Collection implements ViewContract
{
    /**
     * Create a new view instance.
     *
     * @param \Brainwave\View\Factory                 $factory
     * @param \Brainwave\View\Engines\EngineInterface $engine
     * @param string                                  $view
     * @param string                                  $path
     * @param array                                   $data
     *
     * @return void
     */
    public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = array())
    {
        $this->view    = $view;
        $this->path    = $path;
        $this->engine  = $engine;
        $this->factory = $factory;
        $this->data    = $data;

        //Initialize set with these items
        parent::__construct($data instanceof ArrayableInterfaces ? $data->toArray() : $data);
    }

    /**
     * Display template
     *
     * This method echoes the rendered template to the current output buffer
     *
     * @param string $template Pathname of template file relative to templates directory
     */
    public function make($engine = 'php', $template = null, array $data = [])
    {
        echo $this->fetch($engine, $template, $data);
    }

    /**
     * Fetch template
     *
     * This method returns the rendered template. This is useful if you need to capture
     * a rendered template into a variable for futher processing.
     *
     * @var    string $template Pathname of template file relative to templates directory
     *
     * @return string The rendered template
     */
    public function fetch($engine = 'php', $template = null, array $data = [])
    {
        return $this->render($engine, $template, $data);
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @var    string $template Pathname of template file relative to templates directory
     * @param string $template
     *
     * @return string
     */
    protected function render($engine = 'php', $template = null, array $data = [])
    {
        $this->with($data);

        if (is_string($template) && $engine === 'php' && $engine !== 'json') {
            $explodeTemplate = explode('::', $template, 2);

            if (!empty($explodeTemplate[0]) && !empty($explodeTemplate[1])) {
                foreach ($this->container['settings']['view::template.paths'] as $pathName => $path) {
                    if (trim($explodeTemplate[0]) === $pathName) {
                        $templatePath = preg_replace('/([^\/]+)$/', '$1/', $path);
                    }
                }

                $path = preg_replace('/([^\/]+)$/', '$1/', $templatePath).
                        trim($explodeTemplate[1]).
                        $this->getExtensions();
            } else {
                $path = $this->container['settings']['view::default.template.path'].
                        $template.
                        $this->getExtensions();
            }
        } elseif ($engine == 'json') {
            $path = $template;
        } else {
            $path = $template.$this->getExtensions();
        }

        //Replace data
        $this->replace($this->gatherData());

        $engineR = $this->engine->resolve($engine);

        return $engineR->set($path)->get($this->all());
    }

    /**
     * Add a piece of data to the view.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return View
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
     * Add a view instance to the view data.
     *
     * @param string $key
     * @param string $view
     * @param array  $data
     *
     * @return $this
     */
    public function nest($factory, $key, $view, array $data = [])
    {
        return $this->with($key, $this->make($factory, $view, $data));
    }

    /**
     * Dynamically bind parameters to the view.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return View
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }

        throw new \BadMethodCallException("Method [$method] does not exist on view.");
    }

    /**
     * Get a piece of data from the view.
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($key, $value = null)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param string $key
     *
     * @return boolean|null
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
}
