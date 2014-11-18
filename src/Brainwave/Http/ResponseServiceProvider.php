<?php
namespace Brainwave\Http;

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
use \Brainwave\Http\Headers;
use \Brainwave\Http\Response;
use \Brainwave\Cookie\CookieJar;
use \Pimple\ServiceProviderInterface;

/**
 * ResponseServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class ResponseServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(Container $app)
    {
        $app['response'] = function ($app) {
            $headers = new Headers();
            $cookieJar = new CookieJar();
            $response = new Response($headers, $cookieJar);
            $response->setProtocolVersion('HTTP/' . $app['settings']->get('http::version', '1.1'));

            return $response;
        };
    }
}
