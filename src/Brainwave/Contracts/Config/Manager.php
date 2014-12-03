<?php
namespace Brainwave\Contracts\Config;

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
 * Manager
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Manager extends \ArrayAccess
{
    /**
     * Set Brainwave's defaults using the handler
     *
     * @param  array $values
     * @return void
     */
    public function setArray(array $values);

    /**
     * Load the given configuration group.
     *
     * @param string $file
     * @param string $namespace
     * @param string $environment
     * @param string $group
     *
     * @return void
     */

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key);

    /**
     * Get a value
     *
     * @param string $key
     *
     * @return mixed The value of a setting
     */
    public function get($key, $default);

    /**
     * Set a value
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value);
}
