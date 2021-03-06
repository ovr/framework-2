<?php
namespace Brainwave\Exception\Interfaces;

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

/**
 * ExceptionDisplayerInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface ExceptionDisplayerInterface
{
    /**
     * @param Container $app
     * @param string    $charset language
     */
    public function __construct(Container $app, $charset, $console);

    /**
     * Display the given exception to the user.
     *
     * @param  \Exception  $exception
     */
    public function display($exception);
}
