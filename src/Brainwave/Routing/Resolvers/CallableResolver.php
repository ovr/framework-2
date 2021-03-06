<?php
namespace Brainwave\Routing\Resolvers;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Routing\Resolvers\Interfaces\CallableResolverInterface;

/**
 * CallableResolver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class CallableResolver implements CallableResolverInterface
{
    /**
     * [build description]
     * @param  [type] $callable [description]
     * @return [type]           [description]
     */
    public function build($callable)
    {
        $matches = [];

        if (is_string($callable) &&
            preg_match(
                '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!',
                $callable,
                $matches
            )
        ) {
            $class = $matches[1];
            $method = $matches[2];

            $callable = function () use ($class, $method) {
                static $obj = null;
                if ($obj === null) {
                    $obj = new $class;
                }

                return call_user_func_array(array($obj, $method), func_get_args());
            };
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Route callable must be callable');
        }

        return $callable;
    }
}
