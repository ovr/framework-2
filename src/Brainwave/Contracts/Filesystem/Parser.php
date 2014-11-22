<?php
namespace Brainwave\Contracts\Filesystem;

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

/**
 * Parser
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Parser
{
    /**
     * Loads a file and gets its' contents as an array
     *
     * @param  string $filename
     * @param  string $group
     *
     * @return array data
     */
    public function load($filename, $group = null);

    /**
     * Checking if file ist supported
     *
     * @param  string $filename
     *
     * @return boolean
     */
    public function supports($filename);

    /**
     * Format a data file for saving.
     *
     * @param  array $data data
     *
     * @return string data export
     */
    public function format(array $data);
}
