<?php
namespace Brainwave\View;

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

use \Pimple\Container;
use \Brainwave\Support\Str;
use \Brainwave\Collection\Collection;
use \Brainwave\View\Engines\EngineResolver;
use \Brainwave\Contracts\View\View as ViewContract;
use \Brainwave\Contracts\Support\Arrayable as ArrayableContracts;

/**
 * View
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class View extends Collection implements ViewContract
{

    /**
     * Dynamically bind parameters to the view.
     *
     * @param  string  $method
     * @param  array   $parameters
     *
     * @return \Brainwave\View\ViewFactory
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }

        throw new \BadMethodCallException("Method [$method] does not exist on view.");
    }

    /**
     * Get a piece of data from the view.
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->viewData[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function __set($key, $value = null)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->viewData[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string $key
     *
     * @return boolean|null
     */
    public function __unset($key)
    {
        unset($this->viewData[$key]);
    }
}
