<?php
namespace Brainwave\Http\Interfaces;

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

use \GuzzleHttp\Stream\StreamInterface;
use \Brainwave\Crypt\Interfaces\CryptInterface;
use \Brainwave\Http\Interfaces\RequestInterface;

/**
 * ResponseInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface ResponseInterface
{
    /*******************************************************************************
     * Response Header
     ******************************************************************************/

    /**
     * @return string
     */
    public function getProtocolVersion();

    /**
     * @return void
     */
    public function setProtocolVersion($version);

    /**
     * @return integer
     */
    public function getStatus();

    /**
     * @return void
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getReasonPhrase();

    public function getHeaders();

    /**
     * @return boolean
     */
    public function hasHeader($name);

    /**
     * @return string
     */
    public function getHeader($name);

    /**
     * @param string $value
     *
     * @return void
     */
    public function setHeader($name, $value);

    /**
     * @return void
     */
    public function setHeaders(array $headers);

    /**
     * @param string $value
     *
     * @return void
     */
    public function addHeader($name, $value);

    /**
     * @return void
     */
    public function addHeaders(array $headers);

    /**
     * @return void
     */
    public function removeHeader($name);

    public function getCookies();

    /**
     * @return void
     */
    public function setCookies(array $cookies);

    /**
     * @return boolean
     */
    public function hasCookie($name);

    public function getCookie($name);

    /**
     * @return void
     */
    public function setCookie($name, $value);

    /**
     * @return void
     */
    public function removeCookie($name, $settings = []);

    /**
     * @return void
     */
    public function encryptCookies(CryptInterface $crypt);

    /*******************************************************************************
     * Response Body
     ******************************************************************************/

    /**
     * @return StreamInterface
     */
    public function getBody();

    /**
     * @return void
     */
    public function setBody(StreamInterface $body);

    /**
     * @return void
     */
    public function write($body, $overwrite = false);

    /**
     * @return integer|null
     */
    public function getSize();

    /*******************************************************************************
     * Response Helpers
     ******************************************************************************/

    /**
     * @return \Brainwave\Http\Response
     */
    public function finalize(RequestInterface $request);

    /**
     * @return \Brainwave\Http\Response
     */
    public function send();

    /**
     * @return void
     */
    public function redirect($url, $status = 302);

    /**
     * @return boolean
     */
    public function isOk();

    /**
     * @return boolean
     */
    public function isSuccessful();

    /**
     * @return boolean
     */
    public function isRedirect();

    /**
     * @return boolean
     */
    public function isRedirection();

    /**
     * @return boolean
     */
    public function isForbidden();

    /**
     * @return boolean
     */
    public function isNotFound();

    /**
     * @return boolean
     */
    public function isClientError();

    /**
     * @return boolean
     */
    public function isServerError();
}
