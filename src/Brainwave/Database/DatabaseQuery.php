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

    public function __construct(DatabaseManager $manager)
    {
        $this->manager = $manager;
    }

    public function select($table, $join, $columns = null, $where = null, $return = 'obj')
    {
        $query = $this->manager->query($this->selectQuery($table, $join, $columns, $where));

        return $query ?
        $query->fetchAll(
            (is_string($columns) && $columns != '*') ?
            PDO::FETCH_COLUMN :
            ($return == 'obj' ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC)
        ) :
        false;
    }

    public function insert($table, $datas)
    {
        $lastId = [];

        // Check indexed or associative array
        if (!isset($datas[0])) {
            $datas = [$datas];
        }

        foreach ($datas as $data) {
            $keys = [];
            $values = [];
            $columns = [];

            foreach ($data as $key => $value) {
                array_push($columns, $this->manager->columnQuote($key));

                switch (gettype($value))
                {
                    case 'NULL':
                        $values[] = 'NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w])/i", $key, $column_match);

                        if (isset($column_match[0])) {
                            $values[] = $this->manager->quote(json_encode($value));
                        } else {
                            $values[] = $this->manager->quote(serialize($value));
                        }
                        break;

                    case 'boolean':
                        $values[] = ($value ? '1' : '0');
                        break;
                    case 'string':
                        preg_match('/([\w\.])(\[(#?)\])?/', $key, $match);
                        $is_function = isset($match[3]) && $match[3] === '#';
                        $key = $match[1];
                        //no break
                    case 'integer':
                    case 'double':
                        $values[] = $this->manager->quote($value, $is_function);
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
                        $values[] = $value->query;
                        break;
                }
            }

            $keys[] = $key;

            $this->exec(
                'INSERT INTO "'.$table.'" ("'.implode('", "', $keys).
                '") VALUES ('.footerimplode($values, ', ') . ')'
            );

            $lastId[] = $this->pdo->lastInsertId();
        }

        return count($lastId) > 1 ? $lastId : $lastId[ 0 ];
    }

    public function update($table, $data, $where = null)
    {
        $fields = [];

        foreach ($data as $key => $value) {
            preg_match('/([\w])(\[(\|\-|\*|\/)\])?/i', $key, $match);

            if (isset($match[3])) {
                if (is_numeric($value)) {
                    $fields[] = $this->manager->columnQuote($match[1]).' = '.$this->manager->columnQuote($match[1]).' '.
                    $match[3] . ' ' . $value;
                }
            } else {
                $column = $this->manager->columnQuote($key);

                switch (gettype($value))
                {
                    case 'NULL':
                        $fields[] = $column . ' = NULL';
                        break;

                    case 'array':
                        preg_match("/\(JSON\)\s*([\w])/i", $key, $column_match);

                        if (isset($column_match[0])) {
                            $fields[] = $this->manager->columnQuote($column_match[1]).' = '.
                            $this->manager->quote(json_encode($value));
                        } else {
                            $fields[] = $column . ' = ' . $this->manager->quote(serialize($value));
                        }
                        break;

                    case 'boolean':
                        $fields[] = $column . ' = ' . ($value ? '1' : '0');
                        break;
                    case 'string':
                        preg_match('/([\w\.])(\[(#?)\])?/', $key, $match);
                        $is_function = isset($match[3]) && $match[3] === '#';
                        $column = $this->columnQuote($match[1]);
                        // no break
                    case 'integer':
                    case 'double':
                        $fields[] = $column . ' = ' . $this->manager->quote($value, $is_function);
                        break;
                }
            }
        }

        return $this->exec('UPDATE "' . $table . '" SET ' . implode(', ', $fields) . $this->whereClause($where));
    }

    public function toggle($table, $data, $where = null)
    {
        $fields = [];

        foreach ($data as $key => $value) {
            $value = '`' . $value . '`';
            $fields[] = $value . ' = NOT ' . $value;
        }

        return $this->exec(
            'UPDATE `' . $table . '` SET ' . implode(', ', $fields) . $this->where_clause($where)
        );
    }

    public function delete($table, $where)
    {
        return $this->exec('DELETE FROM "' . $table . '"' . $this->whereClause($where));
    }

    public function replace($table, $columns, $search = null, $replace = null, $where = null)
    {
        if (is_array($columns)) {
            $replaceQuery = [];

            foreach ($columns as $column => $replacements) {
                foreach ($replacements as $replaceSearch => $replace_replacement) {
                    $replaceQuery[] = $column.' = REPLACE('.$this->manager->columnQuote($column).', '.
                        $this->manager->quote($replaceSearch).', '.
                        $this->manager->quote($replace_replacement) . ')';
                }
            }

            $replaceQuery = implode(', ', $replaceQuery);
            $where = $search;
        } else {
            if (is_array($search)) {
                $replaceQuery = [];

                foreach ($search as $replaceSearch => $replace_replacement) {
                    $replaceQuery[] = $columns.' = REPLACE('.$this->manager->columnQuote($columns).', '.
                        $this->manager->quote($replaceSearch).', '.
                        $this->manager->quote($replace_replacement) . ')';
                }

                $replaceQuery = implode(', ', $replaceQuery);
                $where = $replace;
            } else {
                $replaceQuery = $columns.' = REPLACE('.$this->manager->columnQuote($columns).', '.
                    $this->manager->quote($search).', '.$this->manager->quote($replace) . ')';
            }
        }

        return $this->exec('UPDATE "' . $table . '" SET ' . $replaceQuery . $this->whereClause($where));
    }

    public function get($table, $columns = null, $where = null)
    {
        if (!isset($where) && !isset($columns)) {
            $columns = [];
            $columns['LIMIT'] = 1;
        } elseif (!isset($where)) {
            $columns['LIMIT'] = 1;
        }

        $data = $this->select($table, $join, $columns, $where);
        return isset($data[0]) ? $data[0] : false;
    }

    private function generateAggregationQuery($aggregation, $table, $column, $join, $where = null)
    {
        if ($where == null) {
            $statement =  $this->selectQuery($table, $aggregation.'('.$column.')', $join);
        } else {
            $statement =  $this->selectQuery($table, $join, $aggregation.'('.$column.')', $where) ;
        }
        return $statement;
    }

    public function has($table, $join, $where = null)
    {
        if ($where == null) {
            $statement = 'SELECT EXISTS(' . $this->selectQuery($table, "1", $join) . ')';
        } else {
            $statement = 'SELECT EXISTS(' . $this->selectQuery($table, $join, "1", $where) . ')';
        }

        return $this->manager->query($statement)->fetchColumn() === '1';
    }

    private function selectQuery($table, $join, $columns = null, $where = null)
    {
        $table = '"' . $table . '"';
        $join_key = is_array($join) ? array_keys($join) : null;

        if (strpos($join_key[0], '[') !== false) {
            $table_join = [];

            $join_array = [
                '>' => 'LEFT',
                '<' => 'RIGHT',
                '<>' => 'FULL',
                '><' => 'INNER'
            ];

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
                            $relation = 'ON '.$table.'."'.key($relation).'" = "'.$match[3].
                            '"."' . current($relation) . '"';
                        }
                    }

                    $table_join[] = $join_array[ $match[2] ] . ' JOIN "' . $match[3] . '" ' . $relation;
                }
            }

            $table .= ' ' . implode($table_join, ' ');
        } else {
            $where = $columns;
            $columns = $join;
        }

        $query = 'SELECT '.$this->manager->columnPush($columns).' FROM '.$table.$this->whereClause($where);
        return $query;
    }

    public function count($table, $join, $where = null)
    {
        return 0 + ($this->manager->query(
            $this->generateAggregationQuery('COUNT', $table, '*', $join, $where = null)
        )->fetchColumn());
    }

    public function max($table, $join, $column = '*', $where = null)
    {
        return 0 + ($this->manager->query(
            $this->generateAggregationQuery('MAX', $table, $column, $join, $where = null)
        )->fetchColumn());
    }

    public function min($table, $join, $column = '*', $where = null)
    {
        return 0 + ($this->manager->query(
            $this->generateAggregationQuery('MIN', $table, $column, $join, $where = null)
        )->fetchColumn());
    }

    public function avg($table, $join, $column = '*', $where = null)
    {
        return 0 + ($this->manager->query(
            $this->generateAggregationQuery('AVG', $table, $column, $join, $where = null)
        )->fetchColumn());
    }

    public function sum($table, $join, $column = '*', $where = null)
    {
        return 0 + ($this->manager->query(
            $this->generateAggregationQuery('SUM', $table, $column, $join, $where = null)
        )->fetchColumn());
    }
}
