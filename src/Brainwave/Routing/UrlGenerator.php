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

use \Brainwave\Http\Request;

/**
 * Route
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class UrlGenerator
{
    /**
     * The route collection.
     *
     * @var \Brainwave\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The request instance.
     *
     * @var \Brainwave\Http\Request
     */
    protected $request;

    /**
     * The force URL root.
     *
     * @var string
     */
    protected $forcedRoot;

    /**
     * The forced schema for URLs.
     *
     * @var string
     */
    protected $forceSchema;

    /**
     * Characters that should not be URL encoded.
     *
     * @var array
     */
    protected $dontEncode = array(
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
    );

    /**
     * Create a new URL Generator instance.
     *
     * @param  \Brainwave\Routing\RouteCollection  $routes
     * @param  \Brainwave\Http\Request   $request
     * @return void
     */
    public function __construct(RouteCollection $routes, Request $request)
    {
        $this->routes = $routes;
        $this->setRequest($request);
    }

    /**
     * Set the current request instance.
     *
     * @param  \Brainwave\Http\Request  $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
