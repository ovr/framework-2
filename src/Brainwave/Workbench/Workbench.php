<?php
namespace Brainwave\Workbench;

//
PHP_OS == "Windows" || PHP_OS == "WINNT" ? define("DS", "\\") : define("DS", "/");

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Pimple\Container;
use \Brainwave\Crypt\Crypt;
use \Brainwave\Flash\Flash;
use \Brainwave\Http\Headers;
use \Brainwave\Http\Cookies;
use \Brainwave\Http\Request;
use \Brainwave\Routing\Route;
use \Brainwave\Http\Response;
use \GuzzleHttp\Stream\Stream;
use \Brainwave\Routing\Router;
use \Brainwave\Support\Helper;
use \Brainwave\Support\Facades;
use \Brainwave\Security\Acl\Acl;
use \Brainwave\View\ViewFactory;
use \Brainwave\Config\Configuration;
use \Brainwave\Routing\RouteFactory;
use \Brainwave\Middleware\Middleware;
use \Brainwave\Environment\Environment;
use \Brainwave\Config\Driver\PhpLoader;
use \Brainwave\Config\Driver\IniLoader;
use \Brainwave\Config\Driver\XmlLoader;
use \Brainwave\Config\Driver\JsonLoader;
use \Brainwave\Resolvers\CallableResolver;
use \Brainwave\Exception\ExceptionHandler;
use \Brainwave\Resolvers\ContainerResolver;
use \Brainwave\Config\ConfigurationHandler;
use \Brainwave\Exception\SlimException\Stop;
use \Brainwave\Exception\SlimException\Pass;
use \Brainwave\Routing\ControllerCollection;
use \Brainwave\Resolvers\DependencyResolver;
use \Brainwave\View\Interfaces\ViewInterface;
use \Brainwave\Exception\FatalErrorException;
use \Brainwave\Environment\EnvironmentDetector;
use \Brainwave\Support\Services\ServiceManager;
use \Brainwave\Exception\HttpException\HttpException;
use \Brainwave\Exception\HttpException\NotFoundHttpException;
use \Brainwave\Routing\Interfaces\ControllerProviderInterface;

/**
 * CacheManager
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 * @property Environment    $environment
 * @property Response       $response
 * @property Request        $request
 * @property Router         $router
 */
class Workbench extends Container
{
    /**
     * @const string
     */
    const VERSION = '3.0.0';

    /**
     * The Brainwave framework version.
     *
     * @var string
     */
    const BRAINWAVE_VERSION = '0.8.0-dev';

    /**
     * Has the app response been sent to the client?
     * @var bool
     */
    protected $responded = false;

    /**
     * @var object The object context for dispatch closures
     */
    protected $dispatchContext;

    /**
     * Array of Controller Constructor Parameters
     * @var array
     */
    protected $inject;

    /**
     * Application hooks
     * @var array
     */
    protected $hooks = array(
        'before' => array(array()),
        'before.router' => array(array()),
        'before.dispatch' => array(array()),
        'after.dispatch' => array(array()),
        'after.router' => array(array()),
        'after' => array(array())
    );

