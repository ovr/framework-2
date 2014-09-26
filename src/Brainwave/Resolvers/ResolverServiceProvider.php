<?php
namespace Brainwave\Routing;

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
use \Pimple\ServiceProviderInterface;
use \Brainwave\Resolvers\CallableResolver;
use \Brainwave\Resolvers\ContainerResolver;
use \Brainwave\Resolvers\DependencyResolver;

/**
 * ResolverServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class ResolverServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        // Route Callable Resolver
        $this['resolver'] = function ($app) {

            $resolverCofig = $app['settings']->get('callable.resolver', 'CallableResolver');

            switch ($resolverCofig) {
                case 'DependencyResolver':
                    return new DependencyResolver($app);
                    break;

                case 'ContainerResolver':
                    return new CallableResolver();
                    break;

                case 'CallableResolver':
                    return new CallableResolver();
                    break;

                default:
                    throw new \Exception("Set a Callable Resolver");
                    break;
            }
        };
    }
}
