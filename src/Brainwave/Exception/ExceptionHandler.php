<?php
namespace Brainwave\Exception;

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

use \Whoops\Run;
use \Brainwave\Routing\Route;
use \Brainwave\Workbench\Workbench;
use \Brainwave\Exception\PlainDisplayer;
use \Brainwave\Exception\BrainwaveException\Stop;
use \Brainwave\Exception\FatalErrorException as FatalError;

/**
 * ExceptionHandler
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class ExceptionHandler
{
    /**
     * Brainwave
     * @var void
     */
    private $app;

    /**
     * Set the editor
     * @var string
     */
    protected $whoopsEditor = 'sublime';

    /**
     * All of the register exception handlers.
     * @var array
     */
    protected $handlers = array();

    public function __construct(Workbench $app, $charset)
    {
        $this->app = $app;

        $this->plainDisplayer(new PlainDisplayer($app, strtolower($charset)));

        if (class_exists('\Whoops\Run')) {
            $this->whoopsDisplayer(new WhoopsDisplayer($app, strtolower($charset)));
        }
    }

    /**
     * Register the exception / error handlers for the application.
     *
     * @param  string  $environment
     * @return void
     */
    public function register()
    {
        $this->registerErrorHandler();

        $this->registerExceptionHandler();

        if ($this->app['env'] != 'testing') {
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
        set_error_handler(array($this, 'handleError'));
    }

    /**
     * Register the PHP exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler()
    {
        set_exception_handler(array($this, 'handleException'));
    }

    /**
     * Register the PHP shutdown handler.
     * @return void
     */
    protected function registerShutdownHandler()
    {
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    /**
     * Unregister the PHP error handler.
     * @return void
     */
    public function unregister()
    {
        restore_error_handler();
    }

    /**
     * Handle the PHP shutdown event.
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
     * Not found handter
     * @return type
     */
    public function pageNotFound()
    {
        $this->app->contentType('text/html');

        $title = '404 Error';
        $header = 'Sorry, the page you are looking for could not be found.';
        $link = $this->app['request']->getScriptName();
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
        $footer = 'Copyright &copy; ' . date('Y') . $this->app['settings']->get('app.footer', 'narrowspark');

        return $this->app['displayer.plain']->decorate($title, $header, $content, $footer, $this->app['displayer.plain']->getStylesheet('pageNotFound'));
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
     * @api
     */
    public function error($argument = null)
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
        } else {
            //Invoke error handler
            $this->app['response']->setStatus(500);
            $this->app['response']->write($this->handleException($argument), true);
            $this->app->stop();
        }
    }

    /**
     * Handle the given exception.
     *
     * @param  \Exception  $exception
     * @param  bool  $fromConsole
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
            } catch (Exception $e) {
                $response = $this->displayException($e);
            }

            // If this handler returns a "non-null" response, we will return it so it will
            // get sent back to the browsers. Once the handler returns a valid response
            // we will cease iterating through them and calling these other handlers.
            if (isset($response) && ! is_null($response)) {
                return $response;
            }
        }
    }

    /**
     * Call error handler
     *
     * This will invoke the custom or default error handler
     * and RETURN its output.
     *
     * @param  Exception|null $argument
     * @return string
     */
    public function handleException($argument = null)
    {
        ob_start();

        if (is_array($this->app['error'])) {
            call_user_func_array(array(new $this->app['error'][0], $this->app['error'][1]), array($argument));
        } elseif (is_callable($this->app['error'])) {
            call_user_func_array($this->app['error'], array($argument));
        } else {
            call_user_func_array(array($this, 'displayException'), array($argument));
        }

        return ob_get_clean();
    }

    /**
     * Display the given exception to the user.
     *
     * @param  \Exception  $exception
     * @return void
     */
    protected function displayException($exception)
    {
        $settings = $this->app['settings'];

        if ($settings->get('app.mode', 'production') == 'development' && $settings->get('debug', false) == true ||
            $settings->get('app.mode', 'production') == 'testing' && $settings->get('debug', false) == true) {
            return class_exists('\Whoops\Run') ? $this->app['displayer.whoops']->display($exception) : $this->app['displayer.plain']->display($exception);
        } else {
            return $this->noException($exception);
        }
    }

    /**
     * [noException description]
     * @param  [type] $exception [description]
     * @return [type]            [description]
     */
    protected function noException($exception)
    {
        //Log error
        if (class_exists('\Monolog\Logger')) {
            //$this->app['logger']->addRecord('error', $exception);
        }

        //Set error status
        $this->app['response']->setStatus(503);
        $title = '503 Error';
        $header = 'Egad!';
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
        $footer = 'Copyright &copy; ' . date('Y') .  $this->app['settings']->get('app.footer', 'narrowspark');

        return $this->app['displayer.plain']->decorate($title, $header, $content, $footer, $this->app['displayer.plain']->getStylesheet('pageNotFound'));
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int   $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE));
    }

    /**
     * Determine if the given handler handles this exception.
     *
     * @param  Closure    $handler
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
     * @param  ReflectionFunction  $reflection
     * @param  \Exception  $exception
     * @return bool
     */
    protected function hints(\ReflectionFunction $reflection, $exception)
    {
        $parameters = $reflection->getParameters();

        $expected = $parameters[0];

        return ! $expected->getClass() || $expected->getClass()->isInstance($exception);
    }

    /**
     * Generate brainwave template markup
     *
     * This method accepts a title, header, content, footer and css to generate an HTML document layout.
     *
     * @param  string $title   The title of the HTML template
     * @param  string $header  The header title of the HTML template
     * @param  string $content The body content of the HTML template
     * @param  string $footer  The footer of the HTML template
     * @param  string $css     The css of the HTML template
     * @return string
     */
    public function decorate($title, $header, $content, $footer, $css = '', $js = '')
    {
        return $this->app['displayer.plain']->decorate($title, $header, $content, $footer, $css, $js);
    }

    /**
    * [getStylesheet description]
    * @return [type] [description]
    */
    public function getStylesheet($mode = 'exception')
    {
        return $this->app['displayer.plain']->getStylesheet($mode);
    }

   /**
    * Description
    * @param type plainDisplayer $displayer
    * @return type
    */
    protected function plainDisplayer(plainDisplayer $displayer)
    {
        $this->app['displayer.plain'] = $displayer;
        return $this;
    }

    /**
    * Description
    * @param type plainDisplayer $displayer
    * @return type
    */
    protected function whoopsDisplayer(WhoopsDisplayer $displayer)
    {
        $this->app['displayer.whoops'] = $displayer;
        return $this;
    }
}