    /**
     * Constructor
     * @api
     */
    public function __construct()
    {
        parent::__construct();

        // App setting
        $this['debug']              = true;
        $this['env']                = '';
        $this['mode']               = 'development';
        $this['facade']             = true;
        $this['https']              = false;
        $this['translator.locale']  = 'en';
        //
        $this['error']              = null;
        // Not Found
        $this['notFound']           = null;
        // Logger
        $this['log']                = null;

        // Settings
        $this['settings'] = function ($c) {
            return new Configuration(new ConfigurationHandler);
        };

        //
        $this->setDispatchContext($this['settings']['routes.context']);

        // Environment
        $this['environment'] = function ($c) {
            return new Environment($_SERVER);
        };

        // Request
        $this['request'] = function ($c) {
            $environment = $c['environment'];
            $headers = new Headers($environment);
            $cookies = new Cookies($headers);
            if ($c['settings']['cookies.encrypt'] ===  true) {
                $cookies->decrypt($c['crypt']);
            }

            return new Request($environment, $headers, $cookies);
        };

        // Response
        $this['response'] = function ($c) {
            $headers = new Headers();
            $cookies = new Cookies();
            $response = new Response($headers, $cookies);
            $response->setProtocolVersion('HTTP/' . $c['settings']['http.version']);

            return $response;
        };

        // Exception handler
        $this['exception'] = function ($c) {
            return new ExceptionHandler($this, $c['settings']['app.charset']);
        };

        // Register Exception
        $this['exception']->register();

        // Service manager
        $this['services'] = function ($c) {
            return new ServiceManager($this);
        };

        // Route
        $this['routes.factory'] = function ($c) {
            return new RouteFactory($c, $c['routes.resolver']);
        };

        // Route factory resolver
        $this['routes.resolver'] = function ($c) {
            $options = array(
                'route_class'    => $c['settings']['routes.route_class'],
                'case_sensitive' => $c['settings']['routes.case_sensitive'],
            );

            return function ($pattern, $callable) use ($options) {
                return new $options['route_class']($pattern, $callable, $options['case_sensitive']);
            };
        };

        // Router
        $this['router'] = function ($c) {
            return new Router();
        };

        // Route Callable Resolver
        $this['resolver'] = function($c) {
            if ($c['settings']['callable.resolver'] == 'DependencyResolver') {
                $resolver = new DependencyResolver($c);
            } elseif ($c['settings']['callable.resolver'] == 'ContainerResolver') {
                $resolver = new ContainerResolver($c);
            } elseif ($c['settings']['callable.resolver'] == 'CallableResolver') {
                $resolver = new CallableResolver();
            } else {
                throw new \Exception("Set a Callable Resolver");
            }

            return $resolver;
        };

        // Route
        $this['route'] = function ($c) {
            return new Route($pattern = null, $callable = null, $c['settings']['routes.case_sensitive']);
        };

        // Controllers factory
        $this['controllers.factory'] = function ($c) {
            return new ControllerCollection($c['route'], $c['router']);
        };

        // View
        $this['view'] = function ($c) {
            return new ViewFactory($c);
        };

        // Register all framework provider
        $allProviders = array_merge($this['settings']['app.defaultProviders'], $this['settings']['app.provides']);
        foreach ($allProviders as $provider => $values) {
            $this['services']->register(new $provider(), $values);
        }

        // Middleware stack
        $this['middleware'] = array($this);

        // Facade
        $this['facades'] = function ($c) {
            Facades::clearResolvedInstances();
            return new Facades($c);
        };

        //Check if facade is active
        if ($this['facade']) {
            return $this['facades'];
        }
    }

    /**
     * Load config files
     * @param  string $parser choose config parser
     * @param  string $file path to config file
     * @return void
     */
    public function bindConfig($parser, $file)
    {
        $file = preg_replace('/\//', DS, $file);

        if ($parser == 'php') {
            $configDriver = new PhpLoader();
        } elseif ($parser == 'json') {
            $configDriver = new JsonLoader();
        } elseif ($parser == 'ini') {
            $configDriver = new IniLoader();
        } elseif ($parser == 'xml') {
            $configDriver = new XmlLoader();
        } else {
            throw new \Exception('Set a correct parser for config');
        }

        $configDriver->load($file);

        //Load file config to application settings
        foreach ($configDriver->getData() as $configName => $configValue) {
            $this->config($configName, $configValue);
        }
    }

    /**
     * Default settings for Brainwave
     * @return void
     */
    public function configFiles()
    {
        foreach ($this->config('app.default.configs') as $configName) {
            $this->bindConfig('php', $this['path.app'] . '/config/'.$configName.'.config.php');
        }

        return $this;
    }

    /**
     * Configure App Settings
     *
     * This method defines application settings and acts as a setter and a getter.
     *
     * If only one argument is specified and that argument is a string, the value
     * of the setting identified by the first argument will be returned, or NULL if
     * that setting does not exist.
     *
     * If only one argument is specified and that argument is an associative array,
     * the array will be merged into the existing application settings.
     *
     * If two arguments are provided, the first argument is the name of the setting
     * to be created or updated, and the second argument is the setting value.
     *
     * @param  string|array $name   If a string, the name of the setting to set or retrieve.
     *                              Else an associated array of setting names and values
     * @param  mixed        $value  If name is a string, the value of the setting identified by $name
     * @return mixed                The value of a setting if only one argument is a string
     * @api
     */
    public function config($name, $value = null)
    {
        if (func_num_args() === 1) {
            if (is_array($name)) {
                foreach ($name as $key => $value) {
                    $this['settings'][$key] = $value;
                }
            } else {
                if (!isset($this['settings'][$name])) {
                    return null;
                }

                $value = $this['settings'][$name];
                return is_callable($value) ? call_user_func($value) : $value;
            }
        } else {
            $this['settings'][$name] = $value;
        }
    }

