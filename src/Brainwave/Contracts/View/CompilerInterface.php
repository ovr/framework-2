<?php
namespace Brainwave\View\Compilers\Interfaces;

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

/**
 * CompilerInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface CompilerInterface
{
    /**
     * Get the path to the compiled version of a view.
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($path);

    /**
     * Determine if the given view is expired.
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path);

    /**
     * Compile the view at the given path.
     * @param  string  $path
     * @return void
     */
    public function compile($path);
}
