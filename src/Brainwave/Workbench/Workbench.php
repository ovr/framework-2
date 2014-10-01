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
 * @version     0.9.2-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Pimple\Container;
use \Brainwave\Support\Arr;
use \Brainwave\Http\Headers;
use \Brainwave\Http\Request;
use \Brainwave\Http\Response;
use \GuzzleHttp\Stream\Stream;
use \Brainwave\Cookie\CookieJar;
use \Brainwave\Config\FileLoader;
use \Brainwave\Config\Configuration;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Middleware\Middleware;
use \Brainwave\Workbench\StaticalProxy;
use \Brainwave\Environment\Environment;
use \Brainwave\Workbench\Exception\Stop;
use \Brainwave\Workbench\Exception\Pass;
use \Brainwave\Exception\ExceptionHandler;
use \Brainwave\Config\ConfigurationHandler;
use \Brainwave\Http\Exception\HttpException;
use \Brainwave\Exception\FatalErrorException;
use \Brainwave\Workbench\StaticalProxyResolver;
use \Brainwave\Environment\EnvironmentDetector;
use \Brainwave\Http\Exception\NotFoundHttpException;
use \Brainwave\Routing\Controller\ControllerCollection;
use \Brainwave\Routing\Interfaces\ControllerProviderInterface;
use \Brainwave\Workbench\Interfaces\BootableProviderInterface;

