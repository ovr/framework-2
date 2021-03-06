<?php
namespace Brainwave\Http;

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

use \GuzzleHttp\Stream\Stream;
use \Brainwave\Http\HeaderTrait;
use \GuzzleHttp\Stream\StreamInterface;
use \Brainwave\Http\Interfaces\HeadersInterface;
use \Brainwave\Http\Interfaces\RequestInterface;
use \Brainwave\Cookie\Interfaces\CookiesJarInterface;
use \Brainwave\Workbench\Environment\Interfaces\EnvironmentInterface;

/**
 * HTTP Request
 *
 * This class provides a simple interface around the Brainwave application environment
 * and raw HTTP request. Use this class to inspect the current HTTP request, including:
 *
 * - The request method
 * - The request headers
 * - The request cookies
 * - The request body
 * - The request parameters (via GET, POST, etc.)
 *
 * This class also contains many other helper methods to inspect the current HTTP request.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Request implements RequestInterface
{
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    /**
     * @var array
     */
    protected static $formDataMediaTypes = ['application/x-www-form-urlencoded'];

    /**
     * Application environment
     *
     * @var \Brainwave\Workbench\Environment\Interfaces\EnvironmentInterface
     */
    protected $env;

    /**
     * Request paths (physical and virtual) cached per instance
     *
     * @var array
     */
    protected $paths;

    /**
     * Request headers
     *
     * @var \Brainwave\Http\Interfaces\HeadersInterface
     */
    protected $headers;

    /**
     * Request cookies
     *
     * @var \Brainwave\Http\Interfaces\CookiesJarInterface
     */
    protected $cookies;

    /**
     * Request query parameters
     *
     * @var array
     */
    protected $queryParameters;

    /**
     * Request body (raw)
     *
     * @var \GuzzleHttp\Stream\StreamInterface
     */
    protected $bodyRaw;

    /**
     * Request body (parsed; only available if body is form-urlencoded)
     *
     * @var array
     */
    protected $body;

    /**
     * Constructor
     *
     * @param EnvironmentInterface  $env
     * @param HeadersInterface      $headers
     * @param CookiesJarInterface   $cookies
     * @param string                $body
     * @api
     */
    public function __construct(
        EnvironmentInterface $env,
        HeadersInterface $headers,
        CookiesJarInterface $cookies,
        $body = null
    ) {
        $this->env = $env;
        $this->headers = $headers;
        $this->cookies = $cookies;

        if (is_string($body) === true) {
            $this->bodyRaw = new Stream(fopen('php://temp', 'r+'));
            $this->bodyRaw->write($body);
        } else {
            $this->bodyRaw = new Stream(fopen('php://input', 'r'));
        }

        $this->bodyRaw->seek(0);
    }

    /**
     * Retrieving Values From $_SERVER
     *
     * @param  string $key   Retrieve a server variable from the request.
     * @return string
     */
    public function server($key = null)
    {
        return $_SERVER[$key];
    }

    /*******************************************************************************
     * Request Header
     ******************************************************************************/

    /**
     * Get HTTP protocol version
     *
     * @return string
     * @api
     */
    public function getProtocolVersion()
    {
        return $this->env->get('SERVER_PROTOCOL');
    }

    /**
     * Get HTTP method
     *
     * @return string
     * @api
     */
    public function getMethod()
    {
        // Get actual request method
        $method = $this->env->get('REQUEST_METHOD');
        $methodOverride = $this->headers->get('HTTP_X_HTTP_METHOD_OVERRIDE');

        // Detect method override (by HTTP header or POST parameter)
        if (!empty($methodOverride)) {
            $method = strtoupper($methodOverride);
        } elseif ($method === static::METHOD_POST) {
            $customMethod = $this->post(static::METHOD_OVERRIDE, false);
            if ($customMethod !== false) {
                $method = strtoupper($customMethod);
            }
        }

        return $method;
    }

    /**
     * Get original HTTP method (before method override applied)
     *
     * @return string
     * @api
     */
    public function getOriginalMethod()
    {
        return $this->env->get('REQUEST_METHOD');
    }

    /**
     * Set HTTP method
     *
     * @param string $method
     * @api
     */
    public function setMethod($method)
    {
        $this->env->set('REQUEST_METHOD', strtoupper($method));
    }

    /**
     * Get URL (scheme host [ port if non-standard ])
     *
     * @return string
     * @api
     */
    public function getUrl()
    {
        $url = $this->getScheme() . '://' . $this->getHost();
        if (
            ($this->getScheme() === 'https' && $this->getPort() !== 443) ||
            ($this->getScheme() === 'http' && $this->getPort() !== 80)
        ) {
            $url .= sprintf(':%s', $this->getPort());
        }

        return $url;
    }

    /**
     * Set URL
     *
     * @param string $url
     * @api
     */
    public function setUrl($url)
    {
        // TODO
    }

    use HeaderTrait;

    /**
     * Get cookies
     *
     * @return array
     * @api
     */
    public function getCookies()
    {
        return $this->cookies->all();
    }

    /**
     * Set multiple cookies
     *
     * @param array $cookies
     * @api
     */
    public function setCookies(array $cookies)
    {
        $this->cookies->replace($cookies);
    }

    /**
     * Does this request have a given cookie?
     *
     * @param  string $name
     * @return bool
     * @api
     */
    public function hasCookie($name)
    {
        return $this->cookies->has($name);
    }

    /**
     * Get cookie value
     *
     * @param  string $name
     * @return string
     * @api
     */
    public function getCookie($name)
    {
        return $this->cookies->get($name);
    }

    /**
     * Set cookie
     *
     * @param string $name
     * @param string $value
     * @api
     */
    public function setCookie($name, $value)
    {
        $this->cookies->set($name, $value);
    }

    /**
     * Remove cookie
     *
     * @param string $name
     * @api
     */
    public function removeCookie($name)
    {
        $this->cookies->remove($name);
    }

    /*******************************************************************************
     * Request Body
     ******************************************************************************/

    /**
     * Get Body
     *
     * @return \GuzzleHttp\Stream\StreamInterface
     * @api
     */
    public function getBody()
    {
        return $this->bodyRaw;
    }

    /**
     * Set request body
     *
     * @param \GuzzleHttp\Stream\StreamInterface $body
     * @api
     */
    public function setBody(StreamInterface $body)
    {
        $this->bodyRaw = $body;
    }

    /*******************************************************************************
     * Request Metadata
     ******************************************************************************/

    /**
     * Does this request use a given method?
     *
     * @param  string $method
     * @return bool
     * @api
     */
    public function isMethod($method)
    {
        return $this->getMethod() === $method;
    }

    /**
     * Is this a GET request?
     *
     * @return bool
     * @api
     */
    public function isGet()
    {
        return $this->isMethod(static::METHOD_GET);
    }

    /**
     * Is this a POST request?
     *
     * @return bool
     * @api
     */
    public function isPost()
    {
        return $this->isMethod(static::METHOD_POST);
    }

    /**
     * Is this a PUT request?
     *
     * @return bool
     * @api
     */
    public function isPut()
    {
        return $this->isMethod(static::METHOD_PUT);
    }

    /**
     * Is this a PATCH request?
     *
     * @return bool
     * @api
     */
    public function isPatch()
    {
        return $this->isMethod(static::METHOD_PATCH);
    }

    /**
     * Is this a DELETE request?
     *
     * @return bool
     * @api
     */
    public function isDelete()
    {
        return $this->isMethod(static::METHOD_DELETE);
    }

    /**
     * Is this a HEAD request?
     *
     * @return bool
     * @api
     */
    public function isHead()
    {
        return $this->isMethod(static::METHOD_HEAD);
    }

    /**
     * Is this a OPTIONS request?
     *
     * @return bool
     * @api
     */
    public function isOptions()
    {
        return $this->isMethod(static::METHOD_OPTIONS);
    }

    /**
     * Is this an AJAX request?
     *
     * @return bool
     * @api
     */
    public function isAjax()
    {
        return $this->params('isajax') === true ||
        $this->headers->get('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest'; // <-- Loose equality is on purpose
    }

    /**
     * Is this an JSON request?
     * @return bool
     * @api
     */
    public function isJson()
    {
        return strpos($this->getContentType(), '/json') !== false;
    }

    /**
     * Is this an XHR request? (alias of \Brainwave\Http\Request::isAjax)
     *
     * @return bool
     * @api
     */
    public function isXhr()
    {
        return $this->isAjax();
    }

    /**
     * Fetch GET and POST data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string           $key
     * @return array|mixed|null
     * @api
     */
    public function params($key = null)
    {
        $get = $this->get() ?: [];
        $post = $this->post() ?: [];
        $union = array_merge($get, $post);
        if ($key) {
            return isset($union[$key]) ? $union[$key] : null;
        }

        return $union;
    }

    /**
     * Fetch GET query parameter(s)
     *
     * Use this method to fetch a GET request query parameter. If the requested GET query parameter
     * identified by the argument does not exist, NULL is returned. If the argument is omitted,
     * all GET query parameters are returned as an array.
     *
     * @param  string           $key
     * @param  mixed            $default Default return value when key does not exist
     * @return array|mixed|null
     * @api
     */
    public function get($key = null, $default = null)
    {
        // Parse and cache query parameters
        if (is_null($this->queryParameters) === true) {
            $qs = $this->env->get('QUERY_STRING');

            if (function_exists('mb_parse_str') === true) {
                mb_parse_str($qs, $this->queryParameters); // <-- Url decodes too
            } else {
                parse_str($qs, $this->queryParameters); // <-- Url decodes too
            }
        }

        // Fetch requested query parameter(s)
        if ($key) {
            if (array_key_exists($key, $this->queryParameters) === true) {
                $returnVal = $this->queryParameters[$key];
            } else {
                $returnVal = $default;
            }
        } else {
            $returnVal = $this->queryParameters;
        }

        return $returnVal;
    }

    /**
     * Fetch request body parameter(s)
     *
     * Use this method to fetch a json body parameter. If the requested json body parameter
     * identified by the argument does not exist, NULL is returned. If the argument is omitted,
     * all json body parameters are returned as an array.
     *
     * @param  string           $key
     * @param  mixed            $default Default return value when key does not exist
     * @return string
     * @throws \RuntimeException If environment input is not available
     * @api
     */
    public function json($key = null, $default = null)
    {
        if (empty($this->body)) {
            if ($this->isJson()) {
                $this->body = json_decode($this->getBody(), true);
            }
        }

        return $this->post($key, $default);
    }

    /**
     * Fetch POST parameter(s)
     *
     * Use this method to fetch a POST body parameter. If the requested POST body parameter
     * identified by the argument does not exist, NULL is returned. If the argument is omitted,
     * all POST body parameters are returned as an array.
     *
     * @param  string           $key
     * @param  mixed            $default Default return value when key does not exist
     * @return string
     * @throws \RuntimeException         If environment input is not available
     * @api
     */
    public function post($key = null, $default = null)
    {
        // Parse and cache request body
        if (is_null($this->body) === true) {
            $this->body = $_POST;

            // Parse raw body if form-urlencoded
            if ($this->isFormData() === true) {
                $rawBody = (string)$this->getBody();
                if (function_exists('mb_parse_str') === true) {
                    mb_parse_str($rawBody, $this->body);
                } else {
                    parse_str($rawBody, $this->body);
                }
            }
        }

        // Fetch POST parameter(s)
        if ($key) {
            if (array_key_exists($key, $this->body) === true) {
                $returnVal = $this->body[$key];
            } else {
                $returnVal = $default;
            }
        } else {
            $returnVal = $this->body;
        }

        return $returnVal;
    }

    /**
     * Fetch PUT data (alias for \Brainwave\Http\Request::post)
     *
     * @param  string           $key
     * @param  mixed            $default Default return value when key does not exist
     * @return string
     * @api
     */
    public function put($key = null, $default = null)
    {
        return $this->post($key, $default);
    }

    /**
     * Fetch PATCH data (alias for \Brainwave\Http\Request::post)
     *
     * @param  string           $key
     * @param  mixed            $default Default return value when key does not exist
     * @return string
     * @api
     */
    public function patch($key = null, $default = null)
    {
        return $this->post($key, $default);
    }

    /**
     * Fetch DELETE data (alias for \Brainwave\Http\Request::post)
     *
     * @param  string           $key
     * @param  mixed            $default Default return value when key does not exist
     * @return string
     * @api
     */
    public function delete($key = null, $default = null)
    {
        return $this->post($key, $default);
    }

    /**
     * Does the Request body contain parsed form data?
     *
     * @return bool
     * @api
     */
    public function isFormData()
    {
        return ($this->getContentType() == '' &&
        $this->getOriginalMethod() === static::METHOD_POST) ||
        in_array($this->getMediaType(), self::$formDataMediaTypes);
    }

    /**
     * Get Content Type
     *
     * @return string|null
     * @api
     */
    public function getContentType()
    {
        return $this->headers->get('CONTENT_TYPE');
    }

    /**
     * Get Media Type (type/subtype within Content Type header)
     *
     * @return string|null
     * @api
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }

        return null;
    }

    /**
     * Get Media Type Params
     *
     * @return array
     * @api
     */
    public function getMediaTypeParams()
    {
        $contentType = $this->getContentType();
        $contentTypeParams = [];
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    /**
     * Get Content Charset
     *
     * @return string|null
     * @api
     */
    public function getContentCharset()
    {
        $mediaTypeParams = $this->getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        }

        return null;
    }

    /**
     * Get Content-Length
     *
     * @return int
     * @api
     */
    public function getContentLength()
    {
        return $this->headers->get('CONTENT_LENGTH', 0);
    }

    /**
     * Get Host
     *
     * @return string
     * @api
     */
    public function getHost()
    {
        $host = $this->headers->get('HTTP_HOST');
        if ($host) {
            if (strpos($host, ':') !== false) {
                $hostParts = explode(':', $host);

                return $hostParts[0];
            }

            return $host;
        }

        return $this->env->get('SERVER_NAME');
    }

    /**
     * Get Host with Port
     *
     * @return string
     * @api
     */
    public function getHostWithPort()
    {
        return sprintf('%s:%s', $this->getHost(), $this->getPort());
    }

    /**
     * Get Port
     *
     * @return int
     * @api
     */
    public function getPort()
    {
        return (int)$this->env->get('SERVER_PORT');
    }

    /**
     * Get Scheme (https or http)
     *
     * @return string
     * @api
     */
    public function getScheme()
    {
        $isHttps = false;

        if ($this->headers->has('X_FORWARDED_PROTO') === true) {
            $headerValue = $this->headers->get('X_FORWARDED_PROTO');
            $isHttps = (strtolower($headerValue) === 'https');
        } else {
            $headerValue = $this->env->get('HTTPS');
            $isHttps = (empty($headerValue) === false && $headerValue !== 'off');
        }

        return $isHttps ? 'https' : 'http';
    }

    /**
     * Get query string
     *
     * @return string
     * @api
     */
    public function getQueryString()
    {
        return $this->env->get('QUERY_STRING', '');
    }

    /**
     * Get client IP address
     *
     * @return string
     * @api
     */
    public function getClientIp()
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if ($this->env->has($key) === true) {
                return $this->env->get($key);
            }
        }

        return null;
    }

    /**
     * Get Referrer
     *
     * @return string|null
     * @api
     */
    public function getReferrer()
    {
        return $this->headers->get('HTTP_REFERER');
    }

    /**
     * Get Referer (for those who can't spell)
     *
     * @return string|null
     * @api
     */
    public function getReferer()
    {
        return $this->getReferrer();
    }

    /**
     * Get User Agent
     *
     * @return string|null
     * @api
     */
    public function getUserAgent()
    {
        return $this->headers->get('HTTP_USER_AGENT');
    }

    /**
     * Get Script Name (physical path)
     *
     * @return string
     * @api
     */
    public function getScriptName()
    {
        $paths = $this->parsePaths();

        return $paths['physical'];
    }

    /**
     * Get Path Info (virtual path)
     *
     * @return string
     * @api
     */
    public function getPathInfo()
    {
        $paths = $this->parsePaths();

        return $paths['virtual'];
    }

    /**
     * Get Path (physical path virtual path)
     *
     * @return string
     * @api
     */
    public function getPath()
    {
        return $this->getScriptName() . $this->getPathInfo();
    }

    /**
     * Parse the physical and virtual paths from the request URI
     *
     * @return array
     */
    protected function parsePaths()
    {
        if (is_null($this->paths) === true) {
            // Server params
            $scriptName = $this->env->get('SCRIPT_NAME'); // <-- "/foo/index.php"
            $requestUri = $this->env->get('REQUEST_URI'); // <-- "/foo/bar?test=abc" or "/foo/index.php/bar?test=abc"
            $queryString = $this->getQueryString(); // <-- "test=abc" or ""

            // Physical path
            if (strpos($requestUri, $scriptName) !== false) {
                $physicalPath = $scriptName; // <-- Without rewriting
            } else {
                $physicalPath = str_replace('\\', '', dirname($scriptName)); // <-- With rewriting
            }
            $scriptName = rtrim($physicalPath, '/'); // <-- Remove trailing slashes

            // Virtual path
            $pathInfo = substr_replace($requestUri, '', 0, strlen($physicalPath)); // <-- Remove physical path
            $pathInfo = str_replace('?' . $queryString, '', $pathInfo); // <-- Remove query string
            $pathInfo = '/' . ltrim($pathInfo, '/'); // <-- Ensure leading slash

            $this->paths = [];
            $this->paths['physical'] = $scriptName;
            $this->paths['virtual'] = $pathInfo;
        }

        return $this->paths;
    }

    /**
     * Convert HTTP request into a string
     *
     * @return string
     * @api
     */
    public function __toString()
    {
        // Build path with query string
        $path = $this->getPath();
        $qs = $this->getQueryString();
        if ($qs) {
            $path = sprintf('%s?%s', $path, $qs);
        }

        // Build headers
        $output = sprintf('%s %s %s', $this->getMethod(), $path, $this->getProtocol()) . PHP_EOL;
        foreach ($this->headers as $name => $value) {
            $output .= sprintf("%s: %s", $name, $value) . PHP_EOL;
        }

        // Build body
        $body = (string)$this->getBody();
        if ($body) {
            $output .= PHP_EOL . $body;
        }

        return $output;
    }
}
