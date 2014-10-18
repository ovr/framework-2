<?php
namespace Brainwave\Routing;

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

use \Brainwave\Workbench\Workbench;
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
     * @var \Brainwave\Workbench\Workbench $app
     */
    protected $app;

    /**
     * The URL generator instance.
     *
     * @var \Brainwave\Routing\UrlGenerator
     */
    protected $generator;

    /**
     * Create a new Redirector instance.
     *
     * @param  \Brainwave\Routing\UrlGenerator  $generator
     * @return void
     */
    public function __construct(UrlGenerator $generator, Workbench $app)
    {
        $this->generator = $generator;
        $this->app = $app;
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
        $this->app['response']->redirect($url, $status);
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
        return $this->app['request']->getScriptName() . $this['router']->urlFor($name, $params);
    }

    /**
     * Halt
     *
     * Stop the application and immediately send the response with a
     * specific status and body to the HTTP client. This may send any
     * type of response: info, success, redirect, client error, or server error.
     *
     * @param  int    $status  The HTTP response status
     * @api
     */
    public function halt($status, $message = '')
    {
        $this->setStatus($status);
        $this->write($message, true);
        $this->stop();
    }
}
