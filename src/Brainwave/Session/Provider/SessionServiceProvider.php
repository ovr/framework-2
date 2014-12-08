<?php
namespace Brainwave\Session\Provider;

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

/**
 * SessionServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['session'] = function ($container) {

        };

        $this->registerCsrf($container);
        $this->registerFlash($container);
    }

    protected function registerFlash(Container $container)
    {
        $container['flash'] = function ($container) {

        };
    }

    public function registerCsrf(Container $container)
    {
        $container['csrf'] = function ($container) {

        };
    }
}
