<?php
namespace Brainwave\Database;

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

use \Brainwave\Database\Grammar\Builder;
use \Brainwave\Database\Grammar\WhereClause;
use \Brainwave\Database\Interfaces\QueryInterface;

/**
 * DatabaseQuery
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class Query implements QueryInterface
{
    /**
     * Database connection instance.
     *
     * @var \Brainwave\Database\Connection\Connection
     */
    protected $connection;

    /**
     * The database query builder instance.
     *
     * @var \Brainwave\Database\Grammar\Builder
     */
    protected $grammar;

    /**
     * WhereClause instance.
     *
     * @var \Brainwave\Database\Grammar\WhereClause
     */
    protected $where;

    /**
     * Bindings for sql
     *
     * @var array
     */
    protected $bindings = [];

    protected $joinArray = [
        '>' => 'LEFT',
        '<' => 'RIGHT',
        '<>' => 'FULL',
        '><' => 'INNER'
    ];

    /**
     * Create a new query instance.
     *
     * @param  \Brainwave\Database\Connection\Interfaces\ConnectionInterface  $connection
     * @return void
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->setQueryGrammar(new Builder($this->connection));
        $this->grammar = $this->connection->getQueryGrammar();
        $this->where = new WhereClause($this->grammar);
    }

    /**
     * This function is for special and customized SQL query
     * that used for complex query. With each data that will be inserted,
     * please use quote function to prevent SQL injection.
     *
     * @param  string $query         The SQL query
     * @param  array  $bindings      SQL bindings
     * @param  array  $driverOptions
     * @return object                The PDOStatement object
     */
    public function query($query, array $bindings = [], array $driverOptions = [])
    {
        return $this->connection->run($query, $bindings, function () use ($query, $bindings, $driverOptions) {

            $driverOptions = array_filter($driverOptions);
            $bindings = array_filter($bindings);

            $con = $this->connection;

            $statement = $con->getPdo()->prepare($query, $driverOptions);

            if (!empty($bindings)) {
                $statement->execute($con->prepareBindings($bindings));
            }

            return $statement;
        });
    }

    /**
     * Set Bindings
     *
     * @param  arrray $bindings
     * @return \Brainwave\Database\Query
     */
    public function bind(arrray $bindings)
    {
        $this->bindings = $bindings;

        return $this;
    }

    /**
     * Get bindings
     *
     * @return array
     */
    protected function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Select data from database
     *
     * @param  string          $table   The table name
     * @param  array           $join    Table relativity for table joining.
     *                                  Ignore it if no table joining required
     * @param  string/array    $columns The target columns of data will be fetched
     * @param  array           $where   The WHERE clause to filter records
     * @param  string          $return
     * @return array
     */
    public function select($table, $join, $columns = null, $where = null)
    {
        $query = $this->query($this->selectContext($table, $join, $columns, $where), $this->getBindings());

        return $query ?
        $query->fetchAll(
            (is_string($columns) && $columns != '*') ?
            \PDO::FETCH_COLUMN :
            \PDO::FETCH_ASSOC
        ) :
        false;
    }

    /**
     * Insert new records in table
     *
     * @param  string $table The table name
     * @param  mixed  $datas The data that will be inserted into table.
     * @return number        The last insert id
     */
    public function insert($table, $datas)
    {
        $lastId = [];

        // Check indexed or associative array
        if (!isset($datas[0])) {
            $datas = array($datas);
        }

        foreach ($datas as $data) {
            $values = [];
            $columns = [];

            foreach ($data as $key => $value) {
                array_push($columns, $this->grammar->wrapColumn($key));

                switch (gettype($value))
                {
                    case 'NULL':
                        $values[] = 'NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $columnMatch);

                        if (isset($columnMatch[0])) {
                            $values[] = $this->grammar->wrapValue(json_encode($value));
                        } else {
                            $values[] = $this->grammar->wrapValue(serialize($value));
                        }
                        break;

                    case 'boolean':
                        $values[] = ($value ? '1' : '0');
                        break;

                    case 'integer':
                    case 'double':
                    case 'string':
                        $values[] = $this->grammar->wrapFunctionName($key, $value);
                        break;

                    case 'object':
                        $values[] = "{$column} = {$value->query}";
                        break;
                }
            }

            $table = $this->grammar->getTablePrefix().$table;
            $col = implode(', ', $columns);
            $val = implode($values, ', ');
            
            $query = "INSERT INTO 
                {$this->grammar->wrapValue($table)} ({$col}) VALUES ({$val})";
        }

        return $this->connection->affectingStatement($query, $this->getBindings());
    }

    /**
     * Modify data in table
     *
     * @param  string $table The table name
     * @param  array  $data  The data that will be modified
     * @param  array  $where The WHERE clause to filter record
     * @return number        The number of rows affected
     */
    public function update($table, $data, $where = null)
    {
        $fields = [];

        foreach ($data as $key => $value) {
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);

            if (isset($match[3])) {
                if (is_numeric($value)) {
                    $fields[] = "{$this->grammar->wrapColumn($match[1])} = 
                    {$this->grammar->wrapColumn($match[1])} {$match[3]} {$value}";
                }
            } else {
                $column = $this->grammar->wrapColumn($key);

                switch (gettype($value))
                {
                    case 'NULL':
                        $fields[] = "{$column} = NULL";
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        if (isset($column_match[0])) {
                            $fields[] = "{$this->grammar->wrapColumn($column_match[1])} = 
                            {$this->grammar->wrapValue(json_encode($value))}";
                        } else {
                            $fields[] = "{$column} = {$this->grammar->wrapValue(serialize($value))}";
                        }
                        break;

                    case 'boolean':
                        $fields[] = "{$column} = {($value ? '1' : '0')}";
                        break;

                    case 'integer':
                    case 'double':
                    case 'string':
                        $fields[] = "{$column} = {$this->wrapFunctionName($key, $value)}";
                        break;
                }
            }
        }

        $table = $this->grammar->getTablePrefix().$table;
        $query = "UPDATE {$this->grammar->wrapValue($table)} SET 
            {implode(', ', $fields)} {$this->whereClause($where)}";

        return $this->connection->affectingStatement($query, $this->getBindings());
    }

    /**
     * Delete data from table
     *
     * @param  string $table The table name
     * @param  array  $where The WHERE clause to filter records
     * @return number        The number of rows affected
     */
    public function delete($table, $where)
    {
        return $this->connection->affectingStatement(
            "DELETE FROM 
            {$this->grammar->wrapValue($this->grammar->getTablePrefix().$table)} 
            {$this->where->where($where)}",
            $this->getBindings()
        );
    }

     /**
     * Replace old data into new one
     *
     * @param  string        $table   The table name
     * @param  string/array  $columns The target columns of data will be replaced
     * @param  string        $search  The value being searched for
     * @param  string        $replace The replacement value that replaces found search values
     * @param  array         $where   The WHERE clause to filter records
     * @return nummber                The number of rows affected
     */
    public function replace($table, $columns, $search = null, $replace = null, $where = null)
    {
        if (is_array($columns)) {
            $replaceQuery = [];

            foreach ($columns as $column => $replacements) {
                foreach ($replacements as $replace_search => $replace_replacement) {
                    $replaceQuery[] = "{$column} = REPLACE({$this->grammar->wrapColumn($column)},
                        {$this->grammar->wrapValue($replace_search)},
                        {$this->grammar->wrapValue($replace_replacement)})";
                }
            }

            $replaceQuery = implode(', ', $replaceQuery);
            $where = $search;
        } else {
            if (is_array($search)) {
                $replaceQuery = [];

                foreach ($search as $replace_search => $replace_replacement) {
                    $replaceQuery[] = "{$columns} = REPLACE({$this->grammar->wrapColumn($columns)},
                        {$this->grammar->wrapValue($replace_search)},
                        {$this->grammar->wrapValue($replace_replacement)})";
                }

                $replaceQuery = implode(', ', $replaceQuery);
                $where = $replace;
            } else {
                $replaceQuery = "{$columns} = REPLACE({$this->grammar->wrapColumn($columns)},
                    {$this->grammar->wrapValue($search)}, 
                    {$this->grammar->wrapValue($replace)})";
            }
        }

        $table = $this->grammar->wrapValue($this->grammar->getTablePrefix().$table);
        $query = "UPDATE {$table} SET {$replaceQuery} {$this->whereClause($where)}";

        return $this->connection->affectingStatement($query, $this->getBindings());
    }

    /**
     * Get only one record from table
     *
     * @param  string       $table   The table name
     * @param  string/array $columns The target columns of data will be fetch
     * @param  array        $where   The WHERE clause to filter records
     * @return string/array          Return the data of the column
     */
    public function get($table, $columns = null, $where = null)
    {
        if (!isset($where)) {
            $where = [];
        }

        $where['LIMIT'] = 1;

        $data = $this->select($table, $columns, $where);

        return isset($data[0]) ? $data[0] : false;
    }

    /**
     * Determine whether the target data existed
     *
     * @param  string  $table The table name
     * @param  array   $join  Table relativity for table joining
     * @param  array   $where The WHERE clause to filter records
     * @return boolean        True of False if the target data has been founded
     */
    public function has($table, $join, $where = null)
    {
        $column = null;

        $result = $this->query(
            $this->selectContext($table, $join, $column, $where, 1),
            $this->getBindings()
        )->fetchColumn($this->connection->getFetchMode());

        return $result === 1 || $result === '1';
    }

    /**
     * Counts the number of rows
     *
     * @param  string  $table  The table name
     * @param  array   $join   Table relativity for table joining
     * @param  string  $column The target column will be counted
     * @param  array   $where  The WHERE clause to filter records
     * @return number          The number of rows
     */
    public function count($table, $join = null, $column = null, $where = null)
    {
        return 0 + ($this->query(
            $this->selectContext($table, $join, $column, $where, 'COUNT'),
            $this->getBindings()
        )->fetchColumn($this->connection->getFetchMode()));
    }

    /**
     * Get the maximum value for the column
     *
     * @param  tring  $table  The table name
     * @param  array  $join   Table relativity for table joining
     * @param  string $column The target column will be calculated
     * @param  array  $where  The WHERE clause to filter records
     * @return number         The maximum number of the column
     */
    public function max($table, $join, $column = null, $where = null)
    {
        $max = $this->query(
            $this->selectContext($table, $join, $column, $where, 'MAX'),
            $this->getBindings()
        )->fetchColumn($this->connection->getFetchMode());

        return is_numeric($max) ? $max + 0 : $max;
    }

    /**
     * Get the minimum value for the column
     *
     * @param  string $table  The table name
     * @param  array  $join   Table relativity for table joining
     * @param  string $column The target column will be calculated
     * @param  array  $where  The WHERE clause to filter records
     * @return number         The minimum number of the column
     */
    public function min($table, $join, $column = null, $where = null)
    {
        $min = $this->query(
            $this->selectContext($table, $join, $column, $where, 'MIN'),
            $this->getBindings()
        )->fetchColumn($this->connection->getFetchMode());

        return is_numeric($min) ? $min + 0 : $min;
    }

    /**
     * Get the average value for the column
     *
     * @param  string $table  The table name
     * @param  array  $join   Table relativity for table joining
     * @param  string $column The target column will be calculated
     * @param  array  $where  The WHERE clause to filter records
     * @return number         The average number of the column
     */
    public function avg($table, $join, $column = null, $where = null)
    {
        return 0 + ($this->query(
            $this->selectContext($table, $join, $column, $where, 'AVG'),
            $this->getBindings()
        )->fetchColumn($this->connection->getFetchMode()));
    }

    /**
     * Get the total value for the column
     *
     * @param  string $table  The table name
     * @param  array  $join   Table relativity for table joining
     * @param  string $column The target column will be calculated
     * @param  array  $where  The WHERE clause to filter records
     * @return number         The total number of the column
     */
    public function sum($table, $join, $column = null, $where = null)
    {
        return 0 + ($this->query(
            $this->selectContext($table, $join, $column, $where, 'SUM'),
            $this->getBindings()
        )->fetchColumn($this->connection->getFetchMode()));
    }

    /**
     * [selectContext description]
     *
     * @param  string $table
     * @param  array  $join
     * @param  mixed  $columns
     * @param  array  $where
     * @param  mixed  $columnFunc
     * @return string
     */
    protected function selectContext($table, $join, $columns = null, $where = null, $columnFunc = null)
    {
        $joinKey = is_array($join) ? array_keys($join) : null;

        if (
            isset($joinKey[0]) &&
            strpos($joinKey[0], '[') === 0
        ) {
            $tableJoin = [];

            foreach ($join as $subTable => $relation) {
                preg_match('/(\[(\<|\>|\>\<|\<\>)\])?([a-zA-Z0-9_\-]*)/', $subTable, $match);

                if ($match[2] !== '' && $match[3] !== '') {
                    if (is_string($relation)) {
                        $relation = "{USING} ({$relation})";
                    }

                    if (is_array($relation)) {
                        // For ['column1', 'column2']
                        if (isset($relation[0])) {
                            $relation = 'USING ("'.implode($relation, '", "').'")';
                        // For ['column1' => 'column2']
                        } else {
                            $relation = "ON {$table} {$this->grammar->wrapValue(key($relation))} = 
                            {$this->grammar->wrapValue($match[3])} {$this->grammar->wrapValue(current($relation))}";
                        }
                    }

                    $tableJoin[] = "{$joinArray[$match[2]]} JOIN {$this->grammar->wrapValue($match[3])} {$relation}";
                }
            }

            $table .= ' '.implode($tableJoin, ' ');
        } else {
            if ($columns === null) {
                if ($where === null) {
                    if (
                        is_array($join) &&
                        isset($columnFunc)
                    ) {
                        $where = $join;
                        $columns = null;
                    } else {
                        $where = null;
                        $columns = $join;
                    }
                } else {
                    $where = $join;
                    $columns = null;
                }
            } else {
                $where = $columns;
                $columns = $join;
            }
        }

        if (isset($columnFunc)) {
            if ($columnFunc === 1) {
                $column = '1';

                if (is_null($where)) {
                    $where = $columns;
                }
            } else {
                if (empty($columns)) {
                    $columns = '*';
                    $where = $join;
                }

                $column = "{$columnFunc} ({$this->grammar->columnPush($columns)})";
            }
        } else {
            $column = $this->grammar->columnPush($columns);
        }

        $table = $this->grammar->wrapValue($this->grammar->getTablePrefix().$table);

        return "SELECT {$column} FROM {$table} {$this->where->where($where)}";
    }
}
