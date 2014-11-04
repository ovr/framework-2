<?php namespace Brainwave\Config\Interfaces;

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
 * Loader Interface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface LoaderInterface
{
    /**
     * Load the given configuration group.
     * @param  string  $file
     * @param  string  $namespace
     * @param  string  $environment
     * @param  string  $group
     * @return array
     */
    public function load($file, $group = null, $environment = null, $namespace = null);

    /**
     * Determine if the given file exists.
     * @param  string  $file
     * @param  string  $namespace
     * @param  string  $environment
     * @param  string  $group
     * @return bool|array
     */
    public function exists($file, $group = null, $environment = null, $namespace = null);

    /**
     * Apply any cascades to an array of package options.
     *
     * @param  string  $env
     * @param  string  $package
     * @param  string  $group
     * @param  array   $items
     * @return array
     */
    public function cascadePackage($file, $package, $group, $env, $items, $namespace = 'packages');
}
