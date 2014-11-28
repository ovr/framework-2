<?php
namespace Brainwave\Contracts\Middleware;

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

use \Brainwave\Contracts\Application;

/**
 * Middleware
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Middleware
{
    /**
     * @return void
     */
    public function setApplication(Application $containerlication);

    /**
     * @return \Brainwave\Application\Application
     */
    public function getApplication();

    /**
     * @return void
     */
    public function setNextMiddleware($nextMiddleware);

    /**
     * @return \Brainwave\Middleware\Middleware
     */
    public function getNextMiddleware();

    public function call();
}