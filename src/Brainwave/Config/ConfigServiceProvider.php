<?php
namespace Brainwave\Config;

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
use \Brainwave\Config\FileLoader;
use \Brainwave\Config\Configuration;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Config\ConfigurationHandler;

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
    public function register(Container $app)
    {
        $app['settings'] = function ($app) {
            $config = new Configuration(new ConfigurationHandler, new FileLoader);

            return $config;
        };
    }
}
