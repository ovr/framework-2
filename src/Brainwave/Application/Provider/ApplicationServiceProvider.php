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
    protected $container;

    public function register(Container $container)
    {
        $this->container = $container;

        $this->registerAliasLoader();
        $this->registerEntvironment();
        $this->registerStaticalProxyResolver();
    }

    protected function registerEntvironment()
    {
        $this->container['environment'] = function () {
            return new EnvironmentDetector();
        };
    }

    protected function registerAliasLoader()
    {
        $this->container['alias'] = function () {
            return new AliasLoader();
        };
    }

    protected function registerStaticalProxyResolver()
    {
        $this->container['statical.resolver'] = function () {
            return new StaticalProxyResolver();
        };
    }
}
