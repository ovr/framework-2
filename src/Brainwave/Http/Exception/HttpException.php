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

use \Brainwave\Http\JsonResponse;

/**
 * HttpException
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class HttpException extends \Exception implements Exception\HttpExceptionInterface
{
    /**
     * @var integer
     */
    protected $status;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Constructor
     *
     * @param integer    $status
     * @param string     $message
     * @param \Exception $previous
     * @param array      $headers
     * @param integer    $code
     */
    public function __construct(
        $status,
        $message = null,
        \Exception $previous = null,
        array $headers = [],
        $code = 0
    ) {
        $this->status  = $status;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonResponse()
    {
        $body = [
            'status_code' => $this->getStatusCode(),
            'message'     => $this->getMessage()
        ];

        return new JsonResponse($body, $this->getStatusCode(), $this->getHeaders());
    }
}
