<?php
namespace Brainwave\Http;

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

/**
 * Headers
 *
 * This class manages a collection of HTTP headers. Each \Brainwave\Http\Request
 * and \Brainwave\Http\Response instance will contain a \Brainwave\Http\Cookies instance.
 *
 * Because HTTP headers may be upper, lower, or mixed case, this class
 * normalizes the user-requested header name into a canonical internal format
 * so that it can adapt to and successfully handle any header name format.
 *
 * Otherwise, this class extends \Brainwave\Container and has access to a simple
 * and common interface to manipulate HTTP header data.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
trait HeaderTrait
{
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

    /**
     * Add a header value
     *
     * @param string $name
     * @param string $value
     * @api
     */
    public function addHeader($name, $value)
    {
        $this->headers->add($name, $value);
    }

    /**
     * Add multiple header values
     *
     * @param array $headers
     * @api
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->headers->add($name, $value);
        }
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
}
