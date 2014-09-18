<?php
namespace Brainwave\Cache;

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
    public function register(Container $app)
    {
        $app['cache.default_options'] = array(
            'driver' => 'array',
        );

        //All supported drivers
        $app['cache.drivers'] = function () {
            return array(
                'apc'       => '\\Brainwave\\Cache\\Driver\\ApcCache',
                'array'     => '\\Brainwave\\Cache\\Driver\\ArrayCache',
                'file'      => '\\Brainwave\\Cache\\Driver\\FileCache',
                'memcache'  => '\\Brainwave\\Cache\\Driver\\MemcacheCache',
                'memcached' => '\\Brainwave\\Cache\\Driver\\MemcachedCache',
                'xcache'    => '\\Brainwave\\Cache\\Driver\\XcacheCache',
                'redis'     => '\\Brainwave\\Cache\\Driver\\RedisCache',
                'wincache'  => '\\Brainwave\\Cache\\Driver\\WincacheCache',
            );
        };

        $app['cache.factory'] = function ($app) {
            return new CacheManager($app['cache.drivers'], array());
        };

        $app['caches.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['caches.options'])) {
                $app['caches.options'] = array(
                    'default' => isset($app['cache.options']) ? $app['cache.options'] : array()
                );
            }

            $tmp = $app['caches.options'];
            foreach ($tmp as $name => &$options) {
                $options = array_replace($app['cache.default_options'], $options);

                if (!isset($app['caches.default'])) {
                    $app['caches.default'] = $name;
                }
            }
            $app['caches.options'] = $tmp;
        });

        $app['caches'] = function ($app) {
            $app['caches.options.initializer']();

            $caches = new Container();
            foreach ($app['caches.options'] as $name => $options) {
                if ($app['caches.default'] === $name) {
                    // we use shortcuts here in case the default has been overridden
                    $config = $app['cache.config'];
                } else {
                    $config = $app['caches.config'][$name];
                }

                $caches[$name] = function ($caches) use ($app, $config) {
                    return $app['cache.factory']->getCache($config['driver'], $config);
                };
            }

            return $caches;
        };

        $app['caches.config'] = function ($app) {
            $app['caches.options.initializer']();

            $configs = new Container();
            foreach ($app['caches.options'] as $name => $options) {
                $configs[$name] = $options;
            }

            return $configs;
        };

        // shortcuts for the "first" cache
        $app['cache'] = function ($app) {
            $caches = $app['caches'];

            return $caches[$app['caches.default']];
        };

        $app['cache.config'] = function ($app) {
            $caches = $app['caches.config'];

            return $caches[$app['caches.default']];
        };
    }
}
