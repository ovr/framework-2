<?php
namespace Brainwave\Support;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

/**
 * Helpers
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */

/**
 * Get the root Facade application instance.
 *
 * @param  string  $make
 * @return mixed
 */
function app($make = null)
{
    if (!is_null($make)) {
        return app()->make($make);
    }

    return \Brainwave\Workbench\StaticalProxyManager::getFacadeApp();
}

/**
 * Get the path to the application folder.
 *
 * @param  string  $path
 * @return string
 */
function appPath($path = '')
{
    return app('path').($path ? '/'.$path : $path);
}

/**
 * Get the path to the storage folder.
 *
 * @param   string  $path
 * @return  string
 */
function storagePath($path = '')
{
    return app('path.storage').($path ? '/'.$path : $path);
}
