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

use \Brainwave\Routing\Interfaces\RouteInterface;

/**
 * Route
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Route implements RouteInterface
{
    /**
     * The route pattern (e.g. "/hello/:first/:name")
     *
     * @var string
     */
    protected $pattern;

    /**
     * The route callable
     *
     * @var mixed
     */
    protected $callable;

    /**
     * The Route context
     *
     * @var mixed
     */
    protected $context;

    /**
     * Conditions for this route's URL parameters
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * Default conditions applied to all route instances
     *
     * @var array
     */
    protected static $defaultConditions = [];

    /**
     * The name of this route (optional)
     *
     * @var string
     */
    protected $name;

    /**
     * Array of URL parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     * Array of URL parameter names
     *
     * @var array
     */
    protected $paramNames = [];

    /**
     * Array of URL parameter names with at the end
     *
     * @var array
     */
    protected $paramNamesPath = [];

    /**
     * HTTP methods supported by this route
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Middleware to be invoked before immediately before this route is dispatched
     *
     * @var array[Callable]
     */
    protected $middleware = [];

    /**
     * @var bool Whether or not this route should be matched in a case-sensitive manner
     */
    protected $caseSensitive;

    /**
     * Controller constructor dependencies
     *
     * @var array
     */
    protected $controllerDependencies = [];

    /**
     * Array of Controller Constructor Parameters
     *
     * @var array
     */
    protected $constructorParams = [];

    /**
     * Determine is the route pattern should be escaped or not
     *
     * @var bool
     */
    protected $escapePattern;

    /**
     * Constructor
     *
     * @param string $pattern       The URL pattern
     * @param mixed  $callable      Anything that returns `true` for `is_callable()`
     * @param bool   $caseSensitive Whether or not this route should be matched in a case-sensitive manner
     * @param bool   $escapePattern If false, the route pattern is considered as a RegExp pattern,
     *
     * @api
     */
    public function __construct(
        $pattern = null,
        callable $callable = null,
        $caseSensitive = true,
        $escapePattern = false
    ) {
        if (!empty($pattern)) {
            $this->setPattern($pattern);
        }
        if (!empty($callable)) {
            $this->setCallable($callable);
        }

        $this->setEscapePattern($escapePattern);
        $this->setConditions(self::getDefaultConditions());
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Set default route conditions for all Routing
     *
     * @param  array $defaultConditions
     * @api
     */
    public static function setDefaultConditions(array $conditions = [])
    {
        self::$defaultConditions = $conditions;
    }

    /**
     * Get default route conditions for all instances
     * @return array
     * @api
     */
    public static function getDefaultConditions()
    {
        return self::$defaultConditions;
    }

    /**
     * Get escapePattern
     *
     * @return bool
     * @api
     */
    public function getEscapePattern()
    {
        return $this->escapePattern;
    }

    /**
     * Set escapePattern
     *
     * @param bool $escapePattern
     * @api
     */
    public function setEscapePattern($escapePattern)
    {
        $this->escapePattern = (bool)$escapePattern;
    }

    /**
     * Get route pattern
     *
     * @return string
     * @api
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set route pattern
     *
     * @param  string $pattern
     * @api
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * Parses controller string to an almost callable array
     *
     * @param string $callable
     *
     * @return bool|array
     */
    public static function stringToCallable($callable)
    {
        $matches = [];
        if (preg_match('!^([^\:])\:([[:alnum:]])$!', $callable, $matches)) {
            $callable = [$matches[1], $matches[2]];
        } else {
            $callable = false;
        }

        return $callable;
    }

    /**
     * [getControllerDependencies description]
     *
     * @return [type] [description]
     */
    public function getControllerDependencies()
    {
        return $this->controllerDependencies;
    }

    /**
     * Description
     *
     * @param type $controllerDependencies
     *
     * @return type
     */
    public function setControllerDependencies($controllerDependencies)
    {
        $this->controllerDependencies = $controllerDependencies;
    }

    /**
     * Get route callable
     *
     * @return mixed
     * @api
     */
    public function getCallable()
    {
         //Instantiate class constructor
        if (is_array($this->callable)) {

            $cParams = $this->getConstructorParams();

            if (!empty($cParams)) {
                $class = new \ReflectionClass($this->callable[0]);
                $instance = $class->newInstanceArgs($cParams);
            } else {
                $instance = new $this->callable[0];
            }

            $this->callable = [$instance, $this->callable[1]];
        }

        return $this->callable;
    }

    /**
     * Set route callable
     *
     * @param  mixed                     $callable
     * @throws \InvalidArgumentException If argument is not callable
     * @api
     */
    public function setCallable(callable $callable)
    {
        $this->callable = $callable;
        return $this;
    }

    /**
     * Get route conditions
     *
     * @return array
     * @api
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Set route conditions
     *
     * @param  array $conditions
     * @api
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * Gets the The Route context.
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the The Route context.
     *
     * @param mixed $context the context
     *
     * @return static
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get route name (this may be null if not set)
     *
     * @return string|null
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set route name
     *
     * @param string $name
     * @api
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * Get route parameters
     *
     * @return array
     * @api
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set route parameters
     *
     * @param  array $params
     * @api
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Get constructor parameters
     *
     * @return array
     * @api
     */
    public function getConstructorParams()
    {
        return $this->constructorParams;
    }

    /**
     * Set constructor parameters
     *
     * @param  array $params
     * @api
     */
    public function setConstructorParams(array $params)
    {
        $this->constructorParams = $params;
        return $this;
    }

    /**
     * Get route parameter value
     *
     * @param  string                    $index Name of URL parameter
     * @return string
     * @throws \InvalidArgumentException        If route parameter does not exist at index
     * @api
     */
    public function getParam($index)
    {
        if (!isset($this->params[$index])) {
            throw new\InvalidArgumentException('Route parameter does not exist at specified index');
        }

        return $this->params[$index];
    }

    /**
     * Set route parameter value
     *
     * @param  string                    $index     Name of URL parameter
     * @param  mixed                     $value     The new parameter value
     * @return void
     * @throws \InvalidArgumentException            If route parameter does not exist at index
     * @api
     */
    public function setParam($index, $value)
    {
        if (!isset($this->params[$index])) {
            throw new \InvalidArgumentException('Route parameter does not exist at specified index');
        }
        $this->params[$index] = $value;
        return $this;
    }

    /**
     * Add supported HTTP methods (this method accepts an unlimited number of string arguments)
     * @api
     */
    public function setHttpMethods()
    {
        $args = func_get_args();
        $this->methods = $args;
        return $this;
    }

    /**
     * Get supported HTTP methods
     *
     * @return array
     * @api
     */
    public function getHttpMethods()
    {
        return $this->methods;
    }

    /**
     * Append supported HTTP methods (this method accepts an unlimited number of string arguments)
     * @api
     */
    public function appendHttpMethods()
    {
        $args = func_get_args();
        if (count($args) && is_array($args[0])) {
            $args = $args[0];
        }
        $this->methods = array_merge($this->methods, $args);
    }

    /**
     * Append supported HTTP methods (alias for Route::appendHttpMethods)
     *
     * @return Route
     * @api
     */
    public function via()
    {
        $args = func_get_args();
        if (count($args) && is_array($args[0])) {
            $args = $args[0];
        }
        $this->methods = array_merge($this->methods, $args);
        return $this;
    }

    /**
     * Detect support for an HTTP method
     *
     * @param  string $method
     * @return bool
     * @api
     */
    public function supportsHttpMethod($method)
    {
        return in_array($method, $this->methods);
    }

    /**
     * Get middleware
     *
     * @return array[Callable]
     * @api
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Set middleware
     *
     * This method allows middleware to be assigned to a specific Route.
     * If the method argument `is_callable` (including callable arrays!),
     * we directly append the argument to `$this->middleware`. Else, we
     * assume the argument is an array of callables and merge the array
     * with `$this->middleware`.  Each middleware is checked for is_callable()
     * and an InvalidArgumentException is thrown immediately if it isn't.
     *
     * @param  Callable|array[Callable]
     * 
     * @return Route
     * @throws \InvalidArgumentException If argument is not callable or not an array of callables.
     * @api
     */
    public function setMiddleware($middleware)
    {
        if (is_callable($middleware)) {
            $this->middleware[] = $middleware;
        } elseif (is_array($middleware)) {
            foreach ($middleware as $callable) {
                if (!is_callable($callable)) {
                    throw new \InvalidArgumentException('All Route middleware must be callable');
                }
            }
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            throw new \InvalidArgumentException('Route middleware must be callable or an array of callables');
        }

        return $this;
    }

    /**
     * Matches URI?
     *
     * Parse this route's pattern, and then compare it to an HTTP resource URI
     * This method was modeled after the techniques demonstrated by Dan Sosedoff at:
     *
     * http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
     *
     * @param  string $resourceUri A Request URI
     * 
     * @return bool
     * @api
     */
    public function matches($resourceUri)
    {
        $pattern = (string)$this->pattern;

        //These chars are valid URI chars but at the same time are reserved regex chars.
        //See: http://en.wikipedia.org/wiki/Percent-encoding#Types_of_URI_characters.
        //Do not use `preg_quote` because there are a few exceptions:
        //( and ) are used to describe optional parameters
        //determines a wildcard parameter

        if ($this->getEscapePattern()) {
            $charsToEscape =[
                "!", "*", "'", "$", ",", "/", "?", "#", "[ ", "]", "."
            ];

            foreach ($charsToEscape as $toEscape) {
                $pattern = str_replace($toEscape, "\\$toEscape", $pattern);
            }
        }

        //Convert URL params into regex patterns, construct a regex for this route, init params
        $patternAsRegex = preg_replace_callback(
            '#:([\w])\?#',
            [$this, 'matchesCallback'],
            str_replace(')', ')?', $pattern)
        );

        if (substr($this->pattern, -1) === '/') {
            $patternAsRegex .= '?';
        }

        $regex = '#^' . $patternAsRegex . '$#';

        if ($this->caseSensitive === false) {
            $regex .= 'i';
        }

        //Cache URL params' names and values if this route matches the current HTTP request
        if (!preg_match($regex, $resourceUri, $paramValues)) {
            return false;
        }
        foreach ($this->paramNames as $name) {
            if (isset($paramValues[$name])) {
                if (isset($this->paramNamesPath[$name])) {
                    $this->params[$name] = explode('/', urldecode($paramValues[$name]));
                } else {
                    $this->params[$name] = urldecode($paramValues[$name]);
                }
            }
        }

        return true;
    }

    /**
     * Convert a URL parameter (e.g. ":id", ":id") into a regular expression
     *
     * @param  array  $m URL parameters
     *
     * @return string    Regular expression for URL parameter
     */
    protected function matchesCallback($m)
    {
        $this->paramNames[] = $m[1];
        if (isset($this->conditions[$m[1]])) {
            return '(?P<' . $m[1] . '>' . $this->conditions[$m[1]] . ')';
        }
        if (substr($m[0], -1) === '') {
            $this->paramNamesPath[$m[1]] = 1;

            return '(?P<' . $m[1] . '>.)';
        }

        return '(?P<' . $m[1] . '>[^/])';
    }

    /**
     * Set route name
     *
     * @param  string      $name The name of the route
     *
     * @return Route
     * @api
     */
    public function name($name)
    {
        $this->setName($name);
        return $this;
    }

    /**
     * Merge route conditions
     *
     * @param  array       $conditions Key-value array of URL parameter conditions
     *
     * @return Route
     * @api
     */
    public function conditions(array $conditions)
    {
        $this->conditions = array_merge($this->conditions, $conditions);
        return $this;
    }

    /**
     * Dispatch route
     *
     * This method invokes the route object's callable. If middleware is
     * registered for the route, each callable middleware is invoked in
     * the order specified.
     *
     * @return bool
     *
     * @param  mixed  $context the object context in which the callable should be invoked
     *
     * @return object          The return value of the route callable, or FALSE on error
     * @api
     */
    public function dispatch($context = null)
    {
        foreach ($this->middleware as $mw) {
            call_user_func_array($mw, array($this));
        }

        $context = (!$context = null) ? $context : $this->getContext();

        if ($context && $this->callable instanceof \Closure && method_exists($this->callable, 'bindTo')) {
            $this->callable = $this->callable->bindTo($context);
        }

        $return = call_user_func_array($this->getCallable(), array_values($this->getParams()));
        return ($return === false) ? false : true;
    }
}
