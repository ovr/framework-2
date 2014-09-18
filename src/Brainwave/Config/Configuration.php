<?php
namespace Brainwave\Config;

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

use \Brainwave\Config\FileLoader;
use \Brainwave\Config\Interfaces\ConfigurationInterface;
use \Brainwave\Config\Interfaces\ConfigurationHandlerInterface;

/**
 * Configuration
 * Uses a ConfigurationHandler class to parse configuration data, accessed as an array.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 */
class Configuration implements ConfigurationInterface, \IteratorAggregate
{
    /**
     * Handler for Configuration values
     *
     * @var mixed
     */
    protected $handler;

    /**
     * Storage array of values
     *
     * @var array
     */
    protected $values = array();

    /**
     * Config folder path
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor
     *
     * @param mixed $handler
     */
    public function __construct(ConfigurationHandlerInterface $handler, FileLoader $loader)
    {
        $this->setHandler($handler);
        $this->setLoader($loader);
    }

    /**
     * Set Brainwave's defaults using the handler
     *
     * @param array $values
     */
    public function setArray(array $values)
    {
        $this->handler->setArray($values);
    }


    /**
     * Get the default settings
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Set a configuration handler and provide it some defaults
     *
     * @param \Brainwave\Config\Interfaces\ConfigurationHandlerInterface $handler
     */
    public function setHandler(ConfigurationHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Get the configuration handler for access
     *
     * @return \Brainwave\Config\Interfaces\ConfigurationHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set the configuration loader
     *
     * @return \Brainwave\Config\FileLoader
     */
    public function setLoader(FileLoader $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * Get the configuration loader
     *
     * @return \Brainwave\Config\FileLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Load the given configuration group.
     *
     * @param  string  $file
     * @param  string  $namespace
     * @param  string  $environment
     * @param  string  $group
     *
     * @return void
     */
    public function bind($file, $namespace = null, $environment = null, $group = null)
    {
        $config = $this->getLoader()->load($file, $namespace, $environment, $group);
        return $this->setArray($config);
    }

    /**
     * Apply any cascades to an array of package options.
     *
     * @param  string  $package
     * @param  string  $group
     * @param  string  $env
     * @param  array   $items
     *
     * @return array
     */
    public function cascadePackage($file, $package = null, $group = null, $env = null, $items = null)
    {
        return $this->getLoader()->cascadePackage($file, $package, $group, $env, $items);
    }

    /**
     * Get a value
     *
     * @param  string $key
     *
     * @return mixed       The value of a setting
     */
    public function get($key, $default)
    {
        if (is_null($this->handler[$key])) {
            return $default;
        } else {
            return is_callable($this->handler[$key]) ? call_user_func($this->handler[$key]) : $this->handler[$key];
        }
    }

    /**
     * Set a value
     *
     * @param  string $key
     * @param  mixed $value
     */
    public function set($key, $value)
    {
        $this->handler[$key] = $value;
    }

    /**
     * Set path to config folder
     *
     * @param string $path
     */
    public function addPath($path)
    {
        $this->path = $path;
        $this->getLoader()->addDefaultPath($path);
        return $this;
    }

    /**
     * Get config folder path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get a value
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->handler[$key];
    }

    /**
     * Set a value
     *
     * @param  string $key
     * @param  mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->handler[$key] = $value;
    }

    /**
     * Check a value exists
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->handler[$key]);
    }

    /**
     * Remove a value
     *
     * @param  string $key
     */
    public function offsetUnset($key)
    {
        unset($this->handler[$key]);
    }

    /**
     * Get an ArrayIterator for the stored items
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
