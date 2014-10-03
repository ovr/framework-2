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
    protected $manager;

    protected $pdo;

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

    public function select($table, $join, $columns = null, $where = null)
    {
        $query = $this->query($this->select_context($table, $join, $columns, $where));

        return $query ? $query->fetchAll(
            (is_string($columns) && $columns != '*') ? PDO::FETCH_COLUMN : PDO::FETCH_ASSOC
        ) : false;
    }

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
                array_push($columns, $this->column_quote($key));

                switch (gettype($value))
                {
                    case 'NULL':
                        $values[] = 'NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        if (isset($column_match[0])) {
                            $values[] = $this->quote(json_encode($value));
                        } else {
                            $values[] = $this->quote(serialize($value));
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

            $this->exec(
                'INSERT INTO "' . $table . '" (' . implode(', ', $columns) . ') VALUES (' . implode($values, ', ') . ')'
            );

            $lastId[] = $this->getPdo()->lastInsertId();
        }

        return count($lastId) > 1 ? $lastId : $lastId[ 0 ];
    }

    public function update($table, $data, $where = null)
    {
        $fields = array();

        foreach ($data as $key => $value) {
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);

            if (isset($match[3])) {
                if (is_numeric($value)) {
                    $fields[] = $this->column_quote($match[1]) . ' = ' .
                    $this->column_quote($match[1]) . ' ' . $match[3] . ' ' . $value;
                }
            } else {
                $column = $this->column_quote($key);

                switch (gettype($value))
                {
                    case 'NULL':
                        $fields[] = $column . ' = NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

                        if (isset($column_match[0])) {
                            $fields[] = $this->column_quote($column_match[1]) . ' = ' .
                            $this->quote(json_encode($value));
                        } else {
                            $fields[] = $column . ' = ' . $this->quote(serialize($value));
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

        return $this->exec('UPDATE "' . $table . '" SET ' . implode(', ', $fields) . $this->where_clause($where));
    }

    public function delete($table, $where)
    {
        return $this->exec('DELETE FROM "' . $table . '"' . $this->where_clause($where));
    }

    public function replace($table, $columns, $search = null, $replace = null, $where = null)
    {
        if (is_array($columns)) {
            $replace_query = array();

            foreach ($columns as $column => $replacements) {
                foreach ($replacements as $replace_search => $replace_replacement) {
                    $replace_query[] = $column . ' = REPLACE(' . $this->column_quote($column) . ', ' .
                        $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
                }
            }

            $replace_query = implode(', ', $replace_query);
            $where = $search;
        } else {
            if (is_array($search)) {
                $replace_query = array();

                foreach ($search as $replace_search => $replace_replacement) {
                    $replace_query[] = $columns . ' = REPLACE(' . $this->column_quote($columns) . ', ' .
                        $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
                }

                $replace_query = implode(', ', $replace_query);
                $where = $replace;
            } else {
                $replace_query = $columns . ' = REPLACE(' . $this->column_quote($columns) . ', ' .
                    $this->quote($search) . ', ' . $this->quote($replace) . ')';
            }
        }

        return $this->exec('UPDATE "' . $table . '" SET ' . $replace_query . $this->where_clause($where));
    }

    public function get($table, $columns, $where = null)
    {
        if (!isset($where)) {
            $where = array();
        }

        $where['LIMIT'] = 1;

        $data = $this->select($table, $columns, $where);

        return isset($data[0]) ? $data[0] : false;
    }

    public function has($table, $join, $where = null)
    {
        $column = null;

        $result=$this->query(
            $this->select_context($table, $join, $column, $where, 1)
        )->fetchColumn();
        return $result===1 || $result==='1';
    }

    public function count($table, $join = null, $column = null, $where = null)
    {
        return 0 + ($this->query($this->select_context($table, $join, $column, $where, 'COUNT'))->fetchColumn());
    }

    public function max($table, $join, $column = null, $where = null)
    {
        $max = $this->query($this->select_context($table, $join, $column, $where, 'MAX'))->fetchColumn();

        return is_numeric($max) ? $max + 0 : $max;
    }

    public function min($table, $join, $column = null, $where = null)
    {
        $min = $this->query($this->select_context($table, $join, $column, $where, 'MIN'))->fetchColumn();

        return is_numeric($min) ? $min + 0 : $min;
    }

    public function avg($table, $join, $column = null, $where = null)
    {
        return 0 + ($this->query($this->select_context($table, $join, $column, $where, 'AVG'))->fetchColumn());
    }

    public function sum($table, $join, $column = null, $where = null)
    {
        return 0 + ($this->query($this->select_context($table, $join, $column, $where, 'SUM'))->fetchColumn());
    }
}
