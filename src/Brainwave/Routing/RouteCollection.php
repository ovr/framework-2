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

use Brainwave\Contracts\Routing\RouteStrategy as RouteStrategyContract;
use FastRoute\DataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use Pimple\Container;

/**
 * RouteCollection
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class RouteCollection extends RouteCollector implements RouteStrategyContract
{
    /**
     * Route strategy functionality
     */
    use RouteStrategyTrait;

    /**
     * @var \Pimple\Conatiner
     */
    protected $container;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * Constructor
     *
     * @param \Pimple\Container        $container
     * @param \FastRoute\RouteParser   $parser
     * @param \FastRoute\DataGenerator $generator
     */
    public function __construct(
        Container $container,
        RouteParser $parser,
        DataGenerator $generator
    ) {
        $this->container = $container;

        parent::__construct($parser, $generator);
    }

    /**
     * Add a route to the collection
     *
     * @param string          $method
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function addRoute($method, $route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        // are we running a single strategy for the collection?
        $strategy = (isset($this->strategy)) ? $this->strategy : $strategy;

        // if the handler is an anonymous function, we need to store it for later use
        // by the dispatcher, otherwise we just throw the handler string at FastRoute
        if ($handler instanceof \Closure) {
            $callback = $handler;
            $handler  = uniqid('brainwave::route::', true);

            $this->routes[$handler]['callback'] = $callback;
        }

        $this->routes[$handler]['strategy'] = $strategy;

        $route = $this->parseRouteString($route);

        parent::addRoute($method, $route, $handler);

        return $this;
    }

    /**
     * Builds a dispatcher based on the routes attached to this collection
     *
     * @return \Brainwave\Routing\Dispatcher
     */
    public function getDispatcher()
    {
        $dispatcher = new Dispatcher($this->container, $this->routes, $this->getData());

        if (! is_null($this->strategy)) {
            $dispatcher->setStrategy($this->strategy);
        }

        return $dispatcher;
    }

    /**
     * Add a route that responds to GET HTTP method
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function get($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('GET', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to POST HTTP method
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function post($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('POST', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to PUT HTTP method
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function put($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('PUT', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to PATCH HTTP method
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function patch($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('PATCH', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to DELETE HTTP method
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function delete($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('DELETE', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to HEAD HTTP method
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function head($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('HEAD', $route, $handler, $strategy);
    }

    /**
     * Add a route that responds to OPTIONS HTTP method
     *
     * @param string          $route
     * @param string|\Closure $handler
     * @param integer         $strategy
     *
     * @return \Brainwave\Routing\RouteCollection
     */
    public function options($route, $handler, $strategy = self::REQUEST_RESPONSE_STRATEGY)
    {
        return $this->addRoute('OPTIONS', $route, $handler, $strategy);
    }

    /**
     * Convenience method to convert pre-defined key words in to regex strings
     *
     * @param  string $route
     * @return string
     */
    protected function parseRouteString($route)
    {
        $wildcards = [
            '/{(.+?):number}/'        => '{$1:[0-9]+}',
            '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
            '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        ];

        return preg_replace(array_keys($wildcards), array_values($wildcards), $route);
    }
}
