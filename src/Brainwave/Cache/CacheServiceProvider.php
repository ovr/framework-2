<?php
namespace Brainwave\Cache;

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
use \Brainwave\Cache\CacheManager;
use \Pimple\ServiceProviderInterface;

/**
 * CacheServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function register(Container $app)
    {
        $this->app = $app;

        $this->registerCacheFactory();
        $this->registerDefaultCache();
        $this->registerCaches();
    }

    protected function registerCacheFactory()
    {
        $this->app['cache.factory'] = function ($app) {
            $cacheFactory = new CacheManager($app, $app['settings']['cache::supported.drivers']);
            $cacheFactory->setPrefix($app['settings']['cache::prefix']);
            return $cacheFactory;
        };
    }

    protected function registerDefaultCache()
    {
        $this->app['cache'] = function ($app) {

            //The default driver
            $app['cache.factory']->setDefaultDriver($app['settings']['cache::driver']);

            return $app['cache.factory']->driver($app['cache.factory']->getDefaultDriver());
        };
    }

    protected function registerCaches()
    {
        if ($this->app['settings']['cache::caches'] !== null) {
            foreach ($this->app['settings']['cache::caches'] as $name => $class) {
                if ($this->app['cache.factory']->getDefaultDriver() === $name) {
                    // we use shortcuts here in case the default has been overridden
                    $config = $this->app['settings']['cache::driver'];
                } else {
                    $config = $this->app['settings']['cache::caches'][$name];
                }

                $this->app['caches'][$name] = function () use ($config) {
                    return $this->app['cache.factory']->driver($config['driver'], $config);
                };
            }
        }
    }
}
