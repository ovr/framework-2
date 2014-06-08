<?php
namespace Brainwave\Config;

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
     * @var mixed
     */
    protected $handler;

    /**
     * Storage array of values
     * @var array
     */
    protected $values = array();

    /**
     * Caches the configuration data
     * @var array
     */
    protected $cache = array();

    /**
     * Default values
     * @var array
     */
    protected $defaults = array(
        // Application
        'app.footer' => 'narrowspark',
        'app.configs' => array(
            'app' => array(
                'ext' => 'php',
                'namespace' => 'config',
                'env' => '',
                'group' => ''
            ),
            'mail' => array(
                'ext' => 'php',
                'namespace' => 'config',
                'env' => '',
                'group' => ''
            ),
            'cache' => array(
                'ext' => 'php',
                'namespace' => 'config',
                'env' => '',
                'group' => ''
            ),
            'services' => array(
                'ext' => 'php',
                'namespace' => 'config',
                'env' => '',
                'group' => ''
            ),
            'view' => array(
                'ext' => 'php',
                'namespace' => 'config',
                'env' => '',
                'group' => ''
            ),
            'autoload' => array(
                'ext' => 'php',
                'namespace' => 'config',
                'env' => '',
                'group' => ''
            ),
        ),
        // Cookies
        'cookies.encrypt' => false,
        'cookies.lifetime' => '20 minutes',
        'cookies.path' => '/',
        'cookies.domain' => null,
        'cookies.secure' => false,
        'cookies.httponly' => false,
        // Encryption
        'crypt.mode' => 'ctr',
        // Session
        'session.handler' => null,
        'session.cookies' => array(),
        'session.flash_key' => 'app.flash',
        'session.encrypt' => false,
        // HTTP
        'http.version' => '1.1',
        // Routing
        'routes.case_sensitive' => true,
        'routes.context' => null,
        'routes.route_class' => '\Brainwave\Routing\Route',
        // View
        'view.items' => array(),
        // Json
        'json.option' => '0',
        //Callable Resolver
        'callable.resolver' => 'CallableResolver'
    );

    /**
     * Config folder path
     * @var string
     */
    protected $path;

    /**
     * Constructor
     * @param mixed $handler
     */
    public function __construct(ConfigurationHandlerInterface $handler, FileLoader $loader)
    {
        $this->setHandler($handler);
        $this->setLoader($loader);
    }

    /**
     * Set Brainwave's defaults using the handler
     * @param array $values
     */
    public function setArray(array $values)
    {
        $this->handler->setArray($values);
    }

    /**
     * Set Brainwave's defaults using the handler
     */
    public function setDefaults()
    {
        $this->handler->setArray($this->defaults);
    }

    /**
     * Get the default settings
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Set a configuration handler and provide it some defaults
     * @param \Brainwave\Config\Interfaces\ConfigurationHandlerInterface $handler
     */
    public function setHandler(ConfigurationHandlerInterface $handler)
    {
        $this->handler = $handler;
        $this->setDefaults();
    }

    /**
     * Get the configuration handler for access
     * @return \Brainwave\Config\Interfaces\ConfigurationHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set the configuration loader
     * @return \Brainwave\Config\FileLoader
     */
    public function setLoader(FileLoader $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * Get the configuration loader
     * @return \Brainwave\Config\FileLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Load the given configuration group.
     * @param  string  $file
     * @param  string  $namespace
     * @param  string  $environment
     * @param  string  $group
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
     * @return array
     */
    public function cascadePackage($file, $package = null, $group = null, $env = null, $items = null)
    {
        return $this->getLoader()->cascadePackage($file, $package, $group, $env, $items);
    }

    /**
     * Get a value
     * @param  string $key
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
     * @param  string $key
     * @param  mixed $value
     */
    public function set($key, $value)
    {
        $this->handler[$key] = $value;
    }

    /**
     * Set path to config folder
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
        $this->getLoader()->setDefaultPath($path);
        return $this;
    }

    /**
     * Get config folder path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get a value
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->handler[$key];
    }

    /**
     * Set a value
     * @param  string $key
     * @param  mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->handler[$key] = $value;
    }

    /**
     * Check a value exists
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->handler[$key]);
    }

    /**
     * Remove a value
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
