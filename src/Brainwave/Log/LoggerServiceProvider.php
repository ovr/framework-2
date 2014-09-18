<?php
namespace Brainwave\Log;

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

use \Monolog\Logger;
use \Pimple\Container;
use \Brainwave\Log\MonologWriter;
use \Pimple\ServiceProviderInterface;

/**
 * LoggerServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(Container $app)
    {
        $app['logger'] = function ($app) {
            return new MonologWriter(
                new Logger($app['env'])
            );
        };
    }
}
