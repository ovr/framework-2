<?php
namespace Brainwave\Console\Command;

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

use Pimple\Container;

/**
 * Command
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class CommandResolver
{
    /**
     * Container instance
     *
     * @var Pimple\Container
     */
    protected $container;

    /**
     * Commands
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Constructor
     *
     * @param Pimple\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Check if container key match '.command'
     *
     * @return array
     */
    public function commands()
    {
        if ($this->commands === null) {
            foreach ($this->container->keys() as $serviceName) {
                if (preg_match('/\.command$/', $serviceName)) {
                    $this->commands[] = $this->container[$serviceName];
                }
            }
        }

        return $this->commands;
    }
}