/**
 * Workbench
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
    const BRAINWAVE_VERSION = '0.9.2-dev';

    /**
     * Has the app response been sent to the client?
     *
     * @var bool
     */
    protected $responded = false;

    /**
     * @var object The object context for dispatch closures
     */
    protected $dispatchContext;

    /**
     * All provicers
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Boots all providers.
     *
     * @var boolean
     */
    protected $booted = false;

    /**
     * The array of booting callbacks.
     *
     * @var array
     */
    protected $bootingCallbacks = [];

    /**
     * The array of booted callbacks.
     *
     * @var array
     */
    protected $bootedCallbacks = [];

    /**
     * Narrowspark config files
     * @var array
     */
    protected $config = [
        'app' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'mail' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'cache' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'services' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'session' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'cookies' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'view' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'autoload' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
        'database' => [
            'ext' => 'php',
            'namespace' => 'config',
            'env' => '',
            'group' => ''
        ],
    ];

    /**
     * @var integer Counts the number of available routes.
     */
    private $routeCount = 0;

    /**
     * Workbench paths
     *
     * @var array
     */
    public static $paths;

    /**
     * Constructor
     * @api
     */
    public function __construct()
    {
        parent::__construct();

        StaticalProxy::setFacadeApplication($this);

        // App setting
        $this['env']                = null;
        //
        $this['error']              = null;
        // Not Found
        $this['notFound']           = null;

        // Settings
        $this['settings'] = function ($c) {
            $config = new Configuration(new ConfigurationHandler, new FileLoader);
            $config->addPath(static::$paths['path']);

            //Load config files
            foreach ($this->config as $file => $setting) {
                $config->bind(
                    $file.'.'.$setting['ext'],
                    $setting['namespace'],
                    $setting['env'],
                    $setting['group']
                );
            }

            return $config;
        };

        // Here we will bind the install paths into the container as strings that can be
        // accessed from any point in the system. Each path key is prefixed with path
        // so that they have the consistent naming convention inside the container.
        foreach (static::$paths as $key => $value) {
            $this[$key] = $value;
        }

        // Environment
        $this['environment'] = function ($c) {
            return new Environment($_SERVER);
        };

        // Request
        $this['request'] = function ($c) {
            $environment = $c['environment'];
            $headers = new Headers($environment);
            $CookieJar = new CookieJar($headers);
            if ($c['settings']->get('CookieJar.encrypt', false) ===  true) {
                $CookieJar->decrypt($c['crypt']);
            }

            return new Request($environment, $headers, $CookieJar);
        };

        // Response
        $this['response'] = function ($c) {
            $headers = new Headers();
            $CookieJar = new CookieJar();
            $response = new Response($headers, $CookieJar);
            $response->setProtocolVersion('HTTP/' . $c['settings']->get('http.version', '1.1'));

            return $response;
        };

        // Register providers
        foreach ($this['settings']->get('services.providers', []) as $provider => $arr) {
            $this->register(new $provider, $arr);
        }

        // Exception handler
        $this['exception'] = function ($c) {
            $exception = new ExceptionHandler($this, $c['settings']->get('app.charset', 'en'));
            return $exception;
        };

        // Set Loader an Path
        $this['translator']->setLoader(new FileLoader)->addPath(static::$paths['path']);

        // Load lang files
        if (!is_null($this['settings']->get('app.language.files', null))) {
            foreach ($this['settings']->get('app.language.files', []) as $file => $lang) {
                $this['translator']->bind(
                    $file.'.'.$lang['ext'],
                    $lang['namespace'],
                    $lang['env'],
                    $lang['group']
                );
            }
        }

        // Middleware stack
        $this['middleware'] = [$this];

        // StaticalProxy
        $this['statical.resolver'] = function ($c) {
            return new StaticalProxyResolver();
        };
        $this['statical'] = function ($c) {
            return new StaticalProxy($c);
        };
    }

    /**
     * Bind the installation paths to the application.
     *
     * @param  array  $paths
     * @return void
     */
    public static function bindInstallPaths(array $paths)
    {
        static::$paths['path'] = realpath($paths['app']);

        // Each path key is prefixed with path
        // so that they have the consistent naming convention.
        foreach (Arr::arrayExcept($paths, ['app']) as $key => $value) {
            static::$paths["path.{$key}"] = realpath($value);
        }
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return Application
     */
    public function register(ServiceProviderInterface $provider, array $values = [])
    {
        $this->providers[] = $provider;

        $provider->register($this);

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by the developer's application logics.
        if ($this->booted) {
            $provider->boot();
        }

        return $provider;
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
     * Register a maintenance mode event listener.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function down(\Closure $callback)
    {
        $this['events']->hook('brainwave.app.down', $callback);
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
     * @throws \Brainwave\Http\Exception\HttpException
     * @throws \Brainwave\Http\Exception\NotFoundHttpException
     */
    public function abort($code, $message = '', array $headers = [])
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
        return $this['env'] = Arr::with(new EnvironmentDetector())->detect($envs);
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
     * Determine if we are running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        return $this['env'] == 'testing';
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['settings']->get('app.locale', 'en');
    }

    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['settings']->set('app.locale', $locale);
        return $this;
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

        $route = $this['route.factory']->make($pattern, $callable);

        $this->routeCount++;
        $route->setName((string)$this->routeCount);

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
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = array())
    {
        $parameters = array_filter($parameters);

        if (!empty($parameters)) {
            //TODO
        } else {

        }

        return $this[$abstract];
    }

    /**
     * Get controllers route
     * @return Route
     * @api
     */
    public function getControllersRoutes()
    {
        $route = [];
        $controllers = $this->controller_factory->getControllers();
        foreach ($controllers as $controller) {
            $route[] = $controller->getRouteName();
        }

        return $route;
    }

    /**
     * Set the object context ($this) for dispatch callables
     * @param object $context The object context ($this) in which
     */
    public function setDispatchContext($context)
    {
        $this->dispatchContext = $context;
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
                call_user_func([new $this['notFound'][0], $this['notFound'][1]]);
            } elseif (is_callable($this['notFound'])) {
                call_user_func($this['notFound']);
            } else {
                call_user_func([$this['exception'], 'pageNotFound']);
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
        if (!in_array($type, ['strong', 'weak'])) {
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
                'The "`"SCRIPT_FILENAME" server variable could not be found.
                 It is required by "Workbench::root()".'
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
     * Send a File
     *
     * This method streams a local or remote file to the client
     *
     * @param  string $file        The URI of the file, can be local or remote
     * @param  string $contentType Optional content type of the stream,
     *         if not specified Brainwave will attempt to get this
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
        if (in_array($newMiddleware, $middleware, true)) {
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

    /**
     * Boots all service providers.
     *
     * This method is automatically called by finalize(), but you can use it
     * to boot all service providers when not handling a request.
     */
    public function boot()
    {
        if (!$this->booted) {
            $this->booted = true;

            foreach ($this->providers as $provider) {
                if ($provider instanceof BootableProviderInterface) {
                    $provider->boot($this);
                }
            }
        }

        $this->bootApplication();
    }

    /**
     * Boot the application and fire app callbacks.
     *
     * @return void
     */
    protected function bootApplication()
    {
        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Register a new boot listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @param  array  $callbacks
     * @return void
     */
    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

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
        $this['events']->applyHook('before');

        if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            ob_start("ob_gzhandler");
        } else {
            ob_start('mb_output_handler');
        }

        $this->boot();

        // Invoke middleware and application stack
        try {
            $this['middleware'][0]->call();
        } catch (\Exception $e) {
            $this['response']->write($this['exception']->handleException($e), true);
        }

        // Finalize and send response
        $this->finalize();

        $this['events']->applyHook('after');
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
            $this['events']->applyHook('before.router');
            $dispatched = false;
            $matchedRouting = $this['router']->getMatchedRoutes($request->getMethod(), $request->getPathInfo(), true);
            foreach ($matchedRouting as $route) {
                try {
                    $this['events']->applyHook('before.dispatch');
                    $dispatched = $route->dispatch($this->dispatchContext);
                    $this['events']->applyHook('after.dispatch');
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
            $this['events']->applyHook('after.router');
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
     * CookieJar, body, and server variables against the set of registered
     * application Routing. The result response object is returned.
     *
     * @param  string $url             The request URL
     * @param  string $method          The request method
     * @param  array  $headers         Associative array of request headers
     * @param  array  $CookieJar         Associative array of request CookieJar
     * @param  string $body            The request body
     * @param  array  $serverVariables Custom $_SERVER variables
     * @return Response
     */
    public function subRequest(
        $url,
        $method = 'GET',
        array $headers = [],
        array $CookieJar = [],
        $body = '',
        array $serverVariables = []
    ) {
        // Build sub-request and sub-response
        $environment = new Environment(array_merge([
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $url,
            'SCRIPT_NAME' => '/index.php'
        ], $serverVariables));

        $headers = new Headers($environment);

        $CookieJar = new CookieJar($headers);

        $subRequest = new Request($environment, $headers, $CookieJar, $body);
        $subResponse = new Response(new Headers(), new CookieJar());

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
        if (!$this->booted) {
            $this->boot();
        }

        if (!$this->responded) {
            $this->responded = true;

            // Encrypt CookieJar
            if ($this['settings']['CookieJar.encrypt']) {
                $this['response']->encryptCookieJar($this['crypt']);
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
