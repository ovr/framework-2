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
    protected $container;

    public function __construct(Container $container)
    {
        $this->container= $container;
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
            'expires' => ($time === null) ? $this->container['settings']['cookies::lifetime'] : $time,
            'path' => ($path === null) ? $this->container['settings']['cookies::path'] : $path,
            'domain' => ($domain === null) ? $this->container['settings']['cookies::domain'] : $domain,
            'secure' => ($secure === null) ? $this->container['settings']['cookies::secure'] : $secure,
            'httponly' => ($httponly === null) ? $this->container['settings']['cookies::httponly'] : $httponly
        ];

        $this->container['response']->setCookie($name, $settings);
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
        return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly);
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
        return $this->container['request']->getCookie($name);
    }

    /**
     * Does this request have a given cookie?
     *
     * @param  string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->container['request']->hasCookie($name);
    }

    /**
     * Encrypt cookies
     *
     * @param EncrypterContract $crypt
     */
    public function encryptCookies(EncrypterContract $crypt)
    {
        $this->container['request']->encryptCookies($crypt);
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
            'domain'   => is_null($domain) ? $this->container['settings']['cookies::domain'] : $domain,
            'path'     => is_null($path) ? $this->container['settings']['cookies::path'] : $path,
            'secure'   => is_null($secure) ? $this->container['settings']['cookies::secure'] : $secure,
            'httponly' => is_null($httponly) ? $this->container['settings']['cookies::httponly'] : $httponly
        ];
        $this->container['response']->removeCookie($name, $settings);
    }
}
