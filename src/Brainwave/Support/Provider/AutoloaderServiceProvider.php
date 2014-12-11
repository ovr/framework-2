<?php
namespace Brainwave\Support\Provider;

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

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * AutoloaderServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class AutoloaderServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Classloader
     *
     * @return \Brainwave\Support\Autoloader\Autoloader
     */
    public function register(Container $container)
    {
        $container['autoloader'] = function () {
            return new Autoloader();
        };
    }
}
