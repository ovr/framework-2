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

use \Brainwave\Collection\Collection;
use \Brainwave\Contracts\Http\Headers as HeadersContract;
use \Brainwave\Contracts\Cookie\CookiesJar as CookiesJarContract;
use \Brainwave\Contracts\Encrypter\Encrypter as EncrypterContract;

/**
 * Cookies
 *
 * This class manages a collection of HTTP cookies. Each
 * \Brainwave\Http\Request and \Brainwave\Http\Response instance will contain a
 * \Brainwave\Cookie\CookieJar instance.
 *
 * This class has several helper methods used to parse
 * HTTP `Cookie` headers and to serialize cookie data into
 * HTTP headers.
 *
 * Like many other Brainwave application objects, \Brainwave\Cookie\CookieJar extends
 * \Brainwave\Collection\Collection so you have access to a simple and common interface
 * to manipulate HTTP cookies.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class CookieJar extends Collection implements CookiesJarContract
{
    /**
     * Default cookie settings
     *
     * @var array
     */
    protected $defaults = [
        'Name'     => null,
        'Value'    => null,
        'Domain'   => null,
        'Path'     => '/',
        'Max-Age'  => null,
        'Expires'  => null,
        'Secure'   => false,
        'Discard'  => false,
        'HttpOnly' => false
    ];

    /**
     * Constructor, will parse headers for cookie information if present
     *
     * @param HeadersContract $headers
     */
    public function __construct(HeadersContract $headers = null)
    {
        if ($headers !== null) {
            $this->data = $this->parseHeader($headers->get('Cookie', ''));
        }
    }

    /**
     * Set cookie
     *
     * The second argument may be a single scalar value, in which case
     * it will be merged with the default settings and considered the `value`
     * of the merged result.
     *
     * The second argument may also be an array containing any or all of
     * the keys shown in the default settings above. This array will be
     * merged with the defaults shown above.
     *
     * @param string $key   Cookie name
     * @param mixed  $value Cookie settings
     */
    public function set($key, $value)
    {
        if (is_array($value)) {
            $settings = array_replace($this->defaults, $value);
        } else {
            $settings = array_replace($this->defaults, ['value' => $value]);
        }

        parent::set($key, $settings);
    }

    /**
     * Remove cookie
     *
     * Unlike \Brainwave\Collection, this will actually *set* a cookie with
     * an expiration date in the past. This expiration date will force
     * the client-side cache to remove its cookie with the given name
     * and settings.
     *
     * @param string $key      Cookie name
     * @param array  $settings Optional cookie settings
     */
    public function remove($key, array $settings = [])
    {
        $settings['value'] = '';
        $settings['expires'] = time() - 86400;
        $this->set($key, array_replace($this->defaults, $settings));
    }

    /**
     * Encrypt cookies
     *
     * This method iterates and encrypts data values.
     *
     * @param EncrypterContract $crypt
     */
    public function encrypt(EncrypterContract $crypt)
    {
        foreach ($this as $name => $settings) {
            $settings['value'] = $crypt->encrypt($settings['value']);
            $this->set($name, $settings);
        }
    }

    /**
     * Serialize this collection of cookies into a raw HTTP header
     *
     * @param HeadersContract $headers
     */
    public function setHeaders(HeadersContract $headers)
    {
        foreach ($this->data as $name => $settings) {
            $this->setHeader($headers, $name, $settings);
        }
    }

    /**
     * Set HTTP cookie header
     *
     * This method will construct and set the HTTP `Set-Cookie` header. Brainwave
     * uses this method instead of PHP's native `setcookie` method. This allows
     * more control of the HTTP header irrespective of the native implementation's
     * dependency on PHP versions.
     *
     * This method accepts the \Brainwave\Http\Headers object by reference as its
     * first argument; this method directly modifies this object instead of
     * returning a value.
     *
     * @param HeadersContract $headers
     * @param string          $name
     * @param string|array    $value
     */
    public function setHeader(HeadersContract $headers, $name, $value)
    {
        $values = [];

        if (is_array($value)) {

            $headerArray = [
                'domain' => ' domain=',
                'path' => ' path=',
                'secure' => ' secure',
                'httponly' => ' HttpOnly'
            ];

            foreach ($headerArray as $variable => $valueHeader) {
                if (isset($value[$variable]) && $value[$variable]) {
                    $erg = ($value[$variable] === true) ? '' : $value[$variable];
                    $values[] = ';'. $valueHeader . $erg;
                }
            }

            if (isset($value['expires'])) {
                if (is_string($value['expires'])) {
                    $timestamp = strtotime($value['expires']);
                } else {
                    $timestamp = (int) $value['expires'];
                }

                if ($timestamp !== 0) {
                    $values[] = '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
                }
            }

            $value = (string)$value['value'];
        }

        $cookie = sprintf(
            '%s=%s',
            urlencode($name),
            urlencode((string) $value) . implode('', $values)
        );

        if (!$headers->has('Set-Cookie') || $headers->get('Set-Cookie') === '') {
            $headers->set('Set-Cookie', $cookie);
        } else {
            $headers->set('Set-Cookie', implode("\n", [$headers->get('Set-Cookie'), $cookie]));
        }
    }

    /**
     * Delete HTTP cookie header
     *
     * This method will construct and set the HTTP `Set-Cookie` header to invalidate
     * a client-side HTTP cookie. If a cookie with the same name (and, optionally, domain)
     * is already set in the HTTP response, it will also be removed. Brainwave uses this method
     * instead of PHP's native `setcookie` method. This allows more control of the HTTP header
     * irrespective of PHP's native implementation's dependency on PHP versions.
     *
     * This method accepts the \Brainwave\Http\Headers object by reference as its
     * first argument; this method directly modifies this object instead of
     * returning a value.
     *
     * @param HeadersContract $headers
     * @param string          $name
     * @param array           $value
     */
    public function deleteHeader(HeadersContract $headers, $name, $value = [])
    {
        $crumbs = ($headers->has('Set-Cookie') ? explode("\n", $headers->get('Set-Cookie')) : []);
        $cookies = [];

        foreach ($crumbs as $crumb) {
            if (isset($value['domain']) && $value['domain']) {
                $regex = sprintf('@%s=.*domain=%s@', urlencode($name), preg_quote($value['domain']));
            } else {
                $regex = sprintf('@%s=@', urlencode($name));
            }

            if (preg_match($regex, $crumb) === 0) {
                $cookies[] = $crumb;
            }
        }

        if (!empty($cookies)) {
            $headers->set('Set-Cookie', implode("\n", $cookies));
        } else {
            $headers->remove('Set-Cookie');
        }

        $this->setHeader(
            $headers,
            $name,
            array_merge(
                [
                    'value' => '',
                    'path' => null,
                    'domain' => null,
                    'expires' => time() - 100
                ],
                $value
            )
        );
    }

    /**
     * Parse cookie header
     *
     * This method will parse the HTTP request's `Cookie` header
     * and extract an associative array of cookie names and values.
     *
     * @param  string $header
     *
     * @return array
     */
    public function parseHeader($header)
    {
        $header = rtrim($header, "\r\n");
        $pieces = preg_split('@\s*[;,]\s*@', $header);
        $cookies = [];

        foreach ($pieces as $cookie) {
            $cookie = explode('=', $cookie, 2);

            if (count($cookie) === 2) {
                $key = urldecode($cookie[0]);
                $value = urldecode($cookie[1]);

                if (!isset($cookies[$key])) {
                    $cookies[$key] = $value;
                }
            }
        }

        return $cookies;
    }
}
