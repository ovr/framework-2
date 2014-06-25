<?php
namespace Brainwave\Workbench\Exception;

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

/**
 * Stop
 *
 * This Exception is thrown when the Brainwave application needs to abort
 * processing and return control flow to the outer PHP script.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Stop extends \Exception
{
}