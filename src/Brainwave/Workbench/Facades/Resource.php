<?php
namespace Brainwave\Workbench\Facades;

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

use \Brainwave\Workbench\StaticalProxyManager;

/**
 * Resource
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Resource extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return self::$brainwave;
    }

    public static function set($name)
    {
        return self::$app->getResources($name);
    }
}
