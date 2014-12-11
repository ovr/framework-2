<?php
namespace Brainwave\Filesystem\Provider;

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

use Brainwave\Filesystem\Adapters\ConnectionFactory as Factory;
use Brainwave\Filesystem\FileLoader;
use Brainwave\Filesystem\Filesystem;
use Brainwave\Filesystem\FilesystemManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
    protected $container;

    public function register(Container $container)
    {
        $this->container = $container;

        $this->container['files'] = function () {
            return new Filesystem();
        };

        $this->registerFlysystem();
        $this->registerFileLoader();
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

        $this->container['filesystem.disk'] = function ($container) {
            return $container['filesystem']->disk($container['settings']['filesystems::default']);
        };

        $this->container['filesystem.cloud'] = function ($container) {
            return $container['filesystem']->disk($container['settings']['filesystems::cloud']);
        };
    }

    /**
     * Register the filesystem factory.
     *
     * @return void
     */
    protected function registerFactory()
    {
        $this->container['filesystem.factory'] = function () {
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
        $this->container['filesystem'] = function ($container) {
            return new FilesystemManager($container, $container['filesystem.factory']);
        };
    }

    protected function registerFileLoader()
    {
        $this->container['file.loader'] = function ($container) {
            $container['path'] = '';

            return new FileLoader($container['files'], $container['path']);
        };
    }
}
