<?php
namespace Brainwave\Filesystem;

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

use Brainwave\Filesystem\Adapters\ConnectionFactory as Factory;
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
    public function register(Container $container)
    {
        $container['files'] = function () {
            return new Filesystem();
        };

        $this->registerFlysystem($container);
        $this->registerFileLoader($container);
    }

    /**
     * Register the driver based filesystem.
     *
     * @return void
     */
    protected function registerFlysystem(Container $container)
    {
        $this->registerFactory($container);

        $this->registerManager($container);

        $container['filesystem.disk'] = function ($container) {
            return $container['filesystem']->disk($container['settings']['filesystems::default']);
        };

        $container['filesystem.cloud'] = function ($container) {
            return $container['filesystem']->disk($container['settings']['filesystems::cloud']);
        };
    }

    /**
     * Register the filesystem factory.
     *
     * @return void
     */
    protected function registerFactory(Container $container)
    {
        $container['filesystem.factory'] = function () {
            return new Factory();
        };
    }

    /**
     * Register the filesystem manager.
     *
     * @return void
     */
    protected function registerManager(Container $container)
    {
        $container['filesystem'] = function ($container) {
            return new FilesystemManager($container, $container['filesystem.factory']);
        };
    }

    protected function registerFileLoader(Container $container)
    {
        $container['file.loader'] = function ($container) {
            $container['path'] = '';

            return new FileLoader($container['files'], $container['path']);
        };
    }
}
