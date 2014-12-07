<?php
namespace Brainwave\Contracts\Application;

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

/**
 * Environment
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Environment
{
    /**
     * Detect the application's current environment.
     *
     * @param \Closure   $callback
     * @param array|null $consoleArgs
     *
     * @return string
     */
    public function detect(\Closure $callback, $consoleArgs = null);

     /**
     * Returns true when the runtime used is HHVM or
     * the runtime used is PHP + Xdebug.
     *
     * @return boolean
     */
    public function canCollectCodeCoverage();

    /**
     * Returns the running php/HHVM version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return boolean
     */
    public function hasXdebug();

    /**
     * Returns true when the runtime used is HHVM.
     *
     * @return boolean
     */
    public function isHHVM();

    /**
     * Returns true when the runtime used is PHP.
     *
     * @return boolean
     */
    public function isPHP();

    /**
     * Returns true when the runtime used is Console.
     *
     * @return boolean
     */
    public function runningInConsole();
}
