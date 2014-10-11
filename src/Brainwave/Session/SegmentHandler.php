<?php
namespace Brainwave\Session;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Session\SessionManager;
use \Brainwave\Session\Interfaces\SegmentHandlerInterface;

/**
 * SegmentFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class SegmentHandler implements SegmentHandlerInterface
{
    /**
     * The session manager.
     * @var Manager
     */
    protected $session;

    /**
     * The segment name.
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     * @param Manager $session The session manager.
     * @param string $name The segment name.
     */
    public function __construct(SessionManager $session, $name)
    {
        $this->session = $session;
        $this->name = $name;
    }

    /**
     * Returns the value of a key in the segment.
     *
     * @param string $key The key in the segment.
     *
     * @return mixed
     */
    public function get($key, $alt = null)
    {
        $this->resumeSession();
        return isset($_SESSION[$this->name][$key])
            ? $_SESSION[$this->name][$key]
            : $alt;
    }

    /**
     * Sets the value of a key in the segment.
     *
     * @param string $key The key to set.
     * @param mixed $val The value to set it to.
     */
    public function set($key, $val)
    {
        $this->resumeOrStartSession();
        $_SESSION[$this->name][$key] = $val;
    }

    /**
     * Clear all data from the segment.
     *
     * @return null
     */
    public function clear()
    {
        if ($this->resumeSession()) {
            $_SESSION[$this->name] = [];
        }
    }

    /**
     * Sets a flash value for the *next* request.
     *
     * @param string $key The key for the flash value.
     * @param mixed $val The flash value itself.
     */
    public function setFlash($key, $val)
    {
        $this->resumeOrStartSession();
        $_SESSION[SessionManager::FLASH_NEXT][$this->name][$key] = $val;
    }

    /**
     * Gets the flash value for a key in the *current* request.
     *
     * @param string $key The key for the flash value.
     *
     * @return mixed The flash value itself.
     */
    public function getFlash($key, $alt = null)
    {
        $this->resumeSession();
        return isset($_SESSION[SessionManager::FLASH_NOW][$this->name][$key])
            ? $_SESSION[SessionManager::FLASH_NOW][$this->name][$key]
            : $alt;
    }

    /**
     * Clears flash values for *only* the next request.
     *
     * @return null
     */
    public function clearFlash()
    {
        if ($this->resumeSession()) {
            $_SESSION[SessionManager::FLASH_NEXT][$this->name] = [];
        }
    }

    /**
     * Gets the flash value for a key in the *next* request.
     *
     * @param string $key The key for the flash value.
     *
     * @return mixed The flash value itself.
     */
    public function getFlashNext($key, $alt = null)
    {
        $this->resumeSession();
        return isset($_SESSION[SessionManager::FLASH_NEXT][$this->name][$key])
            ? $_SESSION[SessionManager::FLASH_NEXT][$this->name][$key]
            : $alt;
    }

    /**
     * Sets a flash value for the *next* request *and* the current one.
     *
     * @param string $key The key for the flash value.
     *
     * @param mixed $val The flash value itself.
     */
    public function setFlashNow($key, $val)
    {
        $this->resumeOrStartSession();
        $_SESSION[SessionManager::FLASH_NOW][$this->name][$key] = $val;
        $_SESSION[SessionManager::FLASH_NEXT][$this->name][$key] = $val;
    }

    /**
     * Clears flash values for *both* the next request *and* the current one.
     *
     * @return null
     */
    public function clearFlashNow()
    {
        if ($this->resumeSession()) {
            $_SESSION[SessionManager::FLASH_NOW][$this->name] = [];
            $_SESSION[SessionManager::FLASH_NEXT][$this->name] = [];
        }
    }

    /**
     * Retains all the current flash values for the next request; values that
     * already exist for the next request take precedence.
     *
     * @return null
     */
    public function keepFlash()
    {
        if ($this->resumeSession()) {
            $_SESSION[SessionManager::FLASH_NEXT][$this->name] = array_merge(
                $_SESSION[SessionManager::FLASH_NEXT][$this->name],
                $_SESSION[SessionManager::FLASH_NOW][$this->name]
            );
        }
    }

    /**
     * Has the segment been loaded with session values?
     *
     * @return bool
     */
    protected function isLoaded()
    {
        return isset($_SESSION[$this->name]);
    }

    /**
     * Loads the segment only if the session has already been started, or if
     * a session is available (in which case it resumes the session first).
     *
     * @return bool
     */
    protected function resumeSession()
    {
        if ($this->isLoaded()) {
            return true;
        }

        if ($this->session->isStarted() || $this->session->resume()) {
            $this->load();
            return true;
        }

        return false;
    }

    /**
     * Sets the segment properties to $_SESSION references.
     *
     * @return null
     */
    protected function load()
    {
        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }

        if (!isset($_SESSION[SessionManager::FLASH_NOW][$this->name])) {
            $_SESSION[SessionManager::FLASH_NOW][$this->name] = [];
        }

        if (!isset($_SESSION[SessionManager::FLASH_NEXT][$this->name])) {
            $_SESSION[SessionManager::FLASH_NEXT][$this->name] = [];
        }
    }

    /**
     * Resumes a previous session, or starts a new one, and loads the segment.
     *
     * @return null
     */
    protected function resumeOrStartSession()
    {
        if (! $this->resumeSession()) {
            $this->session->start();
            $this->load();
        }
    }
}
