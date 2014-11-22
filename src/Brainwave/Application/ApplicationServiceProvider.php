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
    public function register(Container $app)
    {
        $this->registerAliasLoader($app);
        $this->registerEntvironment($app);
        $this->registerStaticalProxyResolver($app);

    }

    protected function registerEntvironment($app)
    {
        $app['environment'] = function ($app) {
            return new EnvironmentDetector($app, $_SERVER);
        };
    }

    protected function registerAliasLoader($app)
    {
        $app['alias'] = function () {
            return new AliasLoader();
        };
    }

    protected function registerStaticalProxyResolver($app)
    {
        $app['statical.resolver'] = function () {
            return new StaticalProxyResolver();
        };
    }
}
