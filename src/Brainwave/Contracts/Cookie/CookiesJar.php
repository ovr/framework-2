<?php
namespace Brainwave\Contracts\Cookie;

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

use Brainwave\Contracts\Support\Collection;
use Brainwave\Contracts\Http\Headers as HeadersContract;

/**
 * CookiesJar
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface CookiesJar extends Collection
{
    /**
     * Serialize this collection of cookies into a raw HTTP header
     *
     * @param  HeadersContract $headers
     * @return void
     */
    public function setHeaders(HeadersContract $headers);

    /**
     * Remove cookie
     *
     * Unlike \Brainwave\Collection, this will actually *set* a cookie with
     * an expiration date in the past. This expiration date will force
     * the client-side cache to remove its cookie with the given name
     * and settings.
     *
     * @param  string $key      Cookie name
     * @param  array  $settings Optional cookie settings
     * @return void
     */
    public function remove($key, array $settings = []);

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
     * @param  HeadersContract $headers
     * @param  string          $name
     * @param  string|array    $value
     * @return void
     */
    public function setHeader(HeadersContract $headers, $name, $value);

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
     * @param  HeadersContract $headers
     * @param  string          $name
     * @param  array           $value
     * @return void
     */
    public function deleteHeader(HeadersContract $headers, $name, $value = []);

    /**
     * Parse cookie header
     *
     * This method will parse the HTTP request's `Cookie` header
     * and extract an associative array of cookie names and values.
     *
     * @param string $header
     *
     * @return array
     */
    public function parseHeader($header);
}
