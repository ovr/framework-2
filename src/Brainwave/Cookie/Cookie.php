<?php
namespace Brainwave\Cookie;

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
use \Brainwave\Contracts\Cookie\Factory as FactoryContract;
use \Brainwave\Contracts\Encrypter\Encrypter as EncrypterContract;

/**
 * Cookie
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Cookie implements FactoryContract
{
    protected $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Create HTTP cookie to be sent with the HTTP response
     *
     * @param  string     $name     The cookie name
     * @param  string     $value    The cookie value
     * @param  int|string $time     The duration of the cookie;
     *                              If integer, should be UNIX timestamp;
     *                              If string, converted to UNIX timestamp with `strtotime`;
     * @param  string     $path     The path on the server in which the cookie will be available on
     * @param  string     $domain   The domain that the cookie is available to
     * @param  bool       $secure   Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection to/from the client
     * @param  bool       $httponly When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public function make(
        $name,
        $value,
        $time = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httponly = null
    ) {
        $settings = [
            'value' => $value,
            'expires' => ($time === null) ? $this->app['settings']->get('cookies::lifetime', '20minutes') : $time,
            'path' => ($path === null) ? $this->app['settings']->get('cookies::path', '/') : $path,
            'domain' => ($domain === null) ? $this->app['settings']->get('cookies::domain', null) : $domain,
            'secure' => ($secure === null) ? $this->app['settings']->get('cookies::secure', false) : $secure,
            'httponly' => ($httponly === null) ? $this->app['settings']->get('cookies::httponly', false) : $httponly
        ];
        $this->app['response']->setCookie($name, $settings);
    }

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @param  string  $name
     * @param  string  $value
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httpOnly
     */
    public function forever($name, $value, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        return $this->set($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
    }

    public function session($value)
    {
        //TODO
    }

    /**
     * Get value of HTTP cookie from the current HTTP request
     *
     * Return the value of a cookie from the current HTTP request,
     * or return NULL if cookie does not exist. Cookies created during
     * the current request will not be available until the next request.
     *
     * @param  string $name The cookie name
     *
     * @return string|null
     */
    public function get($name)
    {
        return $this->app['request']->getCookie($name);
    }

    /**
     * Does this request have a given cookie?
     *
     * @param  string $name
     * @return bool
     * @api
     */
    public function has($name)
    {
        return $this->app['request']->hasCookie($name);
    }

    /**
     * Encrypt cookies
     *
     * @param CryptInterface $crypt
     */
    public function encryptCookies(EncrypterContract $crypt)
    {
        $this->app['request']->encryptCookies($crypt);
    }

    /**
     * Forget HTTP cookie (encrypted or unencrypted)
     *
     * Remove a Cookie from the client. This method will overwrite an existing Cookie
     * with a new, empty, auto-expiring Cookie. This method's arguments must match
     * the original Cookie's respective arguments for the original Cookie to be
     * removed. If any of this method's arguments are omitted or set to NULL, the
     * default Cookie setting values (set during Brainwave::init) will be used instead.
     *
     * @param  string $name     The cookie name
     * @param  string $path     The path on the server in which the cookie will be available on
     * @param  string $domain   The domain that the cookie is available to
     * @param  bool   $secure   Indicates that the cookie should only be transmitted over a secure
     *                          HTTPS connection from the client
     * @param  bool   $httponly When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public function forget(
        $name,
        $path = null,
        $domain = null,
        $secure = null,
        $httponly = null
    ) {
        $settings = [
            'domain' => is_null($domain) ? $this->app['settings']->get('cookies::domain', null) : $domain,
            'path' => is_null($path) ? $this->app['settings']->get('cookies::path', '/') : $path,
            'secure' => is_null($secure) ? $this->app['settings']->get('cookies::secure', false) : $secure,
            'httponly' => is_null($httponly) ?$this->app['settings']->get('cookies::httponly', flase) : $httponly
        ];
        $this->app['response']->removeCookie($name, $settings);
    }
}
