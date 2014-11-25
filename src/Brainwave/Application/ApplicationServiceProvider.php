<?php
namespace Brainwave\Application;

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
use \Brainwave\Application\AliasLoader;
use \Brainwave\Application\EnvironmentDetector;
use \Brainwave\Application\StaticalProxyResolver;

/**
 * EnvironmentServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class EnvironmentServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $this->registerAliasLoader($container);
        $this->registerEntvironment($container);
        $this->registerStaticalProxyResolver($container);

    }

    /**
     * @param Container $container
     */
    protected function registerEntvironment($container)
    {
        $container['environment'] = function ($container) {
            return new EnvironmentDetector($container, $_SERVER);
        };
    }

    /**
     * @param Container $container
     */
    protected function registerAliasLoader($container)
    {
        $container['alias'] = function () {
            return new AliasLoader();
        };
    }

    /**
     * @param Container $container
     */
    protected function registerStaticalProxyResolver($container)
    {
        $container['statical.resolver'] = function () {
            return new StaticalProxyResolver();
        };
    }
}
