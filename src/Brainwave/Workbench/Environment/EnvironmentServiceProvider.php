<?php
namespace Brainwave\Workbench\Environment;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
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
use \Brainwave\Workbench\Environment\Environment;
use \Brainwave\Workbench\Environment\EnvironmentDetector;

/**
 * EnvironmentServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class EnvironmentServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['environment.detector'] = function () {
            return new EnvironmentDetector();
        };

        $app['environment'] = function ($app) {
            return new Environment($app);
        };
    }
}
