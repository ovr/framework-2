<?php
namespace Brainwave\Application\Traits;

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

use Brainwave\Http\Exception\AccessDeniedHttpException;
use Brainwave\Http\Exception\BadRequestHttpException;
use Brainwave\Http\Exception\ConflictHttpException;
use Brainwave\Http\Exception\GoneHttpException;
use Brainwave\Http\Exception\HttpException;
use Brainwave\Http\Exception\LengthRequiredHttpException;
use Brainwave\Http\Exception\MethodNotAllowedHttpException;
use Brainwave\Http\Exception\NotAcceptableHttpException;
use Brainwave\Http\Exception\NotFoundHttpException;
use Brainwave\Http\Exception\PreconditionFailedHttpException;
use Brainwave\Http\Exception\PreconditionRequiredHttpException;
use Brainwave\Http\Exception\ServiceUnavailableHttpException;
use Brainwave\Http\Exception\TooManyRequestsHttpException;
use Brainwave\Http\Exception\UnauthorizedHttpException;
use Brainwave\Http\Exception\UnsupportedMediaTypeHttpException;

/**
 * HttpErrorHandlingTrait
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
trait HttpErrorHandlingTrait
{
    /**
     * Register an application error handler.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function error(\Closure $callback)
    {
        $this['exception']->error($callback);
    }

    /**
     * Register an error handler for fatal errors.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function fatal(\Closure $callback)
    {
        $this->error(function (FatalErrorException $e) use ($callback) {
            return call_user_func($callback, $e);
        });
    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int    $code
     * @param  string $message
     * @param  array  $headers
     * @return void
     *
     * @throws \Brainwave\Http\Exception\HttpException
     * @throws \Brainwave\Http\Exception\GoneHttpException
     * @throws \Brainwave\Http\Exception\ConflictHttpException
     * @throws \Brainwave\Http\Exception\NotFoundHttpException
     * @throws \Brainwave\Http\Exception\BadRequestHttpException
     * @throws \Brainwave\Http\Exception\AccessDeniedHttpException
     * @throws \Brainwave\Http\Exception\UnauthorizedHttpException
     * @throws \Brainwave\Http\Exception\NotAcceptableHttpException
     * @throws \Brainwave\Http\Exception\LengthRequiredHttpException
     * @throws \Brainwave\Http\Exception\TooManyRequestsHttpException
     * @throws \Brainwave\Http\Exception\MethodNotAllowedHttpException
     * @throws \Brainwave\Http\Exception\PreconditionFailedHttpException
     * @throws \Brainwave\Http\Exception\ServiceUnavailableHttpException
     * @throws \Brainwave\Http\Exception\PreconditionRequiredHttpException
     * @throws \Brainwave\Http\Exception\UnsupportedMediaTypeHttpException
     */
    public function abort($code, $message = '', array $headers = array())
    {
        switch ($code) {
            // error code 400
            case Response::HTTP_BAD_REQUEST:
                throw new BadRequestHttpException($message);
            // error code 401
            case Response::HTTP_UNAUTHORIZED:
                throw new UnauthorizedHttpException($message);
            // error code 403
            case Response::HTTP_FORBIDDEN:
                throw new AccessDeniedHttpException($message);
            // error code 404
            case Response::HTTP_NOT_FOUND:
                throw new NotFoundHttpException($message);
            // error code 405
            case Response::HTTP_METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($message);
            // error code 406
            case Response::HTTP_NOT_ACCEPTABLE:
                throw new NotAcceptableHttpException($message);
            // error code 409
            case Response::HTTP_CONFLICT:
                throw new ConflictHttpException($message);
            // error code 410
            case Response::HTTP_GONE:
                throw new GoneHttpException($message);
            // error code 411
            case Response::HTTP_LENGTH_REQUIRED:
                throw new LengthRequiredHttpException($message);
            // error code 412
            case Response::HTTP_PRECONDITION_FAILED:
                throw new PreconditionFailedHttpException($message);
            // error code 415
            case Response::HTTP_UNSUPPORTED_MEDIA_TYPE:
                throw new UnsupportedMediaTypeHttpException($message);
            // error code 428
            case Response::HTTP_PRECONDITION_REQUIRED:
                throw new PreconditionRequiredHttpException($message);
            // error code 429
            case Response::HTTP_TOO_MANY_REQUESTS:
                throw new TooManyRequestsHttpException($message);
            // error code 503
            case Response::HTTP_SERVICE_UNAVAILABLE:
                throw new ServiceUnavailableHttpException($message);
            // all other error codes including 500
            default:
                throw new HttpException($code, $message, null, $headers);
        }
    }

    /**
     * Register a 404 error handler.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function missing(\Closure $callback)
    {
        $this->error(function (NotFoundHttpException $e) use ($callback) {
            return call_user_func($callback, $e);
        });
    }
}
