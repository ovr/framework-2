<?php
namespace Brainwave\Http;

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

use \GuzzleHttp\Stream\Stream;
use \GuzzleHttp\Stream\StreamInterface;
use \Brainwave\Crypt\Interfaces\CryptInterface;
use \Brainwave\Http\Interfaces\HeadersInterface;
use \Brainwave\Http\Interfaces\RequestInterface;
use \Brainwave\Http\Interfaces\ResponseInterface;
use \Brainwave\Cookie\Interfaces\CookiesJarInterface;

/** Response
 *
 * This class provides a simple interface around the HTTP response. Use this class
 * to build and inspect the current HTTP response before it is returned to the client:
 *
 * - The response status
 * - The response headers
 * - The response cookies
 * - The response body
 *
 * @package Brainwave
 * @author  Josh Lockhart
 * @since   1.0.0
 */
class Response implements ResponseInterface
{
    /**
     * Response protocol version
     * @var string
     */
    protected $protocolVersion = 'HTTP/1.1';

    /**
     * Response status code
     * @var int
     */
    protected $status = 200;

    /**
     * Response headers
     * @var \Brainwave\Http\Interfaces\HeadersInterface
     */
    protected $headers;

    /**
     * Response cookies
     * @var \Brainwave\Http\Interfaces\CookiesInterface
     */
    protected $cookies;

    /**
     * Response body
     * @var \GuzzleHttp\Stream\StreamInterface
     */
    protected $body;

    /**
     * Response codes and associated messages
     * @var array
     */
    protected static $messages = [
        //Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        102 => '102 Processing',
        //Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        207 => '207 Multi-Status',
        208 => '208 Already Reported',
        226 => '226 IM Used',
        //Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        308 => '308 Permanent Redirect',
        //Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        418 => '418 I\'m a teapot',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        424 => '424 Failed Dependency',
        426 => '426 Upgrade Required',
        428 => '428 Precondition Required',
        429 => '429 Too Many Requests',
        431 => '431 Request Header Fields Too Large',
        //Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported',
        506 => '506 Variant Also Negotiates',
        507 => '507 Insufficient Storage',
        508 => '508 Loop Detected',
        510 => '510 Not Extended',
        511 => '511 Network Authentication Required'
    ];

    /**
     * Constructor
     *
     * @param \Brainwave\Interfaces\Http\HeadersInterface $headers The HTTP response headers
     * @param \Brainwave\Interfaces\Http\CookiesInterface $cookies The HTTP response cookies
     * @param string                                      $body    The HTTP response body
     * @param int                                         $status  The HTTP response status
     * @api
     */
    public function __construct(
        HeadersInterface $headers,
        CookiesJarInterface $cookies,
        $body = '',
        $status = 200
    ) {
        $this->headers = $headers;
        if ($this->headers->has('Content-Type') === false) {
            $this->headers->set('Content-Type', 'text/html');
        }
        $this->cookies = $cookies;
        $this->setStatus($status);
        $this->body = new Stream(fopen('php://temp', 'r+'));
        $this->write($body);
    }

    /*******************************************************************************
     * Response Header
     ******************************************************************************/

    /**
     * Get HTTP protocol version
     *
     * @return string
     * @api
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Set HTTP protocol version
     *
     * @param string $version Either "HTTP/1.1" or "HTTP/1.0"
     * @api
     */
    public function setProtocolVersion($version)
    {
        $this->protocolVersion = $version;
    }

    /**
     * Get response status code
     *
     * @return int
     * @api
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set response status code
     *
     * @param int $status
     * @api
     */
    public function setStatus($status)
    {
        $this->status = (int)$status;
    }

    /**
     * Get response reason phrase
     *
     * @return string
     * @api
     */
    public function getReasonPhrase()
    {
        if (isset(static::$messages[$this->status]) === true) {
            return static::$messages[$this->status];
        }

        return null;
    }

    /**
     * Get HTTP headers
     *
     * @return array
     * @api
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * Does this request have a given header?
     *
     * @param  string $name
     * @return bool
     * @api
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * Get header value
     *
     * @param  string $name
     * @return string
     * @api
     */
    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    /**
     * Set header value
     *
     * @param string $name
     * @param string $value
     * @api
     */
    public function setHeader($name, $value)
    {
        $this->headers->set($name, $value);
    }

    /**
     * Set multiple header values
     *
     * @param array $headers
     * @api
     */
    public function setHeaders(array $headers)
    {
        $this->headers->replace($headers);
    }

    public function addHeader($name, $value)
    {
        // TODO
    }

    public function addHeaders(array $headers)
    {
        // TODO
    }

    /**
     * Remove header
     *
     * @param string $name
     * @api
     */
    public function removeHeader($name)
    {
        $this->headers->remove($name);
    }

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
     * @return array
     * @api
     */
    public function getCookie($name)
    {
        return $this->cookies->get($name);
    }

    /**
     * Set cookie
     *
     * @param string       $name
     * @param array|string $value
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
     * @param array  $settings
     * @api
     */
    public function removeCookie($name, $settings = [])
    {
        $this->cookies->remove($name, $settings);
    }

    /**
     * Encrypt cookies
     *
     * @param \Brainwave\Crypt\Interfaces\CryptInterface $crypt
     * @api
     */
    public function encryptCookies(CryptInterface $crypt)
    {
        $this->cookies->encrypt($crypt);
    }

