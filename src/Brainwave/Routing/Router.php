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
     * The current (most recently dispatched) route
     * @var Route
     */
    protected $currentRoute;

    /**
     * All route objects, numerically indexed
     * @var array[\Brainwave\Routing\Interfaces\RouteInterface]
     */
    protected $routing;

    /**
     * Named route objects, indexed by route name
     * @var array[\Brainwave\Routing\Interfaces\RouteInterface]
     */
    protected $namedRouting;

    /**
     * Route objects that match the request URI
     * @var array[\Brainwave\Routing\Interfaces\RouteInterface]
     */
    protected $matchedRouting;

    /**
     * @var string Current Base URI
     */
    protected $currentBaseUri = '';

    /**
     * @var array Current stack of middleware
     */
    protected $currentMiddleware;

    /**
     * Cached urls: store and reuse already generated urls
     * @var array
     */
    protected $cachedUrls;

    /**
     * Constructor
     * @api
     */
    public function __construct()
    {
        $this->routing = [];
        $this->currentMiddleware = [];
    }

    /**
     * Get current route
     *
     * This method will return the current \Brainwave\Routing\Route object. If a route
     * has not been dispatched, but route matching has been completed, the
     * first matching \Brainwave\Routing\Route object will be returned. If route matching
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

        if (is_array($this->matchedRouting) && count($this->matchedRouting) > 0) {
            return $this->matchedRouting[0];
        }

        return null;
    }

    /**
     * Get route objects that match a given HTTP method and URI
     *
     * This method is responsible for finding and returning all \Brainwave\Interfaces\RouteInterface
     * objects that match a given HTTP method and URI. Brainwave uses this method to
     * determine which \Brainwave\Interfaces\RouteInterface objects are candidates to be
     * dispatched for the current HTTP request.
     *
     * @param  string             $httpMethod  The HTTP request method
     * @param  string             $resourceUri The resource URI
     * @param  bool               $reload      Should matching routes be re-parsed?
     * @return array[\Brainwave\Interfaces\RouteInterface]
     * @api
     */
    public function getMatchedRoutes($httpMethod, $resourceUri, $save = true)
    {
        $matchedRoutes = [];

        foreach ($this->routing as $route) {
            if (!$route->supportsHttpMethod($httpMethod) && !$route->supportsHttpMethod("ANY")) {
                continue;
            }

            if ($route->matches($resourceUri)) {
                $matchedRoutes[] = $route;
            }
        }

        if ($save === true) {
            $this->matchedRoutes = $matchedRoutes;
        }

        return $matchedRoutes;
    }

    /**
     * Return all route objects that match the given URI
     * @param  string               $pattern      The pattern to match against
     * @return array[\Brainwave\Routing\Route]
     */
    public function getAllRoutes($pattern = null)
    {
        if (null === $pattern) {
            return $this->routing;
        } else {
            $routes = [];
            foreach ($this->routing as $route) {
                if ($route->getPattern() == $pattern) {
                    $routes[] = $route;
                }
            }
            return $routes;
        }
    }

    /**
     * Return array of methods avaliable for the current pattern
     * @param  string               $pattern      The pattern to match against
     * @return array
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
     * Add a route
     *
     * This method registers a RouteInterface object with the router.
     *
     * @param  RouteInterface $route The route object
     * @api
     */
    public function map(RouteInterface $route)
    {
        $route->setPattern($this->currentBaseUri . $route->getPattern());
        $this->routing[] = $route;

        foreach ($this->currentMiddleware as $middleware) {
            $route->setMiddleware($middleware);
        }
    }

    /**
     * Get URL for named route
     * @param  string            $name   The name of the route
     * @param  array             $params Associative array of URL parameter names and replacement values.
     *                                   Unmatched parameters will be used to build the query string.
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
            if (preg_match($search, $url)) {
                $url = preg_replace($search, $value, $url);
                unset($params[$key]);
            }
        }

        //Remove remnants of unpopulated, trailing optional pattern segments, escaped special characters
        $url = preg_replace('#\(/?:.\)|\(|\)|\\\\#', '', $url);

        // Leftovers are added as url query string
        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        $this->cachedUrls[$cacheKey] = $url;

        return $url;
    }

    /**
     * Add named route
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
        $this->namedRouting[(string) $name] = $route;
    }

    /**
     * Has named route
     * @param  string $name The route name
     * @return bool
     * @api
     */
    public function hasNamedRoute($name)
    {
        $this->getNamedRouting();

        return isset($this->namedRouting[(string) $name]);
    }

    /**
     * Get named route
     * @param  string                               $name
     * @return RouteInterface|null
     * @api
     */
    public function getNamedRoute($name)
    {
        $this->getNamedRouting();
        if ($this->hasNamedRoute($name)) {
            return $this->namedRouting[(string) $name];
        }

        return null;
    }

    /**
     * Get external iterator for named Routing
     * @return \ArrayIterator
     * @api
     */
    public function getNamedRoutes()
    {
        if (is_null($this->namedRouting)) {
            $this->namedRouting = [];
            foreach ($this->routing as $route) {
                if ($route->getName() !== null) {
                    $this->addNamedRoute($route->getName(), $route);
                }
            }
        }

        return new \ArrayIterator($this->namedRouting);
    }
}
