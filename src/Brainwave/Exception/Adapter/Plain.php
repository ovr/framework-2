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
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use Brainwave\Contracts\Exception\Adapter;
use Brainwave\Contracts\Http\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * PlainDisplayer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Plain implements Adapter
{
    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     */
    public function display(\Exception $exception)
    {
        $status = $exception instanceof HttpExceptionInterface ?
                $exception->getStatusCode() :
                Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        return new Response(file_get_contents(__DIR__.'/resources/plain.html'), $status, $headers);
    }
}
