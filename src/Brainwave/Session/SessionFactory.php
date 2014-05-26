<?php
namespace Brainwave\Session;

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

use \Brainwave\Crypt\Crypt;
use \Brainwave\Collection\Collection;
use \Brainwave\Session\SegmentFactory;
use \Brainwave\Session\CsrfToken\CsrfTokenFactory;
use \Brainwave\Session\Interfaces\SessionInterface;
use \Brainwave\Session\Interfaces\SessionHandlerInterface;

/**
 * SessionFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class SessionFactory extends Collection implements SessionInterface
{

    /**
     * The CSRF token for this session.
     *
     * @var CsrfToken
     */
    protected $csrfToken;

    /**
     * A CSRF token factory, for lazy-creating the CSRF token.
     * @var CsrfTokenFactory
     */
    protected $csrfTokenFactory;

    /**
     * Csrf Segment
     * @var SegmentFactory
     */
    protected $csrfSegment;

    /**
     * Session Segment
     * @var SegmentFactory
     */
    protected $sessionSegment;

    /**
     * Incoming cookies from the client, typically a copy of the $_COOKIE
     * superglobal.
     * @var array
     */
    protected $cookies;

    /**
     * Session cookie parameters.
     * @var array
     */
    protected $cookie_params = array();

    /**
     * Reference to custom session handler
     * @var SessionHandlerInterface
     */
    protected $handler;

    /**
     * Constructor
     *
     * @param CsrfTokenFactory A CSRF token factory.
     * @param array $cookies An arry of cookies from the client, typically a
     * copy of $_COOKIE.
     */
    public function __construct(Crypt $crypt, array $cookies = array())
    {
        $this->csrfSegment = $this->newSegment('_csrf');
        $this->sessionSegment = $this->newSegment('_session');
        $this->csrfTokenFactory = $this->csrfTokenFactory(new CsrfTokenFactory($this->csrfSegment, $crypt));
        $this->cookies            = $cookies;
        $this->cookie_params      = session_get_cookie_params();
    }

    /**
     * Gets a new session segment instance by name. Segments with the same
     * name will be different objects but will reference the same $_SESSION
     * values, so it is possible to have two or more objects that share state.
     * For good or bad, this a function of how $_SESSION works.
     *
     * @param string $name The name of the session segment, typically a
     * fully-qualified class name.
     * @return Segment
     */
    public function newSegment($name)
    {
        return $this->segmentFactory(new SegmentFactory($this, $name));
    }

    /**
     * Set session handler
     *
     * By default, this class assumes the use of the native file system session handler
     * for persisting session data. This method allows us to use a custom handler.
     *
     * @param  SessionHandlerInterface $handler A custom session handler
     * @api
     */
    public function setSessionHandler(SessionHandlerInterface $handler = null)
    {
        if ($handler !== null) {
            session_set_save_handler(
                array($handler, 'open'),
                array($handler, 'close'),
                array($handler, 'read'),
                array($handler, 'write'),
                array($handler, 'destroy'),
                array($handler, 'gc')
            );
            $this->handler = $handler;
        }
        return $this;
    }

    /**
     * Get session handler
     * @return void $handler A custom session handler
     */
    public function getSessionHandler()
    {
         return $this->handler;
    }

    /**
     * Tells us if a session is available to be reactivated, but not if it has
     * started yet.
     *
     * @return bool
     */
    public function isAvailable()
    {
        $name = $this->getName();
        return isset($this->cookies[$name]);
    }

    /**
     * Tells us if a session has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        $started = false;
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $started = session_status() === PHP_SESSION_ACTIVE ? true : false;
        } else {
            $started = session_id() === '' ? false : true;
        }

        return $started;
    }

    /**
     * Starts a new session, or resumes an existing one.
     *
     * @return bool
     */
    public function start()
    {
        // Initialize new session if a session is not already started
        if ($this->isStarted() === false) {
            $this->initialize();
        }
    }

    /**
     * Save session data to data source
     * @api
     */
    public function save()
    {
        $this->sessionSegment->set('app.session', $this->all());
    }

    /**
     * Initialize new session
     * @throws \RuntimeException If `session_start()` fails
     */
    public function initialize()
    {
        // Disable PHP cache headers
        $this->getCacheLimiter();

        // Ensure session ID uses valid characters when stored in HTTP cookie
        if (ini_get('session.use_cookies') == true) {
            ini_set('session.hash_bits_per_character', 5);
        }

        // Start session
        if (session_start() === false) {
            throw new \RuntimeException('Cannot start session. Unknown error while invoking `session_start()`.');
        };
    }

    /**
     * Clears all session variables across all segments.
     *
     * @return null
     */
    public function clear()
    {
        return session_unset();
    }

    /**
     * Writes session data from all segments and ends the session.
     *
     * @return null
     */
    public function commit()
    {
        return session_write_close();
    }

    /**
     * Destroys the session entirely.
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->isStarted() === false) {
            $this->start();
        }
        $this->clear();
        return session_destroy();
    }

    /**
     * Returns the CSRF token, creating it if needed (and thereby starting a
     * session).
     *
     * @return CsrfToken
     */
    public function getCsrfToken()
    {
        if (!$this->csrfToken) {
            $this->csrfToken = $this->csrfTokenFactory;
        }

        return $this->csrfToken;
    }

    /**
     * Sets the session cache expire time.
     *
     * @param int $expire The expiration time in seconds.
     * @return int
     * @see session_cache_expire()
     */
    public function setCacheExpire($expire)
    {
        return session_cache_expire($expire);
    }

    /**
     *
     * Gets the session cache expire time.
     * @return int The cache expiration time in seconds.
     * @see session_cache_expire()
     */
    public function getCacheExpire()
    {
        return session_cache_expire();
    }

    /**
     * Sets the session cache limiter value.
     *
     * @param string $limiter The limiter value.
     * @return string
     * @see session_cache_limiter()
     */
    public function setCacheLimiter($limiter = false)
    {
        return session_cache_limiter($limiter);
    }

    /**
     * Gets the session cache limiter value.
     *
     * @return string The limiter value.
     * @see session_cache_limiter()
     */
    public function getCacheLimiter()
    {
        return session_cache_limiter();
    }

    /**
     * Gets the current session id.
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Regenerates and replaces the current session id; also regenerates the
     * CSRF token value if one exists.
     *
     * @return bool True is regeneration worked, false if not.
     */
    public function regenerateId()
    {
        $result = session_regenerate_id(true);
        if ($result && $this->csrfToken) {
            $this->csrfToken->regenerateValue();
        }
        return $result;
    }

    /**
     * Sets the current session name.
     *
     * @param string $name The session name to use.
     * @return string
     * @see session_name()
     */
    public function setName($name)
    {
        return session_name($name);
    }

    /**
     * Returns the current session name.
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Sets the session save path.
     *
     * @param string $path The new save path.
     * @return string
     * @see session_save_path()
     */
    public function setSavePath($path)
    {
        return session_save_path($path);
        ini_set('session.gc_probability', 1);
    }

    /**
     * Gets the session save path.
     *
     * @return string
     * @see session_save_path()
     */
    public function getSavePath()
    {
        return session_save_path();
    }

    /**
     * Returns the current session status:
     *
     * - `PHP_SESSION_DISABLED` if sessions are disabled.
     * - `PHP_SESSION_NONE` if sessions are enabled, but none exists.
     * - `PHP_SESSION_ACTIVE` if sessions are enabled, and one exists.
     *
     * @return int
     * @see session_status()
     */
    public function getStatus()
    {
        return session_status();
    }

    /**
     * segmentFactory
     *
     * @param  type SegmentFactory $segment
     * @return void
     */
    public function segmentFactory(SegmentFactory $segment)
    {
        return $segment;
    }

    /**
     * csrfTokenFactory
     *
     * @param type CsrfTokenFactory $csrfToken
     * @return void
     */
    public function csrfTokenFactory(CsrfTokenFactory $csrfToken)
    {
        return $csrfToken;
    }
}
