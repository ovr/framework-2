<?php
namespace Brainwave\Cache;

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
use \Brainwave\Cache\Repository;
use \Brainwave\Cache\Adapter\ApcCache;
use \Brainwave\Cache\Adapter\NullCache;
use \Brainwave\Cache\Adapter\FileCache;
use \Brainwave\Cache\Adapter\ArrayCache;
use \Brainwave\Cache\Adapter\RedisCache;
use \Brainwave\Cache\Adapter\XCacheCache;
use \Brainwave\Cache\Adapter\WinCacheCache;
use \Brainwave\Cache\Adapter\MemcacheCache;
use \Brainwave\Cache\Adapter\MemcachedCache;
use \Brainwave\Cache\Exception\CacheException;
use \Brainwave\Cache\Exception\InvalidArgumentException;
use \Brainwave\Contracts\Cache\Adapter as AdapterContract;
use \Brainwave\Contracts\Cache\Factory as FactoryContract;

/**
 * Manager
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Manager implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * All supported drivers
     *
     * @var array
     */
    protected $supportedDrivers;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Constructor.
     *
     * @param \Pimple\Container $container
     * @param array             $supportedDrivers
     *                          The list of available drivers,
     *                          key=driver name, value=driver class
     */
    public function __construct(Container $container, array $supportedDrivers = [])
    {
        $this->container= $container;
        $this->supportedDrivers = $supportedDrivers;
    }

    /**
     * Builder.
     *
     * @param  string $driver The cache driver to use
     *
     * @return AdapterContract
     */
    public function driver(AdapterContract $driver, array $options = [])
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (!$this->driverExists($driver)) {
            throw new CacheException(
                'The cache driver ['.$driver.'] is not supported by the bundle.'
            );
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver, $options);
        }

        $class = $this->drivers[$driver];

        if (!$class::isSupported()) {
            throw new CacheException(
                'The cache driver ['.$driver.'] is not supported by your running settingsuration.'
            );
        }

        return $class;
    }

    /**
     * Create a new driver instance.
     *
     * @param  string $driver
     * @return mixed
     *
     * @throws \Brainwave\Contracts\Cache\InvalidArgumentException
     */
    protected function createDriver($driver, array $options = [])
    {
        $method = 'create'.ucfirst($driver).'Driver';
        $options = array_filter($options);

        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $options);
        } elseif (method_exists($this, $method)) {
            return empty($options) ? $this->$method() : $this->$method($options);
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * Call a custom driver creator.
     *
     * @param  string $driver
     *
     * @return mixed
     */
    protected function callCustomCreator($driver, array $options = [])
    {
        return $this->customCreators[$driver]($this->app, $options);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string   $driver
     * @param  \Closure $callback
     *
     * @return $this
     */
    public function extend($driver, \Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all of the created "drivers".
     *
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Check if the given driver is supported
     *
     * @param  string $driver
     *
     * @return bool
     */
    public function driverExists($driver)
    {
        return isset($this->supportedDrivers[$driver]);
    }

    /**
     * Create an instance of the APC cache driver.
     *
     * @return Repository
     */
    protected function createApcDriver()
    {
        return $this->repository(new ApcCache($this->getPrefix()));
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @return Repository
     */
    protected function createArrayDriver()
    {
        return $this->repository(new ArrayCache);
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @param  array $config
     *
     * @return Repository
     */
    protected function createFileDriver(array $config = [])
    {
        $config = array_filter($config);

        $path = empty($config) ?
        $this->app['settings']['cache::path'] :
        $config['path'];

        return $this->repository(new FileCache($this->app['files'], $path));
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param  array $config
     *
     * @return Repository
     */
    protected function createMemcachedDriver(array $config = [])
    {
        $config = array_filter($config);

        $servers = empty($config) ?
        $this->app['settings']['cache::memcached'] :
        $config['memcached'];

        $memcached = MemcachedCache::connect($servers);

        return $this->repository(new MemcachedCache($memcached, $this->getPrefix()));
    }

    /**
     * Create an instance of the Memcache cache driver.
     *
     * @param  array $config
     *
     * @return Repository
     */
    protected function createMemcacheDriver(array $config = [])
    {
        $config = array_filter($config);

        $servers = empty($config) ?
        $this->app['settings']['cache::memcache'] :
        $config['memcache'];

        $memcache = MemcacheCache::connect($servers);

        return $this->repository(new MemcacheCache($memcache, $this->getPrefix()));
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param  array $config
     *
     * @return Repository
     */
    protected function createRedisDriver(array $config = [])
    {
        $config = array_filter($config);

        $servers = empty($config) ?
        (
            (!is_null($this->app['settings']['cache::redis.parameters'])) ?
            $this->app['settings']['cache::redis.parameters'] :
            ''
        ) :
        $config['parameters'];

        $options = empty($config) ?
        (
            (!is_null($this->app['settings']['cache::redis.options'])) ?
            $this->app['settings']['cache::redis.options'] :
            []
        ) :
        $config['options'];

        $memcached = RedisCache::connect($servers, $options);

        return $this->repository(new RedisCache($memcached, $this->getPrefix()));
    }

    /**
     * Create an instance of the Null cache driver.
     *
     * @return Repository
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullCache);
    }

    /**
     * Create an instance of the WinCache cache driver.
     *
     * @return Repository
     */
    protected function createWincacheDriver()
    {
        return $this->repository(new WinCacheCache($this->getPrefix()));
    }

    /**
     * Create an instance of the XCache cache driver.
     *
     * @return Repository
     */
    protected function createXcacheDriver()
    {
        return $this->repository(new XCacheCache($this->getPrefix()));
    }

    /**
     * Get the cache "prefix" value.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->app['settings']['cache::prefix'];
    }

    /**
     * Set the cache "prefix" value.
     *
     * @param  string $name
     *
     * @return void
     */
    public function setPrefix($name)
    {
        $this->app['settings']['cache::prefix'] = $name;
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  AdapterContract $Cache
     *
     * @return \Brainwave\Cache\Repository
     */
    protected function repository(AdapterContract $Cache)
    {
        return new Repository($Cache);
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['settings']['cache::driver'];
    }

    /**
     * Set the default cache driver name.
     *
     * @param  string $name
     *
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['settings']['cache::driver'] = $name;
    }
}
