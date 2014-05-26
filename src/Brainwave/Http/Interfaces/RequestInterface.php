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

    public function getProtocolVersion();

    public function getMethod();

    public function setMethod($method);

    public function getUrl();

    public function setUrl($url);

    public function getHeaders();

    public function hasHeader($name);

    public function getHeader($name);

    public function setHeader($name, $value);

    public function setHeaders(array $headers);

    public function addHeader($name, $value);

    public function addHeaders(array $headers);

    public function removeHeader($name);

    /***** Body *****/

    public function getBody();

    public function setBody(StreamInterface $body);

    /***** Metadata *****/

    public function getScriptName();

    public function getPathInfo();

    public function getPath();

    public function getQueryString();

    public function isGet();

    public function get($key = null, $default = null);

    public function isJson();

    public function json($key = null, $default = null);

    public function isPost();

    public function post($key = null, $default = null);

    public function isPut();

    public function put($key = null, $default = null);

    public function isPatch();

    public function patch($key = null, $default = null);

    public function isDelete();

    public function delete($key = null, $default = null);

    public function isHead();

    public function isOptions();

    public function isAjax();

    public function isXhr();

    public function isFormData();
}
