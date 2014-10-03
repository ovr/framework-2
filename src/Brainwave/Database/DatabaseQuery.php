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

use \Brainwave\Database\DatabaseManager;
use \Brainwave\Database\Interfaces\QueryInterface;

/**
 * DatabaseQuery
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class DatabaseQuery implements QueryInterface
{
    /**
     * [$manager description]
     * @var [type]
     */
    protected $manager;

    /**
     * [$pdo description]
     * @var [type]
     */
    protected $pdo;

    /**
     * [__construct description]
     * @param DatabaseManager $manager [description]
     */
    public function __construct(DatabaseManager $manager)
    {
        $this->manager = $manager;
        $this->pdo = $this->manager->getPdo();
    }

    /**
     * [query description]
     * @param  [type] $query  [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function query($query, $params = [])
    {
        $this->manager->setQueryString($query);
        $this->manager->addQueryToHistory($query);

        $request = $this->pdo->prepare($query);
        $request->execute($params);

        return $request;
    }

    /**
     * [select description]
     * @param  [type] $table   [description]
     * @param  [type] $join    [description]
     * @param  [type] $columns [description]
     * @param  [type] $where   [description]
     * @return [type]          [description]
     */
    public function select($table, $join, $columns = null, $where = null)
    {
        $query = $this->query($this->selectContext($table, $join, $columns, $where));

        return $query ? $query->fetchAll(
            (is_string($columns) && $columns != '*') ? PDO::FETCH_COLUMN : PDO::FETCH_ASSOC
        ) : false;
    }

    /**
     * [insert description]
     * @param  [type] $table [description]
     * @param  [type] $datas [description]
     * @return [type]        [description]
     */
    public function insert($table, $datas)
    {
        $lastId = array();

        // Check indexed or associative array
        if (!isset($datas[0])) {
            $datas = array($datas);
        }

        foreach ($datas as $data) {
            $values = array();
            $columns = array();

            foreach ($data as $key => $value) {
                array_push($columns, $this->columnQuote($key));

                switch (gettype($value))
                {
                    case 'NULL':
                        $values[] = 'NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        if (isset($column_match[0])) {
                            $values[] = $this->manager->quote(json_encode($value));
                        } else {
                            $values[] = $this->manager->quote(serialize($value));
                        }
                        break;

                    case 'boolean':
                        $values[] = ($value ? '1' : '0');
                        break;

                    case 'integer':
                    case 'double':
                    case 'string':
                        $values[] = $this->fn_quote($key, $value);
                        break;
                    /*
                    * Adding option for Select Query to be used in Where Statement
                    * Example
                    *
                    * "AND" => [
                    *      "employees.company" =>
                    *      (object)['query'=>"(SELECT id FROM companies WHERE email = '".$email."')"],
                    * ],
                    *
                    * Assuming in this example that companies.email is an unique field
                    */
                    case 'object':
                        $wheres[] = $column . ' = ' . $value->query;
                        break;
                }
            }

            $this->manager->exec(
                'INSERT INTO "' . $this->manager->quoteIdent($table) . '" (' . implode(', ', $columns) . ') VALUES (' . implode($values, ', ') . ')'
            );

            $lastId[] = $this->getPdo()->lastInsertId();
        }

        return count($lastId) > 1 ? $lastId : $lastId[ 0 ];
    }

    /**
     * [update description]
     * @param  [type] $table [description]
     * @param  [type] $data  [description]
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function update($table, $data, $where = null)
    {
        $fields = array();

        foreach ($data as $key => $value) {
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);

            if (isset($match[3])) {
                if (is_numeric($value)) {
                    $fields[] = $this->columnQuote($match[1]) . ' = ' .
                    $this->columnQuote($match[1]) . ' ' . $match[3] . ' ' . $value;
                }
            } else {
                $column = $this->columnQuote($key);

                switch (gettype($value))
                {
                    case 'NULL':
                        $fields[] = $column . ' = NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        if (isset($column_match[0])) {
                            $fields[] = $this->columnQuote($column_match[1]) . ' = ' .
                            $this->manager->quote(json_encode($value));
                        } else {
                            $fields[] = $column . ' = ' . $this->manager->quote(serialize($value));
                        }
                        break;

                    case 'boolean':
                        $fields[] = $column . ' = ' . ($value ? '1' : '0');
                        break;

                    case 'integer':
                    case 'double':
                    case 'string':
                        $fields[] = $column . ' = ' . $this->fn_quote($key, $value);
                        break;
                }
            }
        }

        return $this->manager->exec(
            'UPDATE "' . $this->manager->quoteIdent($table) . '" SET ' . implode(', ', $fields) . $this->whereClause($where)
        );
    }

    /**
     * [delete description]
     * @param  [type] $table [description]
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function delete($table, $where)
    {
        return $this->manager->exec('DELETE FROM "' . $this->manager->quoteIdent($table) . '"' . $this->whereClause($where));
    }

    /**
     * [replace description]
     * @param  [type] $table   [description]
     * @param  [type] $columns [description]
     * @param  [type] $search  [description]
     * @param  [type] $replace [description]
     * @param  [type] $where   [description]
     * @return [type]          [description]
     */
    public function replace($table, $columns, $search = null, $replace = null, $where = null)
    {
        if (is_array($columns)) {
            $replaceQuery = array();

            foreach ($columns as $column => $replacements) {
                foreach ($replacements as $replace_search => $replace_replacement) {
                    $replaceQuery[] = $column . ' = REPLACE(' . $this->columnQuote($column) . ', ' .
                        $this->manager->quote($replace_search) . ', ' .
                        $this->manager->quote($replace_replacement) . ')';
                }
            }

            $replaceQuery = implode(', ', $replaceQuery);
            $where = $search;
        } else {
            if (is_array($search)) {
                $replaceQuery = array();

                foreach ($search as $replace_search => $replace_replacement) {
                    $replaceQuery[] = $columns . ' = REPLACE(' . $this->columnQuote($columns) . ', ' .
                        $this->manager->quote($replace_search) . ', ' .
                        $this->manager->quote($replace_replacement) . ')';
                }

                $replaceQuery = implode(', ', $replaceQuery);
                $where = $replace;
            } else {
                $replaceQuery = $columns . ' = REPLACE(' . $this->columnQuote($columns) . ', ' .
                    $this->manager->quote($search) . ', ' . $this->manager->quote($replace) . ')';
            }
        }

        return $this->manager->exec(
            'UPDATE "' . $this->manager->quoteIdent($table) . '" SET ' . $replaceQuery . $this->whereClause($where)
        );
    }

    /**
     * [get description]
     * @param  [type] $table   [description]
     * @param  [type] $columns [description]
     * @param  [type] $where   [description]
     * @return [type]          [description]
     */
    public function get($table, $columns, $where = null)
    {
        if (!isset($where)) {
            $where = array();
        }

        $where['LIMIT'] = 1;

        $data = $this->select($table, $columns, $where);

        return isset($data[0]) ? $data[0] : false;
    }

    /**
     * [has description]
     * @param  [type]  $table [description]
     * @param  [type]  $join  [description]
     * @param  [type]  $where [description]
     * @return boolean        [description]
     */
    public function has($table, $join, $where = null)
    {
        $column = null;

        $result=$this->query(
            $this->selectContext($table, $join, $column, $where, 1)
        )->fetchColumn();
        return $result===1 || $result==='1';
    }

    /**
     * [count description]
     * @param  [type] $table  [description]
     * @param  [type] $join   [description]
     * @param  [type] $column [description]
     * @param  [type] $where  [description]
     * @return [type]         [description]
     */
    public function count($table, $join = null, $column = null, $where = null)
    {
        return 0 + ($this->query($this->selectContext($table, $join, $column, $where, 'COUNT'))->fetchColumn());
    }

    /**
     * [max description]
     * @param  [type] $table  [description]
     * @param  [type] $join   [description]
     * @param  [type] $column [description]
     * @param  [type] $where  [description]
     * @return [type]         [description]
     */
    public function max($table, $join, $column = null, $where = null)
    {
        $max = $this->query($this->selectContext($table, $join, $column, $where, 'MAX'))->fetchColumn();

        return is_numeric($max) ? $max + 0 : $max;
    }

    /**
     * [min description]
     * @param  [type] $table  [description]
     * @param  [type] $join   [description]
     * @param  [type] $column [description]
     * @param  [type] $where  [description]
     * @return [type]         [description]
     */
    public function min($table, $join, $column = null, $where = null)
    {
        $min = $this->query($this->selectContext($table, $join, $column, $where, 'MIN'))->fetchColumn();

        return is_numeric($min) ? $min + 0 : $min;
    }

    /**
     * [avg description]
     * @param  [type] $table  [description]
     * @param  [type] $join   [description]
     * @param  [type] $column [description]
     * @param  [type] $where  [description]
     * @return [type]         [description]
     */
    public function avg($table, $join, $column = null, $where = null)
    {
        return 0 + ($this->query($this->selectContext($table, $join, $column, $where, 'AVG'))->fetchColumn());
    }

    /**
     * [sum description]
     * @param  [type] $table  [description]
     * @param  [type] $join   [description]
     * @param  [type] $column [description]
     * @param  [type] $where  [description]
     * @return [type]         [description]
     */
    public function sum($table, $join, $column = null, $where = null)
    {
        return 0 + ($this->query($this->selectContext($table, $join, $column, $where, 'SUM'))->fetchColumn());
    }

    /**
     * [selectContext description]
     * @param  [type] $table    [description]
     * @param  [type] $join     [description]
     * @param  [type] $columns  [description]
     * @param  [type] $where    [description]
     * @param  [type] $columnFn [description]
     * @return [type]           [description]
     */
    protected function selectContext($table, $join, &$columns = null, $where = null, $columnFn = null)
    {
        $table = '"' . $table . '"';
        $join_key = is_array($join) ? array_keys($join) : null;

        if (
            isset($join_key[0]) &&
            strpos($join_key[0], '[') === 0
        ) {
            $tableJoin = array();

            $joinArray = array(
                '>' => 'LEFT',
                '<' => 'RIGHT',
                '<>' => 'FULL',
                '><' => 'INNER'
            );

            foreach ($join as $sub_table => $relation) {
                preg_match('/(\[(\<|\>|\>\<|\<\>)\])?([a-zA-Z0-9_\-]*)/', $sub_table, $match);

                if ($match[2] != '' && $match[3] != '') {
                    if (is_string($relation)) {
                        $relation = 'USING ("' . $relation . '")';
                    }

                    if (is_array($relation)) {
                        // For ['column1', 'column2']
                        if (isset($relation[0])) {
                            $relation = 'USING ("' . implode($relation, '", "') . '")';
                        // For ['column1' => 'column2']
                        } else {
                            $relation = 'ON ' . $table . '."' .
                            key($relation) . '" = "' . $match[3] . '"."' . current($relation) . '"';
                        }
                    }

                    $tableJoin[] = $joinArray[ $match[2] ] . ' JOIN "' . $match[3] . '" ' . $relation;
                }
            }

            $table .= ' ' . implode($tableJoin, ' ');
        } else {
            if (is_null($columns)) {
                if (is_null($where)) {
                    if (
                        is_array($join) &&
                        isset($columnFn)
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

        if (isset($columnFn)) {
            if ($columnFn == 1) {
                $column = '1';

                if (is_null($where)) {
                    $where = $columns;
                }
            } else {
                if (empty($columns)) {
                    $columns = '*';
                    $where = $join;
                }

                $column = $columnFn . '(' . $this->manager->columnPush($columns) . ')';
            }
        } else {
            $column = $this->manager->columnPush($columns);
        }

        return 'SELECT ' . $column . ' FROM ' . $table . $this->manager->whereClause($where);
    }
}
