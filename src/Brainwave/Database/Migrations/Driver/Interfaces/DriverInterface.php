<?php
namespace Brainwave\Database\Migrations\Driver\Interfaces;

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
 * DriverInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
interface DriverInterface
{
    /**
     * Get all migrated version numbers
     *
     * @return array
     */
    public function fetchAll();

    /**
     * Up
     *
     * @param Migration $migration
     * @return AdapterInterface
     */
    public function up(Migration $migration);

    /**
     * Down
     *
     * @param Migration $migration
     * @return AdapterInterface
     */
    public function down(Migration $migration);

    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema();

    /**
     * Create Schema
     *
     * @return AdapterInterface
     */
    public function createSchema();
}
