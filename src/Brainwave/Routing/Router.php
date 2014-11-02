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

use \Pimple\Container;
use \Brainwave\Http\Request;
use \Brainwave\Routing\Route;
use \Brainwave\Routing\Interfaces\RouterInterface;
use \Brainwave\Routing\Interfaces\RouteInterface;

/**
 * Router
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Router implements RouterInterface
{
    /**
     * Container instance
     *
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * The current (most recently dispatched) route
     *
     * @var Route
     */
    protected $currentRoute;

    /**
     * All route objects, numerically indexed
     *
     * @var array[\Brainwave\Routing\Interfaces\RouteInterface]
     */
    protected $routes;

    /**
     * Named route objects, indexed by route name
     *
     * @var array[\Brainwave\Routing\Interfaces\RouteInterface]
     */
    protected $namedRoutes;

    /**
     * Route objects that addRoute the request URI
     *
     * @var array[\Brainwave\Routing\Interfaces\RouteInterface]
     */
    protected $addRouteedRoutes;

    /**
     * Cached urls: store and reuse already generated urls
     *
     * @var array
     */
    protected $cachedUrls;

    /**
     * Route groups
     * @var array
     */
    protected $routeGroups;

    /**
     * All params of the addRouteed route
     *
     * @var array
     */
    protected $routeParams;

    /**
     * @var integer Counts the number of available routes.
     */
    private $routeCount = 0;

    /**
     * Constructor
     *
     * @api
     */
    public function __construct(Container $app)
    {
        $this->app = $app;

        $this->routes = [];
        $this->routeGroups = [];
        $this->routeParams = [];
    }

    /**
     * Get any addRouteed route params
     *
     * @return  string
     */
    public function getParam($key = false)
    {
        if ($key === false) {
            return $this->getParams();
        }

        if (array_key_exists($key, $this->routeParams)) {
            return $this->routeParams["$key"];
        } else {
            return null;
        }
    }

    /**
     * Get any route params
     *
     * @return  array
     */
    public function getParams()
    {
        return $this->routeParams;
    }

    /**
     * Get current route
     *
     * This method will return the current \Brainwave\Routing\Route object. If a route
     * has not been dispatched, but route addRouteing has been completed, the
     * first addRouteing \Brainwave\Routing\Route object will be returned. If route addRouteing
     * has not completed, null will be returned.
     *
     * @return RouteInterface|null
     * @api
     */
    public function getCurrentRoute()
    {
        if ($this->currentRoute !== null) {
            return $this->currentRoute;
        }

        if (is_array($this->addRouteedRoutes) && count($this->addRouteedRoutes) > 0) {
            return $this->addRouteedRoutes[0];
        }

        return null;
    }

    /**
     * Get route objects that addRoute a given HTTP method and URI
     *
     * This method is responsible for finding and returning all \Brainwave\Interfaces\RouteInterface
     * objects that addRoute a given HTTP method and URI. Brainwave uses this method to
     * determine which \Brainwave\Interfaces\RouteInterface objects are candidates to be
     * dispatched for the current HTTP request.
     *
     * @param  string             $httpMethod  The HTTP request method
     * @param  string             $resourceUri The resource URI
     * @return array[\Brainwave\Interfaces\RouteInterface]
     * @api
     */
    public function getaddRouteedRoutes($httpMethod, $resourceUri, $save = true)
    {
        $addRouteedRoutes = [];

        foreach ($this->routes as $route) {
            if (!$route->supportsHttpMethod($httpMethod) && !$route->supportsHttpMethod("ANY")) {
                continue;
            }

            if ($route->addRoutees($resourceUri)) {
                $addRouteedRoutes[] = $route;
            }
        }

        if ($save === true) {
            $this->addRouteedRoutes = $addRouteedRoutes;
            $this->routeParams = array_merge($this->routeParams, $route->getParams());
        }

        return $addRouteedRoutes;
    }

    /**
     * Return all route objects that addRoute the given URI
     *
     * @param  string                          $pattern The pattern to addRoute against
     * @return array[\Brainwave\Routing\Route]
     */
    public function getAllRoutes($pattern = null)
    {
        if (null === $pattern) {
            return $this->routes;
        } else {
            $routes = [];
            foreach ($this->routes as $route) {
                if ($route->getPattern() === $pattern) {
                    $routes[] = $route;
                }
            }
            return $routes;
        }
    }

    /**
     * Return array of methods avaliable for the current pattern
     *
     * @param  string   $pattern The pattern to addRoute against
     * @return string[]
     */
    public function getMethodsAvailable($pattern)
    {
        $methods = [];
        foreach ($this->getAllRoutes($pattern) as $route) {
            $methods = array_merge($route->getHttpMethods(), $methods);
        }
        // Force options method as available as it must return this method's return value or self
        $methods[] = "OPTIONS";
        return array_unique($methods);
    }

    /**
     * Add GET|POST|PUT|PATCH|DELETE route
     *
     * Adds a new route to the router with associated callable. This
     * route will only be invoked when the HTTP request's method addRoutees
     * this route's method.
     *
     * ARGUMENTS:
     *
     * First:       string  The URL pattern (REQUIRED)
     * In-Between:  mixed   Anything that returns TRUE for `is_callable` (OPTIONAL)
     * Last:        mixed   Anything that returns TRUE for `is_callable` (REQUIRED)
     *
     * The first argument is required and must always be the
     * route pattern (ie. '/books/:id').
     *
     * The last argument is required and must always be the callable object
     * to be invoked when the route addRoutees an HTTP request.
     *
     * You may also provide an unlimited number of in-between arguments;
     * each interior argument must be callable and will be invoked in the
     * order specified before the route's callable is invoked.
     *
     * USAGE:
     *
     * Route::get('/foo'[, middleware, middleware, ...], callable);
     *
     * @param  array
     * @return Route
     */
    protected function addRoute($args)
    {
        $pattern = array_shift($args);
        $callable = $this->app['resolver']->build(array_pop($args));

        $route = $this->app['routes.factory']->make($pattern, $callable);

        $this->routeCount++;
        $route->setName((string)$this->routeCount);

        $this->map($route);

        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        return $route;
    }

    /**
     * Add GET route
     *
     * @return Route
     * @api
     */
    public function get()
    {
        $args = func_get_args();
        return $this->addRoute($args)->via(Request::METHOD_GET, Request::METHOD_HEAD);
    }

    /**
     * Add POST route
     *
     * @return Route
     * @api
     */
    public function post()
    {
        $args = func_get_args();
        return $this->addRoute($args)->via(Request::METHOD_POST);
    }

    /**
     * Add PUT route
     *
     * @return Route
     * @api
     */
    public function put()
    {
        $args = func_get_args();
        return $this->addRoute($args)->via(Request::METHOD_PUT);
    }

    /**
     * Add PATCH route
     *
     * @return Route
     * @api
     */
    public function patch()
    {
        $args = func_get_args();
        return $this->addRoute($args)->via(Request::METHOD_PATCH);
    }

    /**
     * Add DELETE route
     *
     * @return Route
     * @api
     */
    public function delete()
    {
        $args = func_get_args();
        return $this->addRoute($args)->via(Request::METHOD_DELETE);
    }

    /**
     * Add OPTIONS route
     *
     * @return Route
     * @api
     */
    public function options()
    {
        $args = func_get_args();
        return $this->addRoute($args)->via(Request::METHOD_OPTIONS);
    }

    /**
     * Route Groups
     *
     * This method accepts a route pattern and a callback. All route
     * declarations in the callback will be prepended by the group(s)
     * that it is in.
     *
     * Accepts the same parameters as a standard route so:
     * (pattern, middleware1, middleware2, ..., $callback)
     *
     * @api
     */
    public function group()
    {
        $args = func_get_args();
        $pattern = array_shift($args);

        $callable = $this->app['resolver']->build(array_pop($args));
        $this->app['router']->pushGroup($pattern, $args);

        if (is_callable($callable)) {
            call_user_func($callable);
        }

        $this->app['router']->popGroup();
    }

    /**
     * Add route without HTTP method
     *
     * @return Route
     */
    public function match()
    {
        $args = func_get_args();
        return $this->addRoute($args);
    }

    /**
     * Add route for any HTTP method
     *
     * @return Route
     * @api
     */
    public function any()
    {
        $args = func_get_args();
        return $this->addRoute($args)->via("ANY");
    }

    /**
     * Add a route
     *
     * This method registers a RouteInterface object with the router.
     *
     * @param  RouteInterface $route The route object
     * @api
     */
    public function map(RouteInterface $route)
    {
        list($groupPattern, $groupMiddleware) = $this->processGroups();
        $route->setPattern($groupPattern . $route->getPattern());
        $this->routes[] = $route;

        foreach ($groupMiddleware as $middleware) {
            $route->setMiddleware($middleware);
        }
    }

    /**
     * Process route groups
     *
     * A helper method for processing the group's pattern and middleware.
     *
     * @return array An array with the elements: pattern, middlewareArr
     */
    protected function processGroups()
    {
        $pattern = "";
        $middleware = [];
        foreach ($this->routeGroups as $group) {
            $k = key($group);
            $pattern .= $k;
            if (is_array($group[$k])) {
                $middleware = array_merge($middleware, $group[$k]);
            }
        }
        return [$pattern, $middleware];
    }

    /**
     * Get URL for named route
     *
     * @param  string            $name   The name of the route
     * @param  array             $params Associative array of URL parameter names and replacement values.
     *                                   UnaddRouteed parameters will be used to build the query string.
     * @return string                    The URL for the given route populated with provided replacement values
     * @throws \RuntimeException         If named route not found
     * @api
     */
    public function urlFor($name, $params = [])
    {
        $cacheKey = md5($name . serialize($params));

        if (isset($this->cachedUrls[$cacheKey])) {
            return $this->cachedUrls[$cacheKey];
        }

        if (!$this->hasNamedRoute($name)) {
            throw new \RuntimeException('Named route not found for name: ' . $name);
        }

        $url = $this->getNamedRoute($name)->getPattern();

        foreach ($params as $key => $value) {
            $search = '#:' . preg_quote($key, '#') . '\?(?!\w)#';
            if (preg_addRoute($search, $url)) {
                $url = preg_replace($search, $value, $url);
                unset($params[$key]);
            }
        }

        //Remove remnants of unpopulated, trailing optional pattern segments, escaped special characters
        $url = preg_replace('#\(/?:[^)]+\)+|\(|\)|\\\\#', '', $url);

        // Leftovers are added as url query string
        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        $this->cachedUrls[$cacheKey] = $url;

        return $url;
    }

    /**
     * Add named route
     *
     * @param  string               $name   The route name
     * @param  RouteInterface       $route  The route object
     * @throws \RuntimeException    If a named route already exists with the same name
     * @api
     */
    public function addNamedRoute($name, RouteInterface $route)
    {
        if ($this->hasNamedRoute($name)) {
            throw new \RuntimeException('Named route already exists with name: ' . $name);
        }
        $this->namedRoutes[(string) $name] = $route;
    }

    /**
     * Has named route
     *
     * @param  string $name The route name
     * @return bool
     * @api
     */
    public function hasNamedRoute($name)
    {
        $this->getnamedRoutes();

        return isset($this->namedRoutes[(string) $name]);
    }

    /**
     * Get named route
     *
     * @param  string              $name
     * @return RouteInterface|null
     * @api
     */
    public function getNamedRoute($name)
    {
        $this->getnamedRoutes();
        if ($this->hasNamedRoute($name)) {
            return $this->namedRoutes[(string) $name];
        }

        return null;
    }

    /**
     * Get external iterator for named routes
     *
     * @return \ArrayIterator
     * @api
     */
    public function getNamedRoutes()
    {
        if (is_null($this->namedRoutes)) {
            $this->namedRoutes = [];
            foreach ($this->routes as $route) {
                if ($route->getName() !== null) {
                    $this->addNamedRoute($route->getName(), $route);
                }
            }
        }

        return new \ArrayIterator($this->namedRoutes);
    }
}
