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

use Brainwave\Contracts\Http\Request as RequestContract;
use Brainwave\Http\RequestParameterTrait;
use Symfony\Component\HttpFoundation;

/**
 * Request
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Request extends HttpFoundation\Request implements RequestContract
{
    /**
     * Parameter encapsulation
     */
    use RequestParameterTrait;

    /**
     * {@inheritdoc}
     */
    public function uriSegment($index, $default = null)
    {
        $uri      = trim($this->getPathInfo(), '/');
        $segments = explode('/', $uri);

        return (isset($segments[$index - 1])) ? $segments[$index - 1] : $default;
    }
}
