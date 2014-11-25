<?php
namespace Brainwave\Routing;

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
use \Brainwave\Routing\UrlGenerator;

/**
 * Route
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class Redirector
{
    /**
     * Application instance
     *
     * @var Container $container
     */
    protected $container;

    /**
     * The URL generator instance.
     *
     * @var \Brainwave\Routing\UrlGenerator
     */
    protected $generator;

    /**
     * Create a new Redirector instance.
     *
     * @param  UrlGenerator $generator
     * @param  Container    $container
     * @return void
     */
    public function __construct(UrlGenerator $generator, Container $container)
    {
        $this->generator = $generator;
        $this->container= $container;
    }

    /**
     * Redirect
     *
     * This method immediately redirects to a new URL. By default,
     * this issues a 302 Found response; this is considered the default
     * generic redirect response. You may also specify another valid
     * 3xx status code if you want. This method will automatically set the
     * HTTP Location header for you using the URL parameter.
     *
     * @param  string $url    The destination URL
     * @param  int    $status The HTTP redirect status code (optional)
     * @api
     */
    public function redirect($url, $status = 302)
    {
        $this->container['response']->redirect($url, $status);
        $this->halt($status);
    }

    /**
     * RedirectTo
     *
     * Redirects to a specific named route
     *
     * @param array     $params     Associative array of URL parameters and replacement values
     */
    public function redirectTo($route, $params = [], $status = 302)
    {
        $this->redirect($this->urlFor($route, $params), $status);
    }

    /**
     * Get the URL for a named route
     * @param  string            $name   The route name
     * @param  array             $params Associative array of URL parameters and replacement values
     * @throws \RuntimeException         If named route does not exist
     * @return string
     * @api
     */
    public function urlFor($name, $params = [])
    {
        return $this->container['request']->getScriptName().$this['router']->urlFor($name, $params);
    }

    /**
     * TODO
     *
     * @param  [type]  $path     [description]
     * @param  boolean $absolute [description]
     * @return [type]            [description]
     */
    public function url($path = null, $absolute = false)
    {
        $url = pathinfo($_SERVER["PHP_SELF"], PATHINFO_DIRNAME) . "/" . $path;
        $url = preg_replace("/\/\/+/", "/", $url);
        if ($absolute) {
            $url = "http://" . $_SERVER["SERVER_NAME"] . $url;
        }
        return $url;
    }

    /**
     * Halt
     *
     * Stop the application and immediately send the response with a
     * specific status and body to the HTTP client. This may send any
     * type of response: info, success, redirect, client error, or server error.
     *
     * @param int $status  The HTTP response status
     * @api
     */
    public function halt($status, $message = '')
    {
        $this->container->halt($status, $message = '');
    }
}
