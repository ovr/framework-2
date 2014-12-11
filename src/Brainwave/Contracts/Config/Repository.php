<?php
namespace Brainwave\Contracts\Config;

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

/**
 * Repository
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Repository extends \ArrayAccess
{
    /**
     * Set an array of configuration options
     * Merge provided values with the defaults to ensure all required values are set
     *
     * @param  array $values
     * @required
     * @return void
     */
    public function setArray(array $values = []);

    /**
     * Get all values as nested array
     *
     * @return array
     */
    public function getAllNested();

    /**
     * Get all values as flattened key array
     *
     * @return array
     */
    public function getAllFlat();

    /**
     * Get all flattened array keys
     *
     * @return array
     */
    public function getKeys();
}
