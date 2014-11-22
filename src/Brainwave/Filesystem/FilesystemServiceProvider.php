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

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Filesystem\FileLoader;
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

        $this->registerFlysystem($app);
        $this->registerFileLoader($app);
    }

    /**
     * Register the driver based filesystem.
     *
     * @return void
     */
    protected function registerFlysystem(Container $app)
    {
        $this->registerFactory($app);

        $this->registerManager($app);

        $app['filesystem.disk'] = function () {
            return $app['filesystem']->disk($app['settings']['filesystems::default']);
        };

        $app['filesystem.cloud'] = function () {
            return $app['filesystem']->disk($app['settings']['filesystems::cloud']);
        };
    }

    /**
     * Register the filesystem factory.
     *
     * @return void
     */
    protected function registerFactory(Container $app)
    {
        $app['filesystem.factory'] = function () {
            return new Factory();
        };
    }

    /**
     * Register the filesystem manager.
     *
     * @return void
     */
    protected function registerManager(Container $app)
    {
        $app['filesystem'] = function ($app) {
            return new FilesystemManager($app, $app['filesystem.factory']);
        };
    }

    protected function registerFileLoader(Container $app)
    {
        $app['file.loader'] = function ($app) {
            $app['path'] = '';

            return new FileLoader($app['files'], $app['path']);
        };
    }
}
