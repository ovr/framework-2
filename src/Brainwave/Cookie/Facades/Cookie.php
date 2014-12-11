<?php
namespace Brainwave\Cookie\Facades;

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

use Brainwave\Application\StaticalProxyManager;

/**
 * Cookie
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class Cookie extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'cookie';
    }
}
