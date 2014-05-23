<?php namespace Brainwave\Session;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Collection\Collection;
use \Brainwave\Session\SessionFactory;
use \Brainwave\Collection\Interfaces\CollectionInterface;
use \Brainwave\Session\Interfaces\SegmentFactoryInterface;

/**
* 
*/
class SegmentFactory extends Collection implements SegmentFactoryInterface, CollectionInterface
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
     * The data in the segment is a reference to a $_SESSION key.
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     * @param Manager $session The session manager.
     * @param string $name The segment name.
     */
    public function __construct(SessionFactory $session, $name)
    {
        $this->session = $session;
        $this->name = $name;
    }

    /**
     * Checks to see if the segment data has been loaded; if not, checks to
     * see if a session has already been started or is available, and then
     * loads the segment data from the session.
     * @return bool
     */
    protected function isLoaded()
    {
        if ($this->data !== null) {
            return true;
        }

        if ($this->session->isStarted() || $this->session->isAvailable()) {
            $this->load();
            return true;
        }

        return false;
    }

    /**
     * Forces a session start (or reactivation) and loads the segment data
     * from the session.
     * @return void
     */
    protected function load()
    {
        // is data already loaded?
        if ($this->data !== null) {
            // no need to re-load
            return;
        }

        // if the session is not started, start it
        if (! $this->session->isStarted()) {
            $this->session->start();
        }

        // if we don't have a $_SESSION key for the segment, create one
        if (! isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }

        // set $data as a reference to the $_SESSION key
        $this->data = &$_SESSION[$this->name];
    }

    /**
     * Returns the value of a key in the segment.
     * @param string $key The key in the segment.
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->isLoaded()) {
            return parent::get($key, $default = null);
        }
    }

    /**
     * Sets the value of a key in the segment.
     * @param string $key The key to set.
     * @param mixed $value The value to set it to.
     */
    public function set($key, $value)
    {
        $this->load();
        parent::set($key, $value);
    }

    /**
     * Check whether a key is set in the segment.
     * @param string $key The key to check.
     * @return bool
     */
    public function __isset($key)
    {
        if ($this->isLoaded()) {
            return isset($this->data[$key]);
        }
        return false;
    }

    /**
     * Unsets a key in the segment.
     * @param string $key The key to unset.
     * @return void
     */
    public function remove($key)
    {
        if ($this->isLoaded()) {
            parent::remove($key);
        }
    }

    /**
     * Clear all data from the segment.
     * @return void
     */
    public function clear()
    {
        if ($this->isLoaded()) {
            parent::clear();
        }
    }

    /**
     * Gets the segment name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a read-once flash value on the segment.
     * @param string $key The key for the flash value.
     * @param mixed $val The flash value itself.
     */
    public function setFlash($key, $val)
    {
        $this->load();
        $this->data['_flash'][$key] = $val;
    }

    /**
     * Reads the flash value for a key, thereby removing it from the session.
     * @param string $key The key for the flash value.
     * @return mixed The flash value itself.
     */
    public function getFlash($key)
    {
        if ($this->isLoaded() && isset($this->data['_flash'][$key])) {
            $val = $this->data['_flash'][$key];
            unset($this->data['_flash'][$key]);
            return $val;
        }
    }

    /**
     * Checks whether a flash key is set, without reading it.
     * @param string $key The flash key to check.
     * @return bool True if it is set, false if not.
     */
    public function hasFlash($key)
    {
        if ($this->isLoaded()) {
            return isset($this->data['_flash'][$key]);
        }
        return false;
    }

    /**
     * Clears all flash values.
     * @return void
     */
    public function clearFlash()
    {
        if ($this->isLoaded()) {
            unset($this->data['_flash']);
        }
    }
}
