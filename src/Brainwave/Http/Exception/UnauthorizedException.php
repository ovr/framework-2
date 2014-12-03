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

use Brainwave\Http\Exception\HttpException;

/**
 * UnauthorizedException
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class UnauthorizedException extends HttpException
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param string     $previous
     * @param \Exception $code
     */
    public function __construct($message = 'Unauthorized', \Exception $previous = null, $code = 0)
    {
        parent::__construct(401, $message, $previous, [], $code);
    }
}
