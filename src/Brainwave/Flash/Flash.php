<?php
namespace Brainwave\Flash;

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

use \Brainwave\Flash\Interfaces\FlashInterface;
use \Brainwave\Session\Interfaces\SessionInterface;

/**
 * Flash
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Flash implements FlashInterface
{
    /**
     * The flash session storage key
     * @var string
     */
    protected $key;

    /**
     * Flash segment
     * @var SegmentFactory
     */
    protected $segment;

    /**
     * The session object
     * @var Session
     */
    protected $session;

    /**
     * The flash messages
     * @var array
     */
    protected $messages;

    /**
     * Constructor
     * @param  Session $session
     * @param  string        $key     The flash session storage key
     * @api
     */
    public function __construct(SessionInterface $session, $key = 'flash')
    {
        $this->session = $session;
        $this->segment = $this->session->newSegment('\Brainwave\Flash\Flash', $key);
        $this->key = $key;
        $this->messages = array(
            'prev' => $this->segment->get($key, array()),
            'next' => array(),
            'now' => array()
        );
    }

    /**
     * Set flash message for next request
     * @param  string $key   The flash message key
     * @param  mixed  $value The flash message value
     * @api
     */
    public function next($key, $value)
    {
        $this->messages['next'][(string)$key] = $value;
    }

    /**
     * Set flash message for current request
     * @param  string $key   The flash message key
     * @param  mixed  $value The flash message value
     * @api
     */
    public function now($key, $value)
    {
        $this->messages['now'][(string)$key] = $value;
    }

    /**
     * Persist flash messages from previous request to the next request
     * @api
     */
    public function keep()
    {
        foreach ($this->messages['prev'] as $key => $val) {
            $this->next($key, $val);
        }
    }

    /**
     * Save flash messages to session
     */
    public function save()
    {
        $this->segment->set($this->key, $this->messages['next']);
    }

    /**
     * Return flash messages to be shown for the current request
     * @return array
     * @api
     */
    public function getMessages()
    {
        return array_merge($this->messages['prev'], $this->messages['now']);
    }

    /**
     * Offset exists
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $messages = $this->getMessages();

        return isset($messages[$offset]);
    }

    /**
     * Offset get
     * @param  mixed      $offset
     * @return mixed|null The value at specified offset, or null
     */
    public function offsetGet($offset)
    {
        $messages = $this->getMessages();

        return isset($messages[$offset]) ? $messages[$offset] : null;
    }

    /**
     * Offset set
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->now($offset, $value);
    }

    /**
     * Offset unset
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->messages['prev'][$offset], $this->messages['now'][$offset]);
    }

    /**
     * Get iterator
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getMessages());
    }

    /**
     * Count
     * @return int
     */
    public function count()
    {
        return count($this->getMessages());
    }
}
