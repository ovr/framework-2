<?php
namespace Brainwave\Filesystem\Interfaces;

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
 * FilesystemManagerInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
interface FilesystemManagerInterface
{
    /**
     * Get a filesystem implementation.
     *
     * @param  string  $name
     * @return \Brainwave\Filesystem\Interfaces\FilesystemInterface
     */
    public function disk($name = null);
}
