<?php
namespace Brainwave\Database\Interfaces;

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
 * Query Interface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
interface QueryInterface
{
    public function select($table, $join, $columns = null, $where = null, $return = 'obj');

    public function insert($table, $datas);

    public function update($table, $data, $where = null);

    public function toggle($table, $data, $where = null);

    public function delete($table, $where);

    public function replace($table, $columns, $search = null, $replace = null, $where = null);

    public function get($table, $columns = null, $where = null);

    /**
     * @return boolean
     */
    public function has($table, $join, $where = null);

    /**
     * @param |null $join
     *
     * @return integer
     */
    public function count($table, $join, $where = null);

    public function max($table, $join, $column = '*', $where = null);

    public function min($table, $join, $column = '*', $where = null);

    /**
     * @return integer
     */
    public function avg($table, $join, $column = '*', $where = null);

    /**
     * @return integer
     */
    public function sum($table, $join, $column = '*', $where = null);
}
