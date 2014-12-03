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

use Brainwave\Session\CsrfToken\CsrfTokenFactory;
use Brainwave\Session\Factory;

/**
 * Manger
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Manager implements \SessionHandlerInterface
{
    /**
     * A session segment factory.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * The CSRF token for this session.
     *
     * @var CsrfToken
     */
    protected $csrfToken;

    /**
     * A CSRF token factory, for lazy-creating the CSRF token.
     *
     * @var CsrfTokenFactory
     */
    protected $csrfTokenFactory;

    /**
     * Incoming cookies from the client, typically a copy of the $_COOKIE
     * superglobal.
     *
     * @var array
     */
    protected $cookies;

    /**
     * A callable to invoke when deleting the session cookie.
     *
     * @see setDeleteCookie()
     */
    protected $deleteCookie;

    /**
     * Session cookie parameters.
     *
     * @var array
     */
    protected $cookieParams = [];

    /**
     * Constructor
     *
     * @param factory $factory A session segment factory.
     * @param CsrfTokenFactory A CSRF token factory.
     * @param array   $cookies An arry of cookies from the client, typically a
     *                         copy of $_COOKIE.
     */
    public function __construct(
        Factory $factory,
        CsrfTokenFactory $csrfTokenFactory,
        array $cookies = [],
        $delete_cookie = null
    ) {
        $this->factory          = $factory;
        $this->csrfTokenFactory = $csrfTokenFactory;
        $this->cookies          = $cookies;

        $this->setDeleteCookie($delete_cookie);

        $this->cookieParams = $this->call('session_get_cookie_params');
    }

    /**
     * Sets the delete-cookie callable.
     *
     * @param callable $deleteCookie The callable to invoke when deleting the
     *                               session cookie.
     */
    public function setDeleteCookie($deleteCookie)
    {
        $this->deleteCookie = $deleteCookie;

        if (!$this->deleteCookie) {
            $phpfunc = $this->phpfunc;
            $this->deleteCookie = function (
                $name,
                $params
            ) use ($phpfunc) {
                $phpfunc->setcookie(
                    $name,
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain']
                );
            };
        }
    }

    /**
     * Gets a new session segment instance by name. Segments with the same
     * name will be different objects but will reference the same $_SESSION
     * values, so it is possible to have two or more objects that share state.
     * For good or bad, this a function of how $_SESSION works.
     *
     * @param string $name The name of the session segment, typically a
     *                     fully-qualified class name.
     *
     * @return Factory
     */
    public function getSegment($name)
    {
        return $this->factory->newInstance($this, $name);
    }

    /**
     * Is a session available to be resumed?
     *
     * @return bool
     */
    public function isResumable()
    {
        $name = $this->getName();

        return isset($this->cookies[$name]);
    }

    /**
     * Is the session already started?
     *
     * @return bool
     */
    public function isStarted()
    {
        if ($this->phpfunc->function_exists('session_status')) {
            $started = $this->phpfunc->session_status() === PHP_SESSION_ACTIVE;
        } else {
            $started = $this->sessionStatus();
        }

        return $started;
    }

    /**
     * Returns the session status.
     *
     * PHP 5.3 implementation of session_status for only active/none.
     * Relies on the fact that ini setting 'session.use_trans_sid' cannot be
     * changed when a session is active.
     *
     * PHP ini_set() raises a warning when we attempt to change this setting
     * and session is active. note that the attempted change is to the
     * pre-existing value, so nothing will actually change on success.
     *
     * @return boolean
     */
    protected function sessionStatus()
    {
        $setting = 'session.use_trans_sid';
        $current = $this->phpfunc->ini_get($setting);
        $level   = $this->phpfunc->error_reporting(0);
        $result  = $this->phpfunc->ini_set($setting, $current);
        $this->phpfunc->error_reporting($level);

        return $result !== $current;
    }

    /**
     * Starts a new or existing session.
     *
     * @return bool
     */
    public function start()
    {
        $result = $this->phpfunc->session_start();
        if ($result) {
            $this->moveFlash();
        }

        return $result;
    }

    /**
     * Resumes a session, but does not start a new one if there is no
     * existing one.
     *
     * @return bool
     */
    public function resume()
    {
        if ($this->isStarted()) {
            return true;
        }

        if ($this->isResumable()) {
            return $this->start();
        }

        return false;
    }

    /**
     * Clears all session variables across all segments.
     *
     * @return null
     */
    public function clear()
    {
        if ($this->resumeSession()) {
            $_SESSION[$this->name] = array();
        }
    }

    /**
     * Writes session data from all segments and ends the session.
     *
     * @return null
     */
    public function commit()
    {
        return $this->phpfunc->session_write_close();
    }

    /**
     * Destroys the session entirely.
     *
     * @return bool
     *
     * @see http://php.net/manual/en/function.session-destroy.php
     */
    public function destroy()
    {
        if (! $this->isStarted()) {
            $this->start();
        }

        $name = $this->getName();
        $params = $this->getCookieParams();

        $this->clear();

        $destroyed = $this->phpfunc->session_destroy();

        if ($destroyed) {
            call_user_func($this->deleteCookie, $name, $params);
        }

        return $destroyed;
    }

    /**
     * TODO
     * [gc description]
     * @param  [type] $maxlifetime [description]
     * @return [type] [description]
     */
    public function gc($maxlifetime)
    {
        # code...
    }

    /**
     * TODO
     * [gc description]
     * @return [type] [description]
     */
    public function open($save_path, $name)
    {
        # code...
    }

    /**
     * TODO
     * [gc description]
     * @return [type] [description]
     */
    public function read($session_id)
    {
    }

    /**
     * TODO
     * [gc description]
     * @return [type] [description]
     */
    public function write($session_id, $session_data)
    {
    }

    /**
     * Sets the session cache expire time.
     *
     * @param  int $expire The expiration time in seconds.
     * @return int
     *
     * @see session_cache_expire()
     */
    public function setCacheExpire($expire)
    {
        return $this->phpfunc->session_cache_expire($expire);
    }

    /**
     * Gets the session cache expire time.
     *
     * @return int The cache expiration time in seconds.
     *
     * @see session_cache_expire()
     */
    public function getCacheExpire()
    {
        return $this->phpfunc->session_cache_expire();
    }

    /**
     * Sets the session cache limiter value.
     *
     * @param string $limiter The limiter value.
     *
     * @return string
     *
     * @see session_cache_limiter()
     */
    public function setCacheLimiter($limiter)
    {
        return $this->phpfunc->session_cache_limiter($limiter);
    }

    /**
     * Gets the session cache limiter value.
     *
     * @return string The limiter value.
     *
     * @see session_cache_limiter()
     */
    public function getCacheLimiter()
    {
        return $this->phpfunc->session_cache_limiter();
    }

    /**
     * Sets the session cookie params.
     * Param array keys are:
     *
     * 'lifetime' : Lifetime of the session cookie, defined in seconds.
     *
     * 'path' : Path on the domain where the cookie will work.
     *  Use a single slash ('/') for all paths on the domain.
     *
     * 'domain' : Cookie domain, for example 'www.php.net'.
     *  To make cookies visible on all subdomains then the domain must be
     *  prefixed with a dot like '.php.net'.
     *
     * 'secure' : If TRUE cookie will only be sent over secure connections.
     *
     * 'httponly' : If set to TRUE then PHP will attempt to send the httponly
     *  flag when setting the session cookie.
     *
     * @param  array $params The array of session cookie param keys and values.
     * @return null
     *
     * @see session_set_cookie_params()
     */
    public function setCookieParams(array $params)
    {
        $this->cookieParams = array_merge($this->cookieParams, $params);
        $this->phpfunc->session_set_cookie_params(
            $this->cookieParams['lifetime'],
            $this->cookieParams['path'],
            $this->cookieParams['domain'],
            $this->cookieParams['secure'],
            $this->cookieParams['httponly']
        );
    }

    /**
     * Gets the session cookie params.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Gets the current session id.
     *
     * @return string
     *
     */
    public function getId()
    {
        return $this->phpfunc->session_id();
    }

    /**
     * Sets the current session name.
     *
     * @param  string $name The session name to use.
     * @return string
     *
     * @see session_name()
     */
    public function setName($name)
    {
        return $this->phpfunc->session_name($name);
    }

    /**
     * Returns the current session name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->phpfunc->session_name();
    }

    /**
     * Sets the session save path.
     *
     * @param  string $path The new save path.
     * @return string
     *
     * @see session_save_path()
     */
    public function setSavePath($path)
    {
        return $this->phpfunc->session_save_path($path);
    }

    /**
     * Gets the session save path.
     *
     * @return string
     *
     * @see session_save_path()
     */
    public function getSavePath()
    {
        return $this->phpfunc->session_save_path();
    }

    /**
     * Call to intercept any function pass to it.
     *
     * @param string $func The function to call.
     * @param array  $args Arguments passed to the function.
     *
     * @return mixed The result of the function call.
     */
    public function call($func, array $args = [])
    {
        return call_user_func_array($func, $args);
    }
}
