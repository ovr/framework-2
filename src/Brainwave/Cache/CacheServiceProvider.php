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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Brainwave\Cache\Manager as CacheManager;

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
    protected $container;

    public function register(Container $container)
    {
        $this->container = $container;

        $this->registerCacheFactory();
        $this->registerDefaultCache();
        $this->registerCaches();
    }

    protected function registerCacheFactory()
    {
        $this->container['cache.factory'] = function ($container) {
            $cacheFactory = new CacheManager($container, $container['settings']['cache::supported.drivers']);
            $cacheFactory->setPrefix($container['settings']['cache::prefix']);

            return $cacheFactory;
        };
    }

    protected function registerDefaultCache()
    {
        $this->container['cache'] = function ($container) {

            //The default driver
            $container['cache.factory']->setDefaultDriver($container['settings']['cache::driver']);

            return $container['cache.factory']->driver($container['cache.factory']->getDefaultDriver());
        };
    }

    protected function registerCaches()
    {
        if ($this->container['settings']['cache::caches'] !== null) {
            foreach ($this->container['settings']['cache::caches'] as $name => $class) {
                if ($this->container['cache.factory']->getDefaultDriver() === $name) {
                    // we use shortcuts here in case the default has been overridden
                    $config = $this->container['settings']['cache::driver'];
                } else {
                    $config = $this->container['settings']['cache::caches'][$name];
                }

                $this->container['caches'][$name] = function () use ($config) {
                    return $this->container['cache.factory']->driver($config['driver'], $config);
                };
            }
        }
    }
}
