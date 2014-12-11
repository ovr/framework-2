<?php
namespace Brainwave\Exception\Adapter;

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
 */

use Brainwave\Contracts\Exception\Adapter;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * PlainDisplayer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Symfony implements Adapter
{
    /**
     * The Symfony exception handler.
     *
     * @var \Symfony\Component\Debug\ExceptionHandler
     */
    protected $symfony;

    /**
     * Indicates if JSON should be returned.
     *
     * @var bool
     */
    protected $returnJson;

    /**
     * Create a new Symfony exception displayer.
     *
     * @param \Symfony\Component\Debug\ExceptionHandler $symfony
     * @param bool                                      $returnJson
     *
     * @return void
     */
    public function __construct(ExceptionHandler $symfony, $returnJson = false)
    {
        $this->symfony    = $symfony;
        $this->returnJson = $returnJson;
    }

    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display(\Exception $exception)
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($this->returnJson) {
            return new JsonResponse(array(
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ), $status);
        }

        return $this->symfony->sendPhpResponse($exception);
    }
}
