<?php
namespace Brainwave\Console;

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

/**
 * Application
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class Application
{
    /**
     * Create and boot a new Console application.
     *
     * @param  \Brainwave\Workbench\Workbench  $app
     * @return \Brainwave\Console\Application
     */
    public static function start($app)
    {
        return static::make($app);
    }

    /**
     * Create a new Console application.
     *
     * @param  \Brainwave\Workbench\Workbench  $app
     * @return \Brainwave\Console\Application
     */
    public static function make($app)
    {
        $app->boot();

    }

    protected static function registerCommandStartInfo($command)
    {
    }

    protected static function registerCommandHelp($command)
    {
    }
}
