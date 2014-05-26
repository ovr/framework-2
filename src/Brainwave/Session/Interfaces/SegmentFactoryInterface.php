<?php
namespace Brainwave\Session\Interfaces;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

/**
 * SegmentFactoryInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface SegmentFactoryInterface
{
    /**
     * Returns the value of a key in the segment.
     * @param string $key The key in the segment.
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Sets the value of a key in the segment.
     * @param string $key The key to set.
     * @param mixed $val The value to set it to.
     */
    public function set($key, $val);

    /**
     * Check whether a key is set in the segment.
     * @param string $key The key to check.
     * @return bool
     */
    public function __isset($key);

    /**
     * Unsets a key in the segment.
     * @param string $key The key to unset.
     * @return void
     */
    public function remove($key);

    /**
     * Clear all data from the segment.
     * @return void
     */
    public function clear();

    /**
     * Gets the segment name.
     * @return string
     */
    public function getName();

    /**
     * Sets a read-once flash value on the segment.
     * @param string $key The key for the flash value.
     * @param mixed $val The flash value itself.
     */
    public function setFlash($key, $val);

    /**
     * Reads the flash value for a key, thereby removing it from the session.
     * @param string $key The key for the flash value.
     * @return mixed The flash value itself.
     */
    public function getFlash($key);

    /**
     * Checks whether a flash key is set, without reading it.
     * @param string $key The flash key to check.
     * @return bool True if it is set, false if not.
     */
    public function hasFlash($key);

    /**
     * Clears all flash values.
     * @return void
     */
    public function clearFlash();
}
