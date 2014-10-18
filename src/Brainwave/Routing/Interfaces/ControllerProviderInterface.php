<?php
namespace Brainwave\Routing\Interfaces;

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

use \Brainwave\Workbench\Workbench;

/**
 * ControllerProviderInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Workbench $app An Application instance
     *
     * @return string A ControllerCollection instance
     */
    public function connect(Workbench $app);
}
