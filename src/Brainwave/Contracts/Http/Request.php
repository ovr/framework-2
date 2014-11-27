<?php
namespace Brainwave\Contracts\Http;

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

use \GuzzleHttp\Stream\StreamInterface;

/**
 * Request
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Request
{
    /**
     * @return string
     */
    public function getProtocolVersion();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return void
     */
    public function setMethod($method);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return void
     */
    public function setUrl($url);

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

    /**
     * @return StreamInterface
     */
    public function getBody();

    /**
     * @return void
     */
    public function setBody(StreamInterface $body);

    /**
     * @return string
     */
    public function getScriptName();

    /**
     * @return string
     */
    public function getPathInfo();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getQueryString();

    /**
     * @return boolean
     */
    public function isGet();

    public function get($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isJson();

    /**
     * @return string
     */
    public function json($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isPost();

    /**
     * @return string
     */
    public function post($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isPut();

    /**
     * @return string
     */
    public function put($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isPatch();

    /**
     * @return string
     */
    public function patch($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isDelete();

    /**
     * @return string
     */
    public function delete($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isHead();

    /**
     * @return boolean
     */
    public function isOptions();

    /**
     * @return boolean
     */
    public function isAjax();

    /**
     * @return boolean
     */
    public function isXhr();

    /**
     * @return boolean
     */
    public function isFormData();
}
