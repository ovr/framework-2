<?php
namespace Brainwave\Session;

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

use \SessionHandlerInterface;

/**
 * Flash
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Flash
{
    /**
     * Session key for the "next" flash values.
     *
     * @param string
     */
    protected $flashNext = 'Brainwave\Session\Flash\Next';

     /**
      * Session key for the "current" flash values.
      *
      * @param string
      */
    protected $flashNow = 'Brainwave\Session\Flash\Now';

    /**
     * Handler instance.
     *
     * @var \SessionHandlerInterface
     */
    protected $manager;

    /**
     * [__construct description]
     *
     * @param SessionHandlerInterface $manager
     */
    public function __construct(SessionHandlerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Moves the "next" flash values to the "now" values, thereby clearing the
     * "next" values
     *
     * @return null
     */
    protected function moveFlash()
    {
        if (!isset($_SESSION[self::FLASH_NEXT])) {
            $_SESSION[self::FLASH_NEXT] = [];
        }

        $_SESSION[self::FLASH_NOW]  = $_SESSION[self::FLASH_NEXT];
        $_SESSION[self::FLASH_NEXT] = [];
    }

    /**
     * Sets a flash value for the *next* request.
     *
     * @param string $key The key for the flash value.
     * @param mixed  $val The flash value itself.
     */
    public function set($key, $val)
    {
        $this->resumeOrStartSession();
        $_SESSION[self::FLASH_NEXT][$this->name][$key] = $val;
    }

    /**
     * Gets the flash value for a key in the *current* request.
     *
     * @param string $key The key for the flash value.
     * @param mixed $alt An alternative value to return if the key is not set.
     *
     * @return mixed The flash value itself.
     */
    public function get($key, $alt = null)
    {
        $this->resumeSession();
        return isset($_SESSION[self::FLASH_NOW][$this->name][$key])
             ? $_SESSION[self::FLASH_NOW][$this->name][$key]
             : $alt;
    }

    /**
     * Clears flash values for *only* the next request.
     *
     * @return null
     */
    public function clear()
    {
        if ($this->resumeSession()) {
            $_SESSION[self::FLASH_NEXT][$this->name] = [];
        }
    }

    /**
     * Gets the flash value for a key in the *next* request.
     *
     * @param string $key The key for the flash value.
     * @param mixed $alt An alternative value to return if the key is not set.
     *
     * @return mixed The flash value itself.
     */
    public function getNext($key, $alt = null)
    {
        $this->resumeSession();

        if (isset($_SESSION[self::FLASH_NEXT][$this->name][$key])) {
            return $_SESSION[self::FLASH_NEXT][$this->name][$key];
        }

        return $alt;
    }

    /**
     * Sets a flash value for the *next* request *and* the current one.
     *
     * @param string $key The key for the flash value.
     * @param mixed $val The flash value itself.
     */
    public function setNow($key, $val)
    {
        $this->resumeOrStartSession();

        $_SESSION[self::FLASH_NOW][$this->name][$key] = $val;
        $_SESSION[self::FLASH_NEXT][$this->name][$key] = $val;
    }

    /**
     * Clears flash values for *both* the next request *and* the current one.
     *
     * @return null
     */
    public function clearNow()
    {
        if ($this->resumeSession()) {
            $_SESSION[self::FLASH_NOW][$this->name] = [];
            $_SESSION[self::FLASH_NEXT][$this->name] = [];
        }
    }

    /**
     * Retains all the current flash values for the next request; values that
     * already exist for the next request take precedence.
     *
     * @return null
     */
    public function keep()
    {
        if ($this->resumeSession()) {
            $_SESSION[self::FLASH_NEXT][$this->name] = array_merge(
                $_SESSION[self::FLASH_NEXT][$this->name],
                $_SESSION[self::FLASH_NOW][$this->name]
            );
        }
    }
}