    /**
     * Bind the installation paths to the application.
     *
     * @param  array  $paths
     * @return void
     */
    public function bindInstallPaths(array $paths)
    {
        $this['path.app'] = realpath($paths['app']);

        // Here we will bind the install paths into the container as strings that can be
        // accessed from any point in the system. Each path key is prefixed with path
        // so that they have the consistent naming convention inside the container.
        foreach (Helper::arrayExcept($paths, array('app')) as $key => $value) {
            $this["path.{$key}"] = realpath($value);
        }
    }

    /**
     * Escapes a text for HTML.
     *
     * @param string  $text         The input text to be escaped
     * @param integer $flags        The flags (@see htmlspecialchars)
     * @param string  $charset      The charset
     * @param Boolean $doubleEncode Whether to try to avoid double escaping or not
     *
     * @return string Escaped text
     */
    public function escape($text, $flags = ENT_COMPAT, $charset = null, $doubleEncode = true)
    {
        return htmlspecialchars($text, $flags, $charset ?: $this['charset'], $doubleEncode);
    }

    /**
     * Register an application error handler.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function error(\Closure $callback)
    {
        $this['exception']->error($callback);
    }

    /**
     * Register an error handler for fatal errors.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function fatal(\Closure $callback)
    {
        $this->error(function (FatalErrorException $e) use ($callback) {
            return call_user_func($callback, $e);
        });
    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws \Brainwave\Exception\HttpException\HttpException
     * @throws \Brainwave\Exception\HttpException\NotFoundHttpException
     */
    public function abort($code, $message = '', array $headers = array())
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        } else {
            throw new HttpException($code, $message, null, $headers);
        }
    }

    /**
     * Register a 404 error handler.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function missing(\Closure $callback)
    {
        $this->error(function (NotFoundHttpException $e) use ($callback) {
            return call_user_func($callback, $e);
        });
    }

    /**
     * Get or check the current application environment.
     *
     * @param  dynamic
     * @return string
     */
    public function environment()
    {
        if (count(func_get_args()) > 0) {
            return in_array($this['env'], func_get_args());
        } else {
            return $this['env'];
        }
    }

    /**
     * Detect the application's current environment.
     *
     * @param  array|string  $envs
     * @return string
     */
    public function detectEnvironment($envs)
    {
        return $this['env'] = Helper::with(new EnvironmentDetector())->detect($envs);
    }

    /**
     * Configure App for a given mode
     *
     * This method will immediately invoke the callable if
     * the specified mode matches the current application mode.
     * Otherwise, the callable is ignored. This should be called
     * only _after_ you initialize your Brainwave app.
     *
     * @param  string $mode
     * @param  mixed  $callable
     * @api
     */
    public function setMode($mode, $callable)
    {
        if ($mode == $this['mode'] && is_callable($callable)) {
            call_user_func($callable);
        }
    }

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isLocal()
    {
        return $this['env'] == 'local';
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->config('app.locale');
    }

    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->config('app.locale', $locale);

        //translator
        $this['translator.locale'] = $locale;
    }

    /**
     * Add GET|POST|PUT|PATCH|DELETE route
     *
     * Adds a new route to the router with associated callable. This
     * route will only be invoked when the HTTP request's method matches
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
     * to be invoked when the route matches an HTTP request.
     *
     * You may also provide an unlimited number of in-between arguments;
     * each interior argument must be callable and will be invoked in the
     * order specified before the route's callable is invoked.
     *
     * USAGE:
     *
     * App::get('/foo'[, middleware, middleware, ...], callable);
     *
     * @param  array
     * @return Route
     */
    protected function mapRoute($args)
    {
        $pattern = array_shift($args);
        $callable = $this['resolver']->build(array_pop($args));

        $route = $this['routes.factory']->make($pattern, $callable);
        $this['router']->map($route);
        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        return $route;
    }

    /**
     * Add route without HTTP method
     * @return Route
     */
    public function map()
    {
        $args = func_get_args();
        return $this->mapRoute($args);
    }

    /**
     * Add GET route
     * @return Route
     * @api
     */
    public function get()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_GET, Request::METHOD_HEAD);
    }

    /**
     * Add POST route
     * @return Route
     * @api
     */
    public function post()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_POST);
    }

    /**
     * Add PUT route
     * @return Route
     * @api
     */
    public function put()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_PUT);
    }

    /**
     * Add PATCH route
     * @return Route
     * @api
     */
    public function patch()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_PATCH);
    }

    /**
     * Add DELETE route
     * @return Route
     * @api
     */
    public function delete()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_DELETE);
    }

    /**
     * Add OPTIONS route
     * @return Route
     * @api
     */
    public function options()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via(Request::METHOD_OPTIONS);
    }

    /**
     * Add route for any HTTP method
     * @return Route
     * @api
     */
    public function any()
    {
        $args = func_get_args();
        return $this->mapRoute($args)->via("ANY");
    }

    /**
     * Mounts controllers under the given route prefix.
     * @return Application
     */
    public function mount()
    {
        $args = func_get_args();

        $prefix = '';
        $controller = $args[0];

        if (count($args) > 1) {
            $prefix = array_shift($args);
            $controller = array_pop($args);
        }

        if ($controller instanceof ControllerProviderInterface) {
            $controller = $controller->connect($this);
        }

        if (!$controller instanceof ControllerCollection) {
            throw new \LogicException(
                'The "mount" method takes either a ControllerCollection or a ControllerProviderInterface instance.'
            );
        }

        $controller->flush($prefix);
    }

    /**
     * Get controllers routes
     * @return Route
     * @api
     */
    public function getControllersRoutes()
    {
        $routes = array();
        $controllers = $this->controller_factory->getControllers();
        foreach ($controllers as $controller) {
            $routes[] = $controller->getRouteName();
        }

        return $routes;
    }

    /**
     * Set the object context ($this) for dispatch callables
     *
     * @param object $context The object context ($this) in which
     */
    public function setDispatchContext($context)
    {
        $this->dispatchContext = $context;
    }

    //TODO finish Inject
    /**
     * Description
     * @return arry inject
     */
    public function getInject()
    {
        return $this->inject;
    }

    /**
     * Description
     * @param type array $route or type string $route
     * @param type array $inject
     * @return type array
     */
    public function inject($route, array $inject)
    {
        //Add params to routes
        if (is_array($route)) {

            //Counting Routes
            $count = count($route);

            foreach ($route as $url) {
                foreach ($n as $params) {
                    $i[$params % $count];
                    $routeInject[$url] = $i;
                }
            }
        } else {
            $routeInject[$route] = $inject;
        }

        $this->inject = $routeInject;
        return $this;
    }

    /**
     * Not Found Handler
     *
     * This method defines or invokes the application-wide Not Found handler.
     * There are two contexts in which this method may be invoked:
     *
     * 1. When declaring the handler:
     *
     * If the $callable parameter is not null and is callable, this
     * method will register the callable to be invoked when no
     * Routing match the current HTTP request. It WILL NOT invoke the callable.
     *
     * 2. When invoking the handler:
     *
     * If the $callable parameter is null, Brainwave assumes you want
     * to invoke an already-registered handler. If the handler has been
     * registered and is callable, it is invoked and sends a 404 HTTP Response
     * whose body is the output of the Not Found handler.
     *
     * @param  mixed $callable Anything that returns true for is_callable()
     * @api
     */
    public function notFound($callable = null)
    {
        if (is_callable($callable)) {
            $this['notFound'] = function () use ($callable) {
                return $callable;
            };
        } elseif (is_string($callable)) {
            $callable = Route::stringToCallable($callable);

            if (!$callable) {
                throw new Stop();
            }

            $this['notFound'] = function () use ($callable) {
                return $callable;
            };
        } else {
            ob_start();
            if (is_array($this['notFound'])) {
                call_user_func(array(new $this['notFound'][0], $this['notFound'][1]));
            } elseif (is_callable($this['notFound'])) {
                call_user_func($this['notFound']);
            } else {
                call_user_func(array($this['exception'], 'pageNotFound'));
            }
            $this->halt('404');
        }
    }

    /**
     * Set Last-Modified HTTP Response Header
     *
     * Set the HTTP 'Last-Modified' header and stop if a conditional
     * GET request's `If-Modified-Since` header matches the last modified time
     * of the resource. The `time` argument is a UNIX timestamp integer value.
     * When the current request includes an 'If-Modified-Since' header that
     * matches the specified last modified time, the application will stop
     * and send a '304 Not Modified' response to the client.
     *
     * @param  int                       $time  The last modified UNIX timestamp
     * @throws \InvalidArgumentException        If provided timestamp is not an integer
     * @api
     */
    public function lastModified($time)
    {
        if (is_integer($time)) {
            $this['response']->setHeader('Last-Modified', gmdate('D, d M Y H:i:s T', $time));
            if ($time === strtotime($this['request']->getHeader('IF_MODIFIED_SINCE'))) {
                $this->halt('304');
            }
        } else {
            throw new \InvalidArgumentException(
                'Brainwave::lastModified only accepts an integer UNIX timestamp value.'
            );
        }
    }

    /**
     * Set ETag HTTP Response Header
     *
     * Set the etag header and stop if the conditional GET request matches.
     * The `value` argument is a unique identifier for the current resource.
     * The `type` argument indicates whether the etag should be used as a strong or
     * weak cache validator.
     *
     * When the current request includes an 'If-None-Match' header with
     * a matching etag, execution is immediately stopped. If the request
     * method is GET or HEAD, a '304 Not Modified' response is sent.
     *
     * @param  string                    $value The etag value
     * @param  string                    $type  The type of etag to create; either "strong" or "weak"
     * @throws \InvalidArgumentException        If provided type is invalid
     * @api
     */
    public function etag($value, $type = 'strong')
    {
        // Ensure type is correct
        if (!in_array($type, array('strong', 'weak'))) {
            throw new \InvalidArgumentException('Invalid Brainwave::etag type. Expected "strong" or "weak".');
        }

        // Set etag value
        $value = '"' . $value . '"';
        if ($type === 'weak') {
            $value = 'W/'.$value;
        }
        $this['response']->setHeader('ETag', $value);

        // Check conditional GET
        if ($etagsHeader = $this['request']->getHeader('IF_NONE_MATCH')) {
            $etags = preg_split('@\s*,\s*@', $etagsHeader);
            if (in_array($value, $etags) || in_array('*', $etags)) {
                $this->halt('304');
            }
        }
    }

    /**
     * Set Expires HTTP response header
     *
     * The `Expires` header tells the HTTP client the time at which
     * the current resource should be considered stale. At that time the HTTP
     * client will send a conditional GET request to the server; the server
     * may return a 200 OK if the resource has changed, else a 304 Not Modified
     * if the resource has not changed. The `Expires` header should be used in
     * conjunction with the `etag()` or `lastModified()` methods above.
     *
     * @param string|int    $time   If string, a time to be parsed by `strtotime()`;
     *                              If int, a UNIX timestamp;
     * @api
     */
    public function expires($time)
    {
        if (is_string($time)) {
            $time = strtotime($time);
        }
        $this['response']->setHeader('Expires', gmdate('D, d M Y H:i:s T', $time));
    }

    /**
     * Set HTTP cookie to be sent with the HTTP response
     *
     * @param  string     $name     The cookie name
     * @param  string     $value    The cookie value
     * @param  int|string $time     The duration of the cookie;
     *                                  If integer, should be UNIX timestamp;
     *                                  If string, converted to UNIX timestamp with `strtotime`;
     * @param  string     $path     The path on the server in which the cookie will be available on
     * @param  string     $domain   The domain that the cookie is available to
     * @param  bool       $secure   Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection to/from the client
     * @param  bool       $httponly When TRUE the cookie will be made accessible only through the HTTP protocol
     * @api
     */
    public function setCookie($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $settings = array(
            'value' => $value,
            'expires' => is_null($time) ? $this->config('cookies.lifetime') : $time,
            'path' => is_null($path) ? $this->config('cookies.path') : $path,
            'domain' => is_null($domain) ? $this->config('cookies.domain') : $domain,
            'secure' => is_null($secure) ? $this->config('cookies.secure') : $secure,
            'httponly' => is_null($httponly) ? $this->config('cookies.httponly') : $httponly
        );
        $this['response']->setCookie($name, $settings);
    }

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @param  string  $name
     * @param  string  $value
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httpOnly
     */
    public function setCookieForever($name, $value, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        return $this->setCookie($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Get value of HTTP cookie from the current HTTP request
     *
     * Return the value of a cookie from the current HTTP request,
     * or return NULL if cookie does not exist. Cookies created during
     * the current request will not be available until the next request.
     *
     * @param  string      $name    The cookie name
     * @return string|null
     * @api
     */
    public function getCookie($name)
    {
        return $this['request']->getCookie($name);
    }

    /**
     * Delete HTTP cookie (encrypted or unencrypted)
     *
     * Remove a Cookie from the client. This method will overwrite an existing Cookie
     * with a new, empty, auto-expiring Cookie. This method's arguments must match
     * the original Cookie's respective arguments for the original Cookie to be
     * removed. If any of this method's arguments are omitted or set to NULL, the
     * default Cookie setting values (set during Brainwave::init) will be used instead.
     *
     * @param  string $name     The cookie name
     * @param  string $path     The path on the server in which the cookie will be available on
     * @param  string $domain   The domain that the cookie is available to
     * @param  bool   $secure   Indicates that the cookie should only be transmitted over a secure
     *                          HTTPS connection from the client
     * @param  bool   $httponly When TRUE the cookie will be made accessible only through the HTTP protocol
     * @api
     */
    public function deleteCookie($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $settings = array(
            'domain' => is_null($domain) ? $this->config('cookies.domain') : $domain,
            'path' => is_null($path) ? $this->config('cookies.path') : $path,
            'secure' => is_null($secure) ? $this->config('cookies.secure') : $secure,
            'httponly' => is_null($httponly) ? $this->config('cookies.httponly') : $httponly
        );
        $this['response']->removeCookie($name, $settings);
    }

    /**
     * Get the absolute path to this Brainwave application's root directory
     *
     * This method returns the absolute path to the filesystem directory in which
     * the Brainwave app is instantiated. The return value WILL NOT have a trailing slash.
     *
     * @return string
     * @throws \RuntimeException If $_SERVER[SCRIPT_FILENAME] is not available
     * @api
     */
    public function root()
    {
        if ($this['environment']->has('SCRIPT_FILENAME') === false) {
            throw new \RuntimeException(
                'The "`"SCRIPT_FILENAME" server variable could not be found. It is required by "Workbench::root()".'
            );
        }

        return dirname($this['environment']->get('SCRIPT_FILENAME'));
    }

    /**
     * Stop
     *
     * The thrown exception will be caught in application's `call()` method
     * and the response will be sent as is to the HTTP client.
     *
     * @throws Stop
     * @api
     */
    public function stop()
    {
        throw new Stop();
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
        $this['response']->setStatus($status);
        $this['response']->write($message, true);
        $this->stop();
    }

    /**
     * Pass
     *
     * The thrown exception is caught in the application's `call()` method causing
     * the router's current iteration to stop and continue to the subsequent route if available.
     * If no subsequent matching Routing are found, a 404 response will be sent to the client.
     *
     * @throws Pass
     * @api
     */
    public function pass()
    {
        throw new Pass();
    }

    /**
     * Set the HTTP response Content-Type
     * @param  string $type The Content-Type for the Response (ie. text/html)
     * @api
     */
    public function contentType($type)
    {
        $this['response']->setHeader('Content-Type', $type);
    }

    /**
     * Set the HTTP response status code
     * @param  int $code The HTTP response status code
     * @api
     */
    public function status($code)
    {
        $this['response']->setStatus($code);
    }

    /**
     * Get the URL for a named route
     * @param  string            $name   The route name
     * @param  array             $params Associative array of URL parameters and replacement values
     * @throws \RuntimeException         If named route does not exist
     * @return string
     * @api
     */
    public function urlFor($name, $params = array())
    {
        return $this['request']->getScriptName() . $this['router']->urlFor($name, $params);
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
        $this['response']->redirect($url, $status);
        $this->halt($status);
    }

    /**
     * Assign hook
     * @param  string $name     The hook name
     * @param  mixed  $callable A callable object
     * @param  int    $priority The hook priority; 0 = high, 10 = low
     * @api
     */
    public function hook($name, $callable, $priority = 10)
    {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = array(array());
        }
        if (is_callable($callable)) {
            $this->hooks[$name][(int) $priority][] = $callable;
        }
    }

    /**
     * Invoke hook
     * @param  string $name    The hook name
     * @param  mixed  $hookArg (Optional) Argument for hooked functions
     * @api
     */
    public function applyHook($name, $hookArg = null)
    {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = array(array());
        }
        if (!empty($this->hooks[$name])) {
            // Sort by priority, low to high, if there's more than one priority
            if (count($this->hooks[$name]) > 1) {
                ksort($this->hooks[$name]);
            }
            foreach ($this->hooks[$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        call_user_func($callable, $hookArg);
                    }
                }
            }
        }
    }

    /**
     * Get hook listeners
     *
     * Return an array of registered hooks. If `$name` is a valid
     * hook name, only the listeners attached to that hook are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are hook names and whose values are arrays of listeners.
     *
     * @param  string     $name A hook name (Optional)
     * @return array|null
     * @api
     */
    public function getHooks($name = null)
    {
        if (!is_null($name)) {
            return isset($this->hooks[(string) $name]) ? $this->hooks[(string) $name] : null;
        } else {
            return $this->hooks;
        }
    }

    /**
     * Clear hook listeners
     *
     * Clear all listeners for all hooks. If `$name` is
     * a valid hook name, only the listeners attached
     * to that hook will be cleared.
     *
     * @param  string $name A hook name (Optional)
     * @api
     */
    public function clearHooks($name = null)
    {
        if (!is_null($name) && isset($this->hooks[(string) $name])) {
            $this->hooks[(string) $name] = array(array());
        } else {
            foreach ($this->hooks as $key => $value) {
                $this->hooks[$key] = array(array());
            }
        }
    }

    /**
     * Send a File
     *
     * This method streams a local or remote file to the client
     *
     * @param  string $file        The URI of the file, can be local or remote
     * @param  string $contentType Optional content type of the stream, if not specified Brainwave will attempt to get this
     * @api
     */
    public function sendFile($file, $contentType = false)
    {
        $fp = fopen($file, "r");
        $this['response']->setBody(new Stream($fp));
        if ($contentType) {
            $this['response']->setHeader("Content-Type", $contentType);
        } else {
            if (file_exists($file)) {
                //Set Content-Type
                if (extension_loaded('fileinfo')) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $type = $finfo->file($file);
                    $this['response']->setHeader("Content-Type", $type);
                } else {
                    $this['response']->setHeader("Content-Type", "application/octet-stream");
                }

                //Set Content-Length
                $stat = fstat($fp);
                $this['response']->setHeader("Content-Length", $stat['size']);
            } else {
                //Set Content-Type and Content-Length
                $data = stream_get_meta_data($fp);

                foreach ($data['wrapper_data'] as $header) {
                    if (strpos($header, ':') === false) {
                        continue;
                    }

                    list($k, $v) = explode(": ", $header, 2);

                    if ($k === "Content-Type") {
                        $this['response']->setHeader("Content-Type", $v);
                    } elseif ($k === "Content-Length") {
                        $this['response']->setHeader("Content-Length", $v);
                    }
                }
            }
        }
    }

    /**
     * Send a Process
     *
     * This method streams a process to a client
     *
     * @param  string $command     The command to run
     * @param  string $contentType Optional content type of the stream
     * @api
     */
    public function sendProcess($command, $contentType = "text/plain")
    {
        $this['response']->setBody(new Stream(popen($command, 'r')));
        $this['response']->setHeader("Content-Type", $contentType);
    }

    /**
     * Set Download
     *
     * This method triggers a download in the browser
     *
     * @param  string $filename Optional filename for the download
     * @api
     */
    public function setDownload($filename = false)
    {
        $h = "attachment;";
        if ($filename) {
            $h .= "filename='" . $filename . "'";
        }
        $this['response']->setHeader("Content-Disposition", $h);
    }

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Brainwave Middleware.
     *
     * @param  Middleware
     * @api
     */
    public function add(Middleware $newMiddleware)
    {
        $middleware = $this['middleware'];
        if (in_array($newMiddleware, $middleware)) {
            $middleware_class = get_class($newMiddleware);
            throw new \RuntimeException(
                "Circular Middleware setup detected.
                Tried to queue the same Middleware instance ({$middleware_class}) twice."
            );
        }
        $newMiddleware->setApplication($this);
        $newMiddleware->setNextMiddleware($this['middleware'][0]);
        array_unshift($middleware, $newMiddleware);
        $this['middleware'] = $middleware;
    }

    /********************************************************************************
    * Runner
    *******************************************************************************/

    /**
     * Run
     *
     * This method invokes the middleware stack, including the core Brainwave application;
     * the result is an array of HTTP status, header, and body. These three items
     * are returned to the HTTP client.
     *
     * @api
     */
    public function run()
    {
        $this->applyHook('before');

        if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            ob_start("ob_gzhandler");
        } else {
            ob_start('mb_output_handler');
        }

        // Invoke middleware and application stack
        try {
            $this['middleware'][0]->call();
        } catch (\Exception $e) {
            $this['response']->write($this['exception']->handleException($e), true);
        }

        // Finalize and send response
        $this->finalize();

        $this->applyHook('after');
    }

    /**
     * Shutdown The Application
     * @return Flush the output buffer.
     *         Turns off exception handling
     */
    public function shutdown()
    {
        ob_end_flush();
        $this['exception']->unregister();
    }

    /**
     * Dispatch request and build response
     *
     * This method will route the provided Request object against all available
     * application Routing. The provided response will reflect the status, header, and body
     * set by the invoked matching route.
     *
     * The provided Request and Response objects are updated by reference. There is no
     * value returned by this method.
     *
     * @param  Request  The request instance
     * @param  Response The response instance
     */
    protected function dispatchRequest(Request $request, Response $response)
    {
        try {
            ob_start();
            $this->applyHook('before.router');
            $dispatched = false;
            $matchedRouting = $this['router']->getMatchedRoutes($request->getMethod(), $request->getPathInfo(), false);
            foreach ($matchedRouting as $route) {
                try {
                    $this->applyHook('before.dispatch');
                    $dispatched = $route->dispatch($this->dispatchContext);
                    $this->applyHook('after.dispatch');
                    if ($dispatched) {
                        break;
                    }
                } catch (Pass $e) {
                    continue;
                }
            }
            if (!$dispatched) {
                $this->notFound();
            }
            $this->applyHook('after.router');
        } catch (Stop $e) {

        }
        $response->write(ob_get_clean());
    }

    /**
     * Perform a sub-request from within an application route
     *
     * This method allows you to prepare and initiate a sub-request, run within
     * the context of the current request. This WILL NOT issue a remote HTTP
     * request. Instead, it will route the provided URL, method, headers,
     * cookies, body, and server variables against the set of registered
     * application Routing. The result response object is returned.
     *
     * @param  string $url             The request URL
     * @param  string $method          The request method
     * @param  array  $headers         Associative array of request headers
     * @param  array  $cookies         Associative array of request cookies
     * @param  string $body            The request body
     * @param  array  $serverVariables Custom $_SERVER variables
     * @return Response
     */
    public function subRequest($url, $method = 'GET', array $headers = array(), array $cookies = array(), $body = '', array $serverVariables = array())
    {
        // Build sub-request and sub-response
        $environment = new Environment(array_merge(array(
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $url,
            'SCRIPT_NAME' => '/index.php'
        ), $serverVariables));

        $headers = new Headers($environment);

        $cookies = new Cookies($headers);

        $subRequest = new Request($environment, $headers, $cookies, $body);
        $subResponse = new Response(new Headers(), new Cookies());

        // Cache original request and response
        $oldRequest = $this['request'];
        $oldResponse = $this['response'];

        // Set sub-request and sub-response
        $this['request'] = $subRequest;
        $this['response'] = $subResponse;

        // Dispatch sub-request through application router
        $this->dispatchRequest($subRequest, $subResponse);

        // Restore original request and response
        $this['request'] = $oldRequest;
        $this['response'] = $oldResponse;

        return $subResponse;
    }

    /**
     * Call
     *
     * This method finds and iterates all route objects that match the current request URI.
     */
    public function call()
    {
        $this->dispatchRequest($this['request'], $this['response']);
    }

    /**
     * Finalize send response
     *
     * This method sends the response object
     */
    protected function finalize()
    {
        if (!$this->responded) {
            $this->responded = true;

            // Finalise session if it has been used
            if (isset($_SESSION)) {
                // Save flash messages to session
                $this['flash']->save();

                // Encrypt, save, close session
                if ($this->config('session.encrypt') === true) {
                    $this['session']->encrypt($this['crypt']);
                }
                $this['session']->save();
            }

            // Encrypt cookies
            if ($this['settings']['cookies.encrypt']) {
                $this['response']->encryptCookies($this['crypt']);
            }

            // Send response
            $this['response']->finalize($this['request'])->send();
        }
    }

    /**
     * Dynamically access application services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Dynamically set application services.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * Dynamically check if application services exists.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __isset($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Dynamically remove application services.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __unset($id)
    {
        $this->offsetUnset($id);
    }

    /**
     * Gets a parameter or an object.
     * @param string $id The unique identifier for the parameter or object
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($id)
    {
        return parent::offsetGet(str_replace('_', '.', $id));
    }

    /**
     * Sets a parameter or an object.
     * @param string           $id    The unique identifier for the parameter or object
     * @param mixed            $value The value of the parameter or a closure to define an object
     * @return Workbench\Workbench
     */
    public function offsetSet($id, $value)
    {
        parent::offsetSet(str_replace('_', '.', $id), $value);
        return $this;
    }

    /**
     * Checks if a parameter or an object is set.
     * @param string $id The unique identifier for the parameter or object
     * @return Boolean
     */
    public function offsetExists($id)
    {
        return parent::offsetExists(str_replace('_', '.', $id));
    }

    /**
     * Description
     * @param Unsets a parameter or an object.
     * @return string $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        parent::offsetUnset(str_replace('_', '.', $id));
    }
}
