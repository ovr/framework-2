<?php
namespace Brainwave\Exception\Displayer;

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

use \Whoops\Run;
use \Brainwave\Workbench\Workbench;
use \Monolog\Logger as MonologLogger;
use \Whoops\Handler\PlainTextHandler;
use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;
use \Brainwave\Exception\Interfaces\ExceptionDisplayerInterface;

/**
 * WhoopsDisplayer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class WhoopsDisplayer implements ExceptionDisplayerInterface
{
    /**
     * [$app description]
     * @var [type]
     */
    protected $app;

    /**
     * @return WhoopsDisplayer
     */
    public function __construct(Workbench $app, $appharset)
    {
        $this->app = $app;
        $app['whoopsEditor'] = 'sublime';

        if (class_exists('\Monolog\Logger')) {
            $app['monolog'] = new MonologLogger('BrainwaveLogger');
        }

        $this->setWhoops(new Run);

        $this->setJsonHanlder(new JsonResponseHandler());
        $app['whoops.jsonHandler']->onlyForAjaxRequests(true);

        $this->setPlainTextHandler(new PlainTextHandler($app['monolog']));

        // There's only ever going to be one error page...right?
        $this->setPageHandler(new PrettyPageHandler);
        $app['whoops.pageHandler']->setResourcesPath(dirname(__FILE__).DS.'..'.DS.'WhoopsResources');

        // Retrieves info on the Brainwave environment and ships it off
        // to the PrettyPageHandler's data tables:
        // This works by adding a new handler to the stack that runs
        // before the error page, retrieving the shared page handler
        // instance, and working with it to add new data tables
        try {
            $request = $app['request'];
        } catch (\RuntimeException $e) {
            // This error occurred too early in the application's life
            // and the request instance is not yet available.
            return;
        }

        $app['whoops.pageHandler']->setPageTitle("We're all going to be fired!");
        /*TODO set all parameter*/
        $app['whoops.pageHandler']->addDataTable('Brainwave Application', [
            'Charset'          => $request->getContentCharset(),
            'Locale'           => $request->getContentCharset() ?: '<none>',
            //'Route Name'       => $app['route']->getName() ?: '<none>',
            //'Route Pattern'    => $app['route']->getPattern() ?: '<none>',
            //'Route Middleware' => $app['route']->getMiddleware() ?: '<none>',
            'Application Class'=> get_class($app)
        ]);

        $app['whoops.pageHandler']->addDataTable('Brainwave Application (Request)', [
            'Base URL'    => $request->getUrl(),
            'URI'         => $request->getScriptName(),
            'Request URI' => $request->getPathInfo(),
            'Path'        => $request->getPath(),
            'Query String'=> $request->params() ?: '<none>',
            'HTTP Method' => $request->getMethod(),
            'Script Name' => $request->getScriptName(),
            'Scheme'      => $request->getScheme(),
            'Port'        => $request->getPort(),
            'Protocol'    => $request->getProtocolVersion(),
            'Host'        => $request->getHost(),
        ]);

        $app['whoops'] = function () use ($app) {
            // Open with editor if editor is set
            $app['whoops.pageHandler']->setEditor($this->getWhoopsEditor());

            $app['whoops.run']->pushHandler($app['whoops.pageHandler']);
            $app['whoops.run']->pushHandler($app['whoops.jsonHandler']);
            $app['whoops.run']->pushHandler($app['whoops.plainTextHandler']);
            $app['whoops.run']->pushHandler($app['whoops.pageHandler']);
            return $app['whoops.run'];
        };
    }

    public function display($exception)
    {
        if ($exception instanceof \Exception) {
            $whoops = $this->app['whoops']->handleException($exception);
        } elseif ($exception instanceof \ErrorException) {
            $whoops = $this->app['whoops']->handleError($exception);
        }

        return $whoops;
    }

    /**
     * [setWhoopsEditor description]
     * @param [type] $editor [description]
     */
    public function setWhoopsEditor($editor)
    {
        $this->app['whoopsEditor'] = $editor;
        return $this;
    }

    /**
     * [getWhoopsEditor description]
     * @return [type] [description]
     */
    protected function getWhoopsEditor()
    {
        return $this->app['whoopsEditor'];
    }

    /**
    * [FunctionName description]
    * @param PrettyPageHandler $pageHandler [description]
    */
    protected function setPageHandler(PrettyPageHandler $pageHandler)
    {
        $this->app['whoops.pageHandler'] = $pageHandler;
        return $this;
    }

   /**
    * [FunctionName description]
    */
    protected function setJsonHanlder(JsonResponseHandler $jsonHandler)
    {
        $this->app['whoops.jsonHandler'] = $jsonHandler;
        return $this;
    }

   /**
    * Description
    * @param type PlainTextHandler $text
    * @return WhoopsDisplayer
    */
    protected function setPlainTextHandler(PlainTextHandler $text)
    {
        $this->app['whoops.plainTextHandler'] = $text;
        return $this;
    }

  /**
    * [whoops description]
    * @param  Run    $run [description]
    * @return [type]      [description]
    */
    protected function setWhoops(Run $run)
    {
        $this->app['whoops.run'] = $run;
        return $this;
    }
}
