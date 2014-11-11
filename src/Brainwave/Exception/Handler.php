<?php
namespace Brainwave\Exception;

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
use \Brainwave\Routing\Route;
use \Psr\Log\LoggerInterface;
use \Brainwave\Exception\Exception\Stop;
use \Brainwave\Contracts\Exception\FatalErrorException as FatalError;

/**
 * ExceptionHandler
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Handler
{
    /**
     * The container repository implementation.
     *
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * The log implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * Template setting
     *
     * @var array
     */
    protected $template = [];

    /**
     * All of the register exception handlers.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Indicates if the application is in debug mode.
     *
     * @var bool
     */
    protected $debug;

    /**
     * Create a new exception handler instance.
     *
     * @param \Pimple\Container        $app
     * @param \Psr\Log\LoggerInterface $log
     */
    public function __construct(Container $app, LoggerInterface $log, $debug = true)
    {
        $this->app   = $app;
        $this->log   = $log;
        $this->debug = $debug;
    }

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(\Exception $e)
    {
        $this->log->error((string) $e);
    }

    /**
     * Register the exception /
     * error handlers for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->registerErrorHandler();

        $this->registerExceptionHandler();

        if ($this->app['env'] !== 'testing') {
            $this->registerShutdownHandler();
        }
    }

    /**
     * Unregister the PHP error handler.
     *
     * @return void
     */
    public function unregister()
    {
        restore_error_handler();
    }

    /**
     * Register the PHP error handler.
     *
     * @return void
     */
    protected function registerErrorHandler()
    {
        set_error_handler([$this, 'handleError']);
    }

    /**
     * Register the PHP exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler()
    {
        set_exception_handler([$this, 'handleException']);
    }

    /**
     * Register the PHP shutdown handler.
     * @return void
     */
    protected function registerShutdownHandler()
    {
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown()
    {
        $error = error_get_last();

        // If an error has occurred that has not been displayed, we will create a fatal
        // error exception instance and pass it into the regular exception handling
        // code so it can be displayed back out to the developer for information.
        if (!is_null($error)) {
            extract($error);

            if (!$this->isFatal($type)) {
                return;
            }

            $this->handleException(new FatalError($message, $type, 0, $file, $line));
        }
    }

    /**
     * Convert errors into ErrorException objects
     *
     * This method catches PHP errors and converts them into ErrorException objects;
     * these ErrorException objects are then thrown and caught by Brainwave's
     * built-in or custom error handlers.
     *
     * @param  int            $level   The numeric type of the Error
     * @param  string         $message  The error message
     * @param  string         $file The absolute path to the affected file
     * @param  int            $line The line number of the error in the affected file
     *
     * @throws ErrorException
     */
    public function handleError($level, $message = '', $file = '', $line = '')
    {
        if ($level & error_reporting()) {
            throw new \ErrorException($message, $level, 0, $file, $line);
        }
    }

    /**
     * Error Handler
     *
     * This method defines or invokes the application-wide Error handler.
     * There are two contexts in which this method may be invoked:
     *
     * 1. When declaring the handler:
     *
     * If the $argument parameter is callable, this
     * method will register the callable to be invoked when an uncaught
     * Exception is detected, or when otherwise explicitly invoked.
     * The handler WILL NOT be invoked in this context.
     *
     * 2. When invoking the handler:
     *
     * If the $argument parameter is not callable, Brainwave assumes you want
     * to invoke an already-registered handler. If the handler has been
     * registered and is callable, it is invoked and passed the caught Exception
     * as its one and only argument. The error handler's output is captured
     * into an output buffer and sent as the body of a 500 HTTP Response.
     *
     * @param  mixed $argument A callable or an exception
     *
     * @return void
     *
     * @throws \Brainwave\Exception\Exception\Stop
     */
    public function error($argument)
    {
        if ($argument instanceof \Closure) {
            //Register error handler
            array_unshift($this->handlers, $argument);

        } elseif (is_string($argument)) {
            $argument = Route::stringToCallable($argument);

            if (!$argument) {
                try {
                    throw new Stop();
                } catch (Stop $e) {
                    $this->displayException($e);
                }
            }

            array_unshift($this->handlers, $argument);
        }

        //Invoke error handler
        $this->app['response']->setStatus(500);
        $this->app['response']->write($this->handleException($argument), true);

        throw new Stop();
    }

    /**
     * Not found handter
     *
     * @return type
     */
    public function pageNotFound()
    {
        $this->app->contentType('text/html');
        $this->app['response']->setStatus(404);

        $link    = $this->app['request']->getScriptName();
        $content = <<<EOF
<div>
    <i class="fa fa-circle-o"></i>
    <span>
        The page you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly.
        <br/>
        If all else fails, you can visit our home page at the link below.
    </span>
</div>
<div>
    <i class="fa fa-circle-o"></i><a href="$link/">Visit the Home Page</a>
</div>
EOF;

        $templateSettings = $this->getTemplate();
        $this->app['view']->make(
            $templateSettings['404.engine'],
            $templateSettings['404.template'],
            [
                'title' => '404 Error',
                'header' => 'Sorry, the page you are looking for could not be found.',
                'content' => $content,
                'footer' => 'Copyright &copy;'.date('Y').' narrowspark'
            ]
        );
    }

    /**
     * Handle the given exception.
     *
     * @param  \Exception $exception
     *
     * @return void
     */
    protected function callCustomHandlers($exception)
    {
        foreach ($this->handlers as $handler) {
            // If this exception handler does not handle the given exception, we will just
            // go the next one. A handler may type-hint an exception that it handles so
            //  we can have more granularity on the error handling for the developer.
            if (!$this->handlesException($handler, $exception)) {
                continue;
            }

            // We will wrap this handler in a try / catch and avoid white screens of death
            // if any exceptions are thrown from a handler itself. This way we will get
            // at least some errors, and avoid errors with no data or not log writes.
            try {
                $response = $handler($exception, $code, $fromConsole);
            } catch (\Exception $e) {
                $response = $this->formatException($e);
            }

            // If this handler returns a "non-null" response, we will return it so it will
            // get sent back to the browsers. Once the handler returns a valid response
            // we will cease iterating through them and calling these other handlers.
            if (isset($response) && $response !== null) {
                return $response;
            }
        }
    }

    /**
     * Format an exception thrown by a handler.
     *
     * @param  \Exception  $e
     * @return string
     */
    protected function formatException(\Exception $e)
    {
        if ($this->debug) {
            $location = $e->getMessage().' in '.$e->getFile().':'.$e->getLine();
            return 'Error in exception handler: '.$location;
        }

        return 'Error in exception handler.';
    }


    /**
     * Call error handler
     *
     * This will invoke the custom or default error handler
     * and return its output.
     *
     * @param  Exception|null $argument
     *
     * @return string
     */
    public function handleException($argument = null)
    {
        ob_start();

        if (is_array($this->app['error'])) {
            call_user_func_array(array(new $this->app['error'][0], $this->app['error'][1]), array($argument));
        } elseif (is_callable($this->app['error'])) {
            call_user_func($this->app['error'], [$argument]);
        }

        return $this->displayException($argument);

        ob_get_clean();
    }

    /**
     * Display the given exception to the user.
     *
     * @param  \Exception $exception
     *
     * @return void
     */
    protected function displayException(\Exception $exception)
    {
        $settings = $this->app['settings'];

        if ($settings->get('app::mode', 'production') === 'development' &&
            $settings->get('app::debug', false) === true ||
            $settings->get('app::mode', 'production') === 'testing' &&
            $settings->get('app::debug', false) === true
        ) {
            ($settings['app::exception.handler'] === 'whoops') ?
            $ext = $this->app['exception.debug']->display($exception) :
            $ext = $this->app['exception.plain']->display($exception);
        } else {
            $ext = $this->noException($exception);
        }

        return $ext;
    }

    /**
     * Logs Exception if debug is false
     *
     * @param  \Exception  $exception
     *
     * @return void
     */
    protected function noException(\Exception $exception)
    {
        //Log error
        $this->report($exception);
        $this->app['response']->setStatus(503);

        $content = <<<EOF
<div>
    <i class="fa fa-circle-o"></i>
    <span>
        A website error has occurred.
    </span>
</div>
<div>
    <i class="fa fa-circle-o"></i>
    <span>
        We may be working on fixing this already, but if it keeps happening let us know.
    </span>
</div>
EOF;

        $templateSettings = $this->getTemplate();
        $this->app['view']->make(
            $templateSettings['503.engine'],
            $templateSettings['503.template'],
            [
                'title'   => '404 Error',
                'header'  => 'Egad!',
                'content' => $content,
                'footer'  => 'Copyright &copy;'.date('Y').' narrowspark'
            ]
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int   $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * Determine if the given handler handles this exception.
     *
     * @param  \Closure    $handler
     * @param  \Exception  $exception
     * @return bool
     */
    protected function handlesException(\Closure $handler, $exception)
    {
        $reflection = new \ReflectionFunction($handler);

        return $reflection->getNumberOfParameters() == 0 || $this->hints($reflection, $exception);
    }

    /**
     * Determine if the given handler type hints the exception.
     *
     * @param  \ReflectionFunction  $reflection
     * @param  \Exception  $exception
     *
     * @return boolen
     */
    protected function hints(\ReflectionFunction $reflection, $exception)
    {
        $parameters = $reflection->getParameters();

        $expected = $parameters[0];

        return ! $expected->getClass() || $expected->getClass()->isInstance($exception);
    }

    /**
     * Set template for exception
     *
     * @param string $type   template type
     * @param string $engine render engine
     * @param string $path   template path
     *
     * @return boolen
     */
    public function setTemplate($type, $engine, $path)
    {
        $this->template = [
            "{$type}.engine" => $engine,
            "{$type}.path"   => $path,
        ];

        return $this;
    }

    /**
     * Get template for exception
     *
     * @return array
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the debug level for the handler.
     *
     * @param  bool  $debug
     * @return void
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
}
