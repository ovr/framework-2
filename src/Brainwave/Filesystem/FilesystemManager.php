<?php
namespace Brainwave\Filesystem;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Pimple\Container;
use \League\Flysystem\Filesystem as Flysystem;
use \League\Flysystem\AdapterInterface as Adapter;
use \Brainwave\Filesystem\Adapters\ConnectionFactory as Factory;
use \Brainwave\Filesystem\Interfaces\FilesystemManagerInterface;

/**
 * FilesystemManager
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class FilesystemManager implements FilesystemManagerInterface {

    /**
     * The application instance.
     *
     * @var \Brainwave\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The factory instance.
     *
     * @var \Brainwave\Filesystem\Adapters\ConnectionFactory
     */
    protected $factory;

    /**
     * The array of resolved filesystem drivers.
     *
     * @var array
     */
    protected $disks = [];

    /**
     * Create a new filesystem manager instance.
     *
     * @param  \Pimple\Container  $app
     * @param  \Brainwave\Filesystem\Adapters\ConnectionFactory  $factory
     * @return void
     */
    public function __construct(Container $app, Factory $factory)
    {
        $this->app = $app;
        $this->factory = $factory;
    }

    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $name
     * @return \Brainwave\Filesystem\Interfaces\FilesystemInterface
     */
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->get($name);
    }

    /**
     * Attempt to get the disk from the local cache.
     *
     * @param  string  $name
     * @return \Brainwave\Filesystem\Interfaces\FilesystemInterface
     */
    protected function get($name)
    {
        return isset($this->disks[$name]) ? $this->disks[$name] : $this->resolve($name);
    }

    /**
     * Resolve the given disk.
     *
     * @param  string  $name
     * @return \Brainwave\Filesystem\Interfaces\FilesystemInterface
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        return $this->adapt($this->factory->make($config));
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param  \League\Flysystem\AdapterInterface  $adapter
     * @return \Brainwave\Filesystem\Interfaces\FilesystemInterface
     */
    protected function adapt(Adapter $adapter)
    {
        return new FilesystemAdapter(new Flysystem($adapter));
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['settings']["filesystems::disks.{$name}"];
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['settings']['filesystems::default'];
    }
}
