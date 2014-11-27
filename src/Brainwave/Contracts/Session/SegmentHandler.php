<?php
namespace Brainwave\Contracts\Session;

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
 * SegmentFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface SegmentHandler
{
   /**
     * Returns the value of a key in the segment.
     *
     * @param string $key The key in the segment.
     *
     * @return mixed
     */
    public function get($key, $alt = null);

    /**
     * Sets the value of a key in the segment.
     *
     * @param string $key The key to set.
     * @param mixed $val The value to set it to.
     * @return void
     */
    public function set($key, $val);

    /**
     * Clear all data from the segment.
     *
     * @return null
     */
    public function clear();

    /**
     * Sets a read-once flash value on the segment.
     *
     * @param string $key The key for the flash value.
     * @param mixed $val The flash value itself.
     * @return void
     */
    public function setFlash($key, $val);

    /**
     * Reads the flash value for a key, thereby removing it from the session.
     *
     * @param string $key The key for the flash value.
     *
     * @return mixed The flash value itself.
     */
    public function getFlash($key, $alt = null);

    /**
     * Clears all flash values.
     *
     * @return null
     */
    public function clearFlash();
}
