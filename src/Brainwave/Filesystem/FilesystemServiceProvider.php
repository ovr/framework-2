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
use \Pimple\ServiceProviderInterface;
use \Brainwave\Filesystem\Filesystem;
use \Brainwave\Filesystem\FilesystemManager;
use \Brainwave\Filesystem\Adapters\ConnectionFactory as Factory;

/**
 * FilesystemServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class FilesystemServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['files'] = function ($app) {
            return new Filesystem();
        };

        $this->registerFlysystem();
    }

    /**
     * Register the driver based filesystem.
     *
     * @return void
     */
    protected function registerFlysystem()
    {
        $this->registerFactory();

        $this->registerManager();

        $this->app['filesystem.disk'] = function () {
            return $this->app['filesystem']->disk($this->getDefaultDriver());
        };

        $this->app['filesystem.cloud'] = function () {
            return $this->app['filesystem']->disk($this->getCloudDriver());
        };
    }

    /**
     * Register the filesystem factory.
     *
     * @return void
     */
    protected function registerFactory()
    {
        $this->app['filesystem.factory'] = function () {
            return new Factory();
        };
    }

    /**
     * Register the filesystem manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app['filesystem'] = function () {
            return new FilesystemManager($this->app, $app['filesystem.factory']);
        };
    }

    /**
     * Get the default file driver.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->app['settings']['filesystems::default'];
    }

    /**
     * Get the default cloud based file driver.
     *
     * @return string
     */
    protected function getCloudDriver()
    {
        return $this->app['settings']['filesystems::cloud'];
    }
}
