<?php
namespace Brainwave\Application\Provider;

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

use Brainwave\Application\AliasLoader;
use Brainwave\Application\EnvironmentDetector;
use Brainwave\Application\StaticalProxyResolver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * ApplicationServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class ApplicationServiceProvider implements ServiceProviderInterface
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
    protected function registerEntvironment(Container $container)
    {
        $container['environment'] = function () {
            return new EnvironmentDetector();
        };
    }

    /**
     * @param Container $container
     */
    protected function registerAliasLoader(Container $container)
    {
        $container['alias'] = function () {
            return new AliasLoader();
        };
    }

    /**
     * @param Container $container
     */
    protected function registerStaticalProxyResolver(Container $container)
    {
        $container['statical.resolver'] = function () {
            return new StaticalProxyResolver();
        };
    }
}
