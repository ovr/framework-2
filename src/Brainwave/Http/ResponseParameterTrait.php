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
 * ResponseParameterTrait
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
trait ResponseParameterTrait
{
    /**
     * Return array or single key from $_COOKIE
     *
     * @param string $key
     *
     * @return mixed
     */
    public function cookie($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->headers->getCookies();
        }

        return (isset($this->headers->getCookies()[$key])) ? $this->headers->getCookies()[$key] : $default;
    }

    /**
     * Return array or single key from headers taken from $_SERVER
     *
     * @param string $key
     *
     * @return mixed
     */
    public function headers($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->headers;
        }

        return $this->headers->get($key, $default);
    }
}