    /*******************************************************************************
     * Response Body
     ******************************************************************************/

    /**
     * Get response body
     *
     * @return \GuzzleHttp\Stream\StreamInterface
     * @api
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set response body
     *
     * @param \GuzzleHttp\Stream\StreamInterface $body
     * @api
     */
    public function setBody(StreamInterface $body)
    {
        $this->body = $body;
    }

    /**
     * Append response body
     *
     * @param string $body      Content to append to the current HTTP response body
     * @param bool   $overwrite Clear the existing body before writing new content?
     * @api
     */
    public function write($body, $overwrite = false)
    {
        if ($overwrite === true) {
            $this->body->close();
            $this->body = new Stream(fopen('php://temp', 'r+'));
        }
        $this->body->write($body);
    }

    /**
     * Get the response body size if known
     *
     * @return int|false
     * @api
     */
    public function getSize()
    {
        return $this->body->getSize();
    }

    /*******************************************************************************
     * Response Helpers
     ******************************************************************************/

    /**
     * Finalize response for delivery to client
     *
     * Apply final preparations to the resposne object
     * so that it is suitable for delivery to the client.
     *
     * @param  \Brainwave\Http\Interfaces\RequestInterface $request
     * @return \Brainwave\Http\Interfaces\Response Self
     * @api
     */
    public function finalize(RequestInterface $request)
    {
        $sendBody = true;

        if (in_array($this->status, [204, 304]) === true) {
            $this->headers->remove('Content-Type');
            $this->headers->remove('Content-Length');
            $sendBody = false;
        } else {
            $size = @$this->getSize();
            if ($size) {
                $this->headers->set('Content-Length', $size);
            }
        }

        // Serialize cookies into HTTP header
        $this->cookies->setHeaders($this->headers);

        // Remove body if HEAD request
        if ($request->isHead() === true) {
            $sendBody = false;
        }

        // Truncate body if it should not be sent with response
        if ($sendBody === false) {
            $this->body->close();
            $this->body->factory(new Stream(fopen('php://temp', 'r+')));
        }

        return $this;
    }

    /**
     * Send HTTP response headers and body
     *
     * @return \Brainwave\Http\Interfaces\Response Self
     * @api
     */
    public function send()
    {
        // Send headers
        if (headers_sent() === false) {
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header(sprintf('Status: %s', $this->getReasonPhrase()));
            } else {
                header(sprintf('%s %s', $this->getProtocolVersion(), $this->getReasonPhrase()));
            }

            foreach ($this->headers as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $hVal) {
                        header("$name: $value", false);
                    }
                } else {
                    header("$name: $value", false);
                }
            }
        }

        // Send body
        $this->body->seek(0);
        while ($this->body->eof() === false) {
            echo $this->body->read(1024);
        }

        return $this;
    }

    /**
     * Redirect
     *
     * This method prepares the response object to return an HTTP Redirect response
     * to the client.
     *
     * @param string $url    The redirect destination
     * @param int    $status The redirect HTTP status code
     * @api
     */
    public function redirect($url, $status = 302)
    {
        $this->setStatus($status);
        $this->headers->set('Location', $url);
    }

    /**
     * Helpers: Empty?
     *
     * @return bool
     * @api
     */
    public function isEmpty()
    {
        return in_array($this->status, [201, 204, 304]);
    }

    /**
     * Helpers: Informational?
     *
     * @return bool
     * @api
     */
    public function isInformational()
    {
        return $this->status >= 100 && $this->status < 200;
    }

    /**
     * Helpers: OK?
     *
     * @return bool
     * @api
     */
    public function isOk()
    {
        return $this->status === 200;
    }

    /**
     * Helpers: Successful?
     *
     * @return bool
     * @api
     */
    public function isSuccessful()
    {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * Helpers: Redirect?
     *
     * @return bool
     * @api
     */
    public function isRedirect()
    {
        return in_array($this->status, [301, 302, 303, 307]);
    }

    /**
     * Helpers: Redirection?
     *
     * @return bool
     * @api
     */
    public function isRedirection()
    {
        return $this->status >= 300 && $this->status < 400;
    }

    /**
     * Helpers: Forbidden?
     *
     * @return bool
     * @api
     */
    public function isForbidden()
    {
        return $this->status === 403;
    }

    /**
     * Helpers: Not Found?
     *
     * @return bool
     * @api
     */
    public function isNotFound()
    {
        return $this->status === 404;
    }

    /**
     * Helpers: Client error?
     *
     * @return bool
     * @api
     */
    public function isClientError()
    {
        return $this->status >= 400 && $this->status < 500;
    }

    /**
     * Helpers: Server Error?
     *
     * @return bool
     * @api
     */
    public function isServerError()
    {
        return $this->status >= 500 && $this->status < 600;
    }

    /**
     * Convert response to string
     *
     * @return string
     * @api
     */
    public function __toString()
    {
        $output = sprintf('%s %s', $this->getProtocolVersion(), $this->getReasonPhrase()) . PHP_EOL;
        foreach ($this->headers as $name => $value) {
            $output .= sprintf('%s: %s', $name, $value) . PHP_EOL;
        }
        $body = (string)$this->getBody();
        if ($body) {
            $output .= PHP_EOL . $body;
        }

        return $output;
    }
}
