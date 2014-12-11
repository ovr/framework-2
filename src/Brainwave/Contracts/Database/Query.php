<?php
namespace Brainwave\Contracts\Database;

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
 * Query
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Query
{
    /**
     * This function is for special and customized SQL query
     * that used for complex query. With each data that will be inserted,
     * please use quote function to prevent SQL injection.
     *
     * @param  string $query         The SQL query
     * @param  array  $params        SQL params
     * @param  array  $driverOptions
     * @return object The PDOStatement object
     */
    public function query($query, array $params = [], array $driverOptions = []);

    /**
     * Select data from database
     *
     * @param  string       $table   The table name
     * @param  array        $join    Table relativity for table joining.
     *                               Ignore it if no table joining required
     * @param  string|array $columns The target columns of data will be fetched
     * @param  array        $where   The WHERE clause to filter records
     * @return array
     */
    public function select($table, $join, $columns = null, $where = null);

    /**
     * Insert new records in table
     *
     * @param  string  $table The table name
     * @param  array   $datas The data that will be inserted into table.
     * @return integer
     */
    public function insert($table, $datas);

    /**
     * Modify data in table
     *
     * @param  string  $table The table name
     * @param  array   $data  The data that will be modified
     * @param  array   $where The WHERE clause to filter record
     * @return integer The number of rows affected
     */
    public function update($table, $data, $where = null);

    /**
     * Delete data from table
     *
     * @param  string  $table The table name
     * @param  array   $where The WHERE clause to filter records
     * @return integer The number of rows affected
     */
    public function delete($table, $where);

    /**
     * Replace old data into new one
     *
     * @param  string       $table   The table name
     * @param  string|array $columns The target columns of data will be replaced
     * @param  string       $search  The value being searched for
     * @param  string       $replace The replacement value that replaces found search values
     * @param  array        $where   The WHERE clause to filter records
     * @return integer      The number of rows affected
     */
    public function replace($table, $columns, $search = null, $replace = null, $where = null);

    /**
     * Get only one record from table
     *
     * @param  string       $table   The table name
     * @param  string|array $columns The target columns of data will be fetch
     * @param  array        $where   The WHERE clause to filter records
     * @return string|array Return the data of the column
     */
    public function get($table, $columns, array $where);

    /**
     * Determine whether the target data existed
     *
     * @param  string  $table The table name
     * @param  array   $join  Table relativity for table joining
     * @param  array   $where The WHERE clause to filter records
     * @return boolean True of False if the target data has been founded
     */
    public function has($table, $join, $where = null);

    /**
     * Counts the number of rows
     *
     * @param  string  $table  The table name
     * @param  array   $join   Table relativity for table joining
     * @param  string  $column The target column will be counted
     * @param  array   $where  The WHERE clause to filter records
     * @return integer The number of rows
     */
    public function count($table, $join = null, $column = null, $where = null);

    /**
     * Get the maximum value for the column
     *
     * @param  tring  $table  The table name
     * @param  array  $join   Table relativity for table joining
     * @param  string $column The target column will be calculated
     * @param  array  $where  The WHERE clause to filter records
     * @return number The maximum number of the column
     */
    public function max($table, $join, $column = null, $where = null);

    /**
     * Get the minimum value for the column
     *
     * @param  string $table  The table name
     * @param  array  $join   Table relativity for table joining
     * @param  string $column The target column will be calculated
     * @param  array  $where  The WHERE clause to filter records
     * @return number The minimum number of the column
     */
    public function min($table, $join, $column = '*', $where = null);

    /**
     * Get the average value for the column
     *
     * @param  string  $table  The table name
     * @param  array   $join   Table relativity for table joining
     * @param  string  $column The target column will be calculated
     * @param  array   $where  The WHERE clause to filter records
     * @return integer The average number of the column
     */
    public function avg($table, $join, $column = '*', $where = null);

    /**
     * Get the total value for the column
     *
     * @param  string  $table  The table name
     * @param  array   $join   Table relativity for table joining
     * @param  string  $column The target column will be calculated
     * @param  array   $where  The WHERE clause to filter records
     * @return integer The total number of the column
     */
    public function sum($table, $join, $column = '*', $where = null);
}
