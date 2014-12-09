<?php
namespace Brainwave\Application;

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

use Brainwave\Application\Provider\ApplicationServiceProvider;
use Brainwave\Config\Provider\ConfigServiceProvider;
use Brainwave\Contracts\Application\Application as ApplicationContract;
use Brainwave\Contracts\Application\BootableProvider as BootableProviderContract;
use Brainwave\Exception\FatalErrorException;
use Brainwave\Filesystem\Provider\FilesystemServiceProvider;
use Brainwave\Http\Exception\HttpException;
use Brainwave\Http\Exception\NotFoundHttpException;
use Brainwave\Http\Provider\RequestServiceProvider;
use Brainwave\Http\Provider\ResponseServiceProvider;
use Brainwave\Middleware\Middleware;
use Brainwave\Support\Arr;
use Brainwave\Translator\Provider\TranslatorServiceProvider;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Brainwave\Http\Exception\GoneHttpException;
use Brainwave\Http\Exception\ConflictHttpException;
use Brainwave\Http\Exception\BadRequestHttpException;
use Brainwave\Http\Exception\AccessDeniedHttpException;
use Brainwave\Http\Exception\UnauthorizedHttpException;
use Brainwave\Http\Exception\NotAcceptableHttpException;
use Brainwave\Http\Exception\LengthRequiredHttpException;
use Brainwave\Http\Exception\TooManyRequestsHttpException;
use Brainwave\Http\Exception\MethodNotAllowedHttpException;
use Brainwave\Http\Exception\PreconditionFailedHttpException;
use Brainwave\Http\Exception\ServiceUnavailableHttpException;
use Brainwave\Http\Exception\PreconditionRequiredHttpException;
use Brainwave\Http\Exception\UnsupportedMediaTypeHttpException;

