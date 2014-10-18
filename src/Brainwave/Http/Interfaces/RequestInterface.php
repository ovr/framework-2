<?php
namespace Brainwave\Http\Interfaces;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
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
 * RequestInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface RequestInterface
{
    /***** Header *****/

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

    /***** Body *****/

    /**
     * @return StreamInterface
     */
    public function getBody();

    /**
     * @return void
     */
    public function setBody(StreamInterface $body);

    /***** Metadata *****/

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

    public function json($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isPost();

    public function post($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isPut();

    public function put($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isPatch();

    public function patch($key = null, $default = null);

    /**
     * @return boolean
     */
    public function isDelete();

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
