<?php
namespace Brainwave\Config\Provider;

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

use Brainwave\Config\Manager as ConfigManager;
use Brainwave\Config\Repository;
use Brainwave\Filesystem\FileLoader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * ConfigServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.5-dev
 *
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['settings.path'] = '';

        $container['settings'] = function ($container) {
            $config = new ConfigManager(
                new Repository(),
                new FileLoader(
                    $container['files'],
                    $container['settings.path']
                )
            );

            return $config;
        };
    }
}