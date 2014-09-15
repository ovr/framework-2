<?php
namespace Brainwave\Http;

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

use \Brainwave\Collection\Collection;
use \Brainwave\Http\Interfaces\HeadersInterface;
use \Brainwave\Environment\Interfaces\EnvironmentInterface;

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
 * @since   0.8.0-dev
 *
 */
class Headers extends Collection implements HeadersInterface
{
    /**
     * Special header keys to treat like HTTP_ headers
     * @var array
     */
    protected $special = array(
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE'
    );

    /**
     * Constructor, will parse an environment for headers if present
     *
     * @param \Brainwave\Interfaces\EnvironmentInterface $environment
     * @api
     */
    public function __construct(EnvironmentInterface $environment = null)
    {
        if (!is_null($environment)) {
            $this->parseHeaders($environment);
        }
    }

    /**
     * Parse provided headers into this collection
     *
     * @param  \Brainwave\Interfaces\EnvironmentInterface $environment
     * @return void
     * @api
     */
    public function parseHeaders(EnvironmentInterface $environment)
    {
        foreach ($environment as $key => $value) {
            $key = strtoupper($key);

            if (
                strpos($key, 'HTTP_') === 0 ||
                strpos($key, 'REDIRECT_') === 0 ||
                in_array($key, $this->special)
            ) {
                if ($key === 'HTTP_CONTENT_TYPE' || $key === 'HTTP_CONTENT_LENGTH') {
                    continue;
                }

                parent::set($this->normalizeKey($key), $value);
            }
        }
    }

    /**
     * Set data key to value
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     * @api
     */
    public function set($key, $value)
    {
        parent::set($this->normalizeKey($key), $value);
    }

    /**
     * Get data value with key
     *
     * @param  string $key     The data key
     * @param  mixed  $default The value to return if data key does not exist
     * @return mixed           The data value, or the default value
     * @api
     */
    public function get($key, $default = null)
    {
        return parent::get($this->normalizeKey($key), $default);
    }

    /**
     * Does this set contain a key?
     *
     * @param  string  $key The data key
     * @return boolean
     * @api
     */
    public function has($key)
    {
        return parent::has($this->normalizeKey($key));
    }

    /**
     * Remove value with key from this set
     *
     * @param string $key The data key
     * @api
     */
    public function remove($key)
    {
        parent::remove($this->normalizeKey($key));
    }

    /**
     * Transform header name into canonical form
     *
     * @param  string $key
     * @return string
     */
    public function normalizeKey($key)
    {
        $key = strtolower($key);
        $key = str_replace(array('-', '_'), ' ', $key);
        $key = preg_replace('#^http #', '', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '-', $key);

        return $key;
    }
}
