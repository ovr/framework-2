<?php
namespace Brainwave\Contracts\View;

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

use \Brainwave\Contracts\Collection\Collection as CollectionContract

/**
 * Factory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Factory extends CollectionContract
{
    /**
     * Add a piece of data to the view.
     *
     * @param  string|array  $key
     * @param  mixed   $value
     * @return \Brainwave\View\ViewFactory
     */
    public function with($key, $value = null);

    /**
     * Get a piece of data from the view.
     *
     * @return mixed
     */
    public function __get($key);

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value);

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key);

    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string  $key
     * @return bool
     */
    public function __unset($key);

    /**
     * Dynamically bind parameters to the view.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return \Brainwave\View\ViewFactory
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters);

    /**
     * Gets a variable.
     *
     * @return array
     */
    public function gatherData();

    /**
     * Assign a variable to the template.
     *
     * @param mixed $name
     * @param mixed $data the data
     * @return self
     */
    public function share($name, $data = null);
}
