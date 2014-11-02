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
use \Pimple\Container;
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
     * Container
     *
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * Determine if the error provider should return JSON.
     *
     * @var bool
     */
    protected $console;

    /**
     * 
     *
     * @param Container $app \Pimple\Container
     * @param string    $charset language
     * @param boolen    $console
     * @return WhoopsDisplayer
     */
    public function __construct(Container $app, $charset, $console)
    {
        $this->app = $app;
        $this->console = $console;
    }

    /**
     * Show Exception
     *
     * @param  [type] $exception
     * @return void
     */
    public function display($exception)
    {
        if ($exception instanceof \Exception) {
            $whoops = $this->app['whoops']->handleException($exception);
        } elseif ($exception instanceof \ErrorException) {
            $whoops = $this->app['whoops']->handleError($exception);
        }

        return $whoops;
    }
}
