<?php
namespace Brainwave\Http;

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
use \Brainwave\Http\Request;
use \Brainwave\Http\Headers;
use \Brainwave\Cookie\CookieJar;
use \Pimple\ServiceProviderInterface;

/**
 * RequestServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class RequestServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(Container $container)
    {
        $container['request'] = function ($container) {
            $environment = $container['environment'];
            $headers = new Headers($environment);
            $cookieJar = new CookieJar($headers);

            if ($container['settings']->get('cookies::encrypt', false) ===  true) {
                $cookieJar->decrypt($container['encrypter']);
            }

            return new Request($environment, $headers, $cookieJar);
        };
    }
}
