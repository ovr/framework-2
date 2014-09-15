<?php
namespace Brainwave\Resolvers;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Resolvers\Interfaces\CallableResolverInterface;

/**
 * DependencyResolver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class DependencyResolver implements CallableResolverInterface
{
    /**
     * Application Brainwave\Workbench\Workbench
     * @var bool
     */
    private $app;

    /**
     * Set Application
     * @param $app Brainwave\Workbench\Workbench
     */
    public function __construct(Workbench $app)
    {
        $this->app = $app;
    }

    /**
     * [build description]
     * @param  [type] $callable [description]
     * @return [type]           [description]
     */
    public function build($callable)
    {
        if (is_string($callable) && preg_match('!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!', $callable, $matches)) {

            $service = $matches[1];
            $method = $matches[2];

            if (!isset($this->app[$service])) {
                throw new \InvalidArgumentException('Route key does not exist in Workbench');
            }

            $callable =  array($this->app[$service],$method);
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Route callable must be callable');
        }

        return $callable;
    }
}
