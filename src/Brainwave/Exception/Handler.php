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

use Pimple\Container;
use Psr\Log\LoggerInterface;
use Brainwave\Contracts\Exception\FatalErrorException as FatalError;
use Brainwave\Contracts\Http\HttpException as HttpExceptionContract;

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
    protected $container;

    /**
     * The log implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

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
     * @param \Pimple\Container        $container
     * @param \Psr\Log\LoggerInterface $log
     */
    public function __construct(Container $container, LoggerInterface $log, $debug = true)
    {
        $this->container = $container;
        $this->log       = $log;
        $this->debug     = $debug;
    }

    /**
     * Report or log an exception.
     *
     * @param \Exception $e
     *
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
     * @param  $env
     *
     * @return void
     */
    public function register($env)
    {
        $this->registerErrorHandler();

        $this->registerExceptionHandler();

        if ($env !== 'testing') {
            $this->registerShutdownHandler();
        }
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
        set_exception_handler([$this, 'handleUncaughtException']);
    }

    /**
     * Register the PHP shutdown handler.
     *
     * @return void
     */
    protected function registerShutdownHandler()
    {
        register_shutdown_function([$this, 'handleShutdown']);
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
     * Convert errors into ErrorException objects
     *
     * This method catches PHP errors and converts them into ErrorException objects;
     * these ErrorException objects are then thrown and caught by Brainwave's
     * built-in or custom error handlers.
     *
     * @param int    $level   The numeric type of the Error
     * @param string $message The error message
     * @param string $file    The absolute path to the affected file
     * @param int    $line    The line number of the error in the affected file
     *
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = array())
    {
        if ($level & error_reporting()) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception.
     *
     * @param \Exception $exception
     *
     * @return void
     */
    public function handleUncaughtException($exception)
    {
        $this->handleException($exception)->send();
    }

    /**
     * Handle a console exception.
     *
     * @param \Exception $exception
     *
     * @return string
     */
    public function handleConsole($exception)
    {
        return $this->callCustomHandlers($exception, true);
    }

    /**
     * Handle the given exception.
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function callCustomHandlers($exception)
    {
        foreach ($this->handlers as $handler) {
            // If this exception handler does not handle the given exception, we will just
            // go the next one. A handler may type-hint an exception that it handles so
            //  we can have more granularity on the error handling for the developer.
            if (!$this->handlesException($handler, $exception)) {
                continue;
            } elseif ($exception instanceof HttpExceptionContract) {
                $code = $exception->getStatusCode();
            } else {
                $code = '500';
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
     * Format an exception thrown by a handler.
     *
     * @param \Exception $e
     *
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
     * Register an application error handler.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function error(\Closure $callback)
    {
        array_unshift($this->handlers, $callback);
    }

    /**
     * Register an application error handler at the bottom of the stack.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function pushError(\Closure $callback)
    {
        $this->handlers[] = $callback;
    }

    /**
     * Handle an exception for the application.
     *
     * @param \Exception $exception
     *
     * @return \Brainwave\Http\Response
     */
    public function handleException($exception)
    {
        $response = $this->callCustomHandlers($exception);

        // If one of the custom error handlers returned a response, we will send that
        // response back to the client after preparing it. This allows a specific
        // type of exceptions to handled by a Closure giving great flexibility.
        if (!is_null($response)) {
            return $this->prepareResponse($response);
        }

        // If no response was sent by this custom exception handler, we will call the
        // default exception displayer for the current application context and let
        // it show the exception to the user / developer based on the situation.
        return $this->displayException($exception);
    }

    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     *
     * @return void
     */
    protected function displayException($exception)
    {
        $settings = $this->container['settings'];

        if ($settings->get('app::mode', 'production') === 'development' ||
            $settings->get('app::mode', 'production') === 'testing'
        ) {
            $displayer = $this->debug ? $this->container['exception.debug'] : $this->container['exception.plain'];

            return $displayer->display($exception);
        }

        return $this->noException($exception);
    }

    /**
     * Logs Exception if debug is false
     *
     * @param \Exception $exception
     *
     * @return void
     */
    protected function noException(\Exception $exception)
    {
        //Log error
        $this->report($exception);
        $this->container['response']->setStatus(503);

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
        $this->container['view']->make(
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
     * @param int $type
     *
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * Determine if the given handler handles this exception.
     *
     * @param \Closure   $handler
     * @param \Exception $exception
     *
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
     * @param \ReflectionFunction $reflection
     * @param \Exception          $exception
     *
     * @return boolean
     */
    protected function hints(\ReflectionFunction $reflection, $exception)
    {
        $parameters = $reflection->getParameters();

        $expected = $parameters[0];

        return !$expected->getClass() || $expected->getClass()->isInstance($exception);
    }
}
