<?php namespace Brainwave\Exception\HttpException;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Exception\HttpException\HttpException;

/**
 * NotFoundHttpException.
 */
class NotFoundHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param Exception $previous The previous exception
     * @param integer    $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(404, $message, $previous, array(), $code);
    }
}
