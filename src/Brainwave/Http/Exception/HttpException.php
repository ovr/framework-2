<?php
namespace Brainwave\Http\Exception;

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

use \Brainwave\Contracts\Http\HttpException as HttpExceptionContract;

/**
 * HttpException
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class HttpException extends \RuntimeException implements HttpExceptionContract
{
    private $statusCode;
    private $headers;

    /**
     * Create a new http exception instance.
     *
     * @param integer    $statusCode
     * @param string     $message
     * @param \Exception $previous
     * @param array      $headers
     * @param integer   $code
     */
    public function __construct(
        $statusCode,
        $message = null,
        \Exception $previous = null,
        array $headers = [],
        $code = 0
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
