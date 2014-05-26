<?php
namespace Brainwave\Http\Interfaces;

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

    public function getProtocolVersion();

    public function setProtocolVersion($version);

    public function getStatus();

    public function setStatus($status);

    public function getReasonPhrase();

    public function getHeaders();

    public function hasHeader($name);

    public function getHeader($name);

    public function setHeader($name, $value);

    public function setHeaders(array $headers);

    public function addHeader($name, $value);

    public function addHeaders(array $headers);

    public function removeHeader($name);

    public function getCookies();

    public function setCookies(array $cookies);

    public function hasCookie($name);

    public function getCookie($name);

    public function setCookie($name, $value);

    public function removeCookie($name, $settings = array());

    public function encryptCookies(CryptInterface $crypt);

    /*******************************************************************************
     * Response Body
     ******************************************************************************/

    public function getBody();

    public function setBody(StreamInterface $body);

    public function write($body, $overwrite = false);

    public function getSize();

    /*******************************************************************************
     * Response Helpers
     ******************************************************************************/

    public function finalize(RequestInterface $request);

    public function send();

    public function redirect($url, $status = 302);

    public function isOk();

    public function isSuccessful();

    public function isRedirect();

    public function isRedirection();

    public function isForbidden();

    public function isNotFound();

    public function isClientError();

    public function isServerError();
}