/**
 * Application
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Application extends Container implements ApplicationContract
{
    /**
     * The Brainwave framework version.
     *
     * @var string
     */
    const VERSION = '0.9.4-dev';

    /**
     * All registered providers
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
     *
     * @var array
     */
    protected $config = [
        'app' => [
            'ext' => 'php',
            'group' => 'app',
        ],
        'http' => [
            'ext' => 'php',
            'group' => 'http',
        ],
        'mail' => [
            'ext' => 'php',
            'group' => 'mail',
        ],
        'cache' => [
            'ext' => 'php',
            'group' => 'cache',
        ],
        'services' => [
            'ext' => 'php',
            'group' => 'services',
        ],
        'session' => [
            'ext' => 'php',
            'group' => 'session',
        ],
        'cookies' => [
            'ext' => 'php',
            'group' => 'cookies',
        ],
        'view' => [
            'ext' => 'php',
            'group' => 'view',
        ],
        'autoload' => [
            'ext' => 'php',
            'group' => 'autoload',
        ],
        'database' => [
            'ext' => 'php',
            'group' => 'database',
        ],
    ];

    /**
     * Application paths
     *
     * @var array
     */
    public static $paths;

    /**
     * Instantiate a new Application
     *
     * Let's start make magic!
     */
    public function __construct()
    {
        parent::__construct();

        StaticalProxyManager::setFacadeApplication($this);

        // App setting
        $this['env'] = null;

        // Middleware stack
        $this['middleware'] = [$this];

        // Application
        $this['app'] = $this;

        $this->register(new ResponseServiceProvider());
        $this->register(new RequestServiceProvider());

        // Needed App services
        $this->register(new ApplicationServiceProvider());

        // Filessystem
        $this->register(new FilesystemServiceProvider());

        $this->registerConfig();

        $this->registerTranslator();

        // Register providers
        foreach ($this['settings']['services::providers'] as $provider => $arr) {
            $this->register(new $provider(), $arr);
        }
    }

    /**
     * Register Config
     *
     * @return Brainwave\Config\Manager
     */
    protected function registerConfig()
    {
        // Here we will bind the install paths into the container as strings that can be
        // accessed from any point in the system. Each path key is prefixed with path
        // so that they have the consistent naming convention inside the container.
        foreach (static::$paths as $key => $value) {
            $this[$key] = $value;
        }

        // Settings
        $this->register(
            new ConfigServiceProvider(),
            ['settings.path' => static::$paths['path.config']]
        );

        //Load config files
        foreach ($this->config as $file => $setting) {
            $this['settings']->bind(
                $file.'.'.$setting['ext'],
                $setting['group'],
                null,
                null
            );
        }
    }

    /**
     * Register Translator
     *
     * @return \Brainwave\Translator\Manager
     */
    protected function registerTranslator()
    {
        // Translator
        $this->register(new TranslatorServiceProvider(), ['translator.path' => static::$paths['path.config']]);

        // Load lang files
        if ($this['settings']['app::language.files'] !== null) {
            foreach ($this['settings']['app::language.files'] as $file => $lang) {
                $this['translator']->bind(
                    $file.'.'.$lang['ext'],
                    $lang['group'],
                    $lang['env'],
                    $lang['namespace']
                );
            }
        }
    }

    /**
     * Bind the installation paths to the application.
     *
     * @param array $paths
     *
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
     * @param \Pimple\ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                            $values   An array of values that customizes the provider
     *
     * @return Application
     */
    public function register(ServiceProviderInterface $provider, array $values = [])
    {
        parent::register($provider, $values);

        $this->providers[] = $provider;

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by the developer's application logics.
        if ($this->booted && $provider instanceof BootableProviderContract) {
            $provider->boot($this);
        }
    }

    /**
     * Mounts controllers under the given route prefix.
     *
     * @param string                                           $prefix      The route prefix
     * @param ControllerCollection|ControllerProviderInterface $controllers A ControllerCollection or a ControllerProviderInterface instance
     *
     * @return Application
     */
    public function mount($prefix, $controllers)
    {
        if ($controllers instanceof ControllerProviderInterface) {
            $controllers = $controllers->connect($this);
        }

        if (!$controllers instanceof ControllerCollection) {
            throw new \LogicException('The "mount" method takes either a ControllerCollection or a ControllerProviderInterface instance.');
        }

        //TODO $this['controllers']->mount($prefix, $controllers);

        return $this;
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return boolean|null
     */
    public function isDownForMaintenance()
    {
        //TODO
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
     * @param \Closure $callback
     *
     * @return void
     */
    public function down(\Closure $callback)
    {
        $this['events']->hook('brainwave.app.down', $callback);
    }

    /**
     * Register an application error handler.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function error(\Closure $callback)
    {
        $this['exception']->error($callback);
    }

    /**
     * Register an error handler for fatal errors.
     *
     * @param \Closure $callback
     *
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
     * @param  int    $code
     * @param  string $message
     * @param  array  $headers
     * @return void
     *
     * @throws \Brainwave\Http\Exception\HttpException
     * @throws \Brainwave\Http\Exception\GoneHttpException
     * @throws \Brainwave\Http\Exception\ConflictHttpException
     * @throws \Brainwave\Http\Exception\NotFoundHttpException
     * @throws \Brainwave\Http\Exception\BadRequestHttpException
     * @throws \Brainwave\Http\Exception\AccessDeniedHttpException
     * @throws \Brainwave\Http\Exception\UnauthorizedHttpException
     * @throws \Brainwave\Http\Exception\NotAcceptableHttpException
     * @throws \Brainwave\Http\Exception\LengthRequiredHttpException
     * @throws \Brainwave\Http\Exception\TooManyRequestsHttpException
     * @throws \Brainwave\Http\Exception\MethodNotAllowedHttpException
     * @throws \Brainwave\Http\Exception\PreconditionFailedHttpException
     * @throws \Brainwave\Http\Exception\ServiceUnavailableHttpException
     * @throws \Brainwave\Http\Exception\PreconditionRequiredHttpException
     * @throws \Brainwave\Http\Exception\UnsupportedMediaTypeHttpException
     */
    public function abort($code, $message = '', array $headers = array())
    {
        switch ($code) {
            // error code 400
            case Response::HTTP_BAD_REQUEST:
                throw new BadRequestHttpException($message);
            // error code 401
            case Response::HTTP_UNAUTHORIZED:
                throw new UnauthorizedHttpException($message);
            // error code 403
            case Response::HTTP_FORBIDDEN:
                throw new AccessDeniedHttpException($message);
            // error code 404
            case Response::HTTP_NOT_FOUND:
                throw new NotFoundHttpException($message);
            // error code 405
            case Response::HTTP_METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($message);
            // error code 406
            case Response::HTTP_NOT_ACCEPTABLE:
                throw new NotAcceptableHttpException($message);
            // error code 409
            case Response::HTTP_CONFLICT:
                throw new ConflictHttpException($message);
            // error code 410
            case Response::HTTP_GONE:
                throw new GoneHttpException($message);
            // error code 411
            case Response::HTTP_LENGTH_REQUIRED:
                throw new LengthRequiredHttpException($message);
            // error code 412
            case Response::HTTP_PRECONDITION_FAILED:
                throw new PreconditionFailedHttpException($message);
            // error code 415
            case Response::HTTP_UNSUPPORTED_MEDIA_TYPE:
                throw new UnsupportedMediaTypeHttpException($message);
            // error code 428
            case Response::HTTP_PRECONDITION_REQUIRED:
                throw new PreconditionRequiredHttpException($message);
            // error code 429
            case Response::HTTP_TOO_MANY_REQUESTS:
                throw new TooManyRequestsHttpException($message);
            // error code 503
            case Response::HTTP_SERVICE_UNAVAILABLE:
                throw new ServiceUnavailableHttpException($message);
            // all other error codes including 500
            default:
                throw new HttpException($code, $message, null, $headers);
        }
    }

    /**
     * Register a 404 error handler.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function missing(\Closure $callback)
    {
        $this->error(function (NotFoundHttpException $e) use ($callback) {
            return call_user_func($callback, $e);
        });
    }

    /**
     * Creates a streaming response.
     *
     * @param mixed   $callback A valid PHP callback
     * @param integer $status   The response status code
     * @param array   $headers  An array of response headers
     *
     * @return StreamedResponse
     */
    public function stream($callback = null, $status = 200, array $headers = array())
    {
        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * Convert some data into a JSON response.
     *
     * @param mixed   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     *
     * @return JsonResponse
     */
    public function json($data = array(), $status = 200, array $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Sends a file.
     *
     * @param \SplFileInfo|string $file               The file to stream
     * @param integer             $status             The response status code
     * @param array               $headers            An array of response headers
     * @param null|string         $contentDisposition The type of Content-Disposition to set automatically with the filename
     *
     * @return BinaryFileResponse
     *
     * @throws \RuntimeException When the feature is not supported, before http-foundation v2.2
     */
    public function sendFile($file, $status = 200, array $headers = array(), $contentDisposition = null)
    {
        return new BinaryFileResponse($file, $status, $headers, true, $contentDisposition);
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['settings']->get('app::locale', 'en');
    }

    /**
     * Set the current application locale.
     *
     * @param string $locale
     *
     * @return Application
     */
    public function setLocale($locale)
    {
        $this['settings']->set('app::locale', $locale);

        return $this;
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array  $parameters
     *
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
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Brainwave Middleware.
     *
     * @param Middleware
     */
    public function middleware(Middleware $newMiddleware)
    {
        $middleware = $this['middleware'];

        if (in_array($newMiddleware, $middleware, true)) {
            $middlewareClass = get_class($newMiddleware);
            throw new \RuntimeException(
                "Circular Middleware setup detected.
                Tried to queue the same Middleware instance ({$middlewareClass}) twice."
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
                if ($provider instanceof BootableProviderContract) {
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
     * @param mixed $callback
     *
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param  mixed $callback
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
     * @param  array $callbacks
     * @return void
     */
    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

    /**
     * Set the application request for the console environment.
     *
     * @return string
     */
    public function setRequestForConsoleEnvironment()
    {
        //TODO
        //return $this->runningInConsole();
    }

    /**
     * Run
     *
     * This method invokes the middleware stack, including the core Brainwave application;
     * the result is an array of HTTP status, header, and body. These three items
     * are returned to the HTTP client.
     */
    public function run()
    {
        $this['events']->applyHook('before');

        if ($this['env'] !== 'console') {
            ob_start('mb_output_handler');
        }

        $this->boot();

        $dispatcher = $this['route']->getDispatcher();

        $response = $dispatcher->dispatch('GET', '/');

        $response->send();

        // TODO
        // Invoke middleware and application stack
        // try {
        //     $this['middleware'][0];
        // } catch (\Exception $e) {
        //     $this['exception']->handleException($e);
        // }

        $this['events']->applyHook('after');
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
    }

    /**
     * Shutdown The Application
     *
     * @return Flush the output buffer. Turns off exception handling
     */
    public function shutdown()
    {
        $this['exception']->unregister();
    }

    /**
     * Dynamically access application services.
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Dynamically set application services.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if application services exists.
     *
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically remove application services.
     *
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Gets a parameter or an object.
     *
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($key)
    {
        return parent::offsetGet(str_replace('_', '.', $key));
    }

    /**
     * Sets a parameter or an object.
     *
     * @param mixed $value The value of the parameter or a closure to define an object
     *
     * @return Application
     */
    public function offsetSet($key, $value)
    {
        parent::offsetSet(str_replace('_', '.', $key), $value);

        return $this;
    }

    /**
     * Checks if a parameter or an object is set.
     *
     *
     * @return Boolean
     */
    public function offsetExists($key)
    {
        return parent::offsetExists(str_replace('_', '.', $key));
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param $key
     *
     * @return string $id The unique identifier for the parameter or object
     */
    public function offsetUnset($key)
    {
        parent::offsetUnset(str_replace('_', '.', $key));
    }
}
