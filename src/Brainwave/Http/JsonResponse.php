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
 */

use Brainwave\Contracts\Http\Response as ResponseContract;
use Symfony\Component\HttpFoundation;

/**
 * JsonResponse
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class JsonResponse extends HttpFoundation\JsonResponse implements ResponseContract
{
    /**
     * Parameter encapsulation
     */
    use ResponseParameterTrait;
}
