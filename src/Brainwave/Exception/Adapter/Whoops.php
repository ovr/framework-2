<?php
namespace Brainwave\Exception\Adapter;

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
use Brainwave\Contracts\Exception\Adapter as ExceptionAdapter;

/**
 * WhoopsDisplayer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Whoops implements ExceptionAdapter
{
    /**
     * Container
     *
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * Whoops displayer
     *
     * @param Container $container
     *
     * @return WhoopsDisplayer
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Show Exception
     *
     * @param \Exception|\ErrorException $exception
     *
     * @return void
     */
    public function display($exception)
    {
        if ($exception instanceof \Exception) {
            return $this->container['whoops']->handleException($exception);
        } elseif ($exception instanceof \ErrorException) {
            return $this->container['whoops']->handleError($exception);
        }
    }
}
