<?php
namespace Brainwave\Database\Grammar;

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

use \Brainwave\Database\Grammar\Builder;

/**
 * WhereClause
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class WhereClause
{
    /**
     * The query builder instance.
     *
     * @var \Brainwave\Database\Grammar\Builder
     */
    protected $query;

    /**
     * [$whereCondition description]
     * @var array
     */
    protected $whereCondition = [
        'LIKE',
        'MATCH',
        'GROUP',
        'ORDER',
        'HAVING',
        'LIMIT'
    ];

    /**
     * Create a new where clause instance.
     *
     * @param  \Brainwave\Database\Grammar\Builder  $query
     * @return void
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * [where description]
     *
     * @param  mixed  $where
     * @return string
     */
    public function where($where)
    {
        $whereClause = '';

        if (is_array($where)) {
            $whereAND = preg_grep('/^AND\s*#?$/i', array_keys($where));
            $whereOR = preg_grep('/^OR\s*#?$/i', array_keys($where));
            $singleCondition = array_diff_key($where, array_flip($this->whereCondition));

            if ($singleCondition !== []) {
                $whereClause = $this->whereNull($singleCondition);
            }

            if (!empty(array_filter($whereAND))) {
                $value = array_values($whereAND);
                $whereClause = $this->whereAnd($where["{$value[0]}"]);
            }

            if (!empty(array_filter($whereOR))) {
                $value = array_values($whereOR);
                $whereClause = $this->whereOr($where["{$value[0]}"]);
            }

            foreach ($this->whereCondition as $condition) {
                if (isset($where[$condition])) {
                    $condition = strtolower($condition);
                    $condition = ucwords($condition);

                    $whereClause .= $this->{'where'.$condition}($where[$condition], $whereClause);
                }
            }
        } elseif ($where !== null) {
            $whereClause .= ' ' . $where;
        }

        return $whereClause;
    }

    /**
     * Where NULL
     *
     * @param  array  $array
     * @return string
     */
    protected function whereNull(array $array)
    {
        return " WHERE {$this->whereDataImplode($array, '')}";
    }

    /**
     * Where AND
     *
     * @param  array  $array
     * @return string
     */
    protected function whereAnd(array $array)
    {
        return " WHERE {$this->whereDataImplode($array, ' AND')}";
    }

    /**
     * Where OR
     *
     * @param  array  $array
     * @return string
     */
    protected function whereOr(array $array)
    {
        return " WHERE {$this->whereDataImplode($array, ' OR')}";
    }

    /**
     * Where GROUP
     *
     * @param  array  $array
     * @param  array  $whereClause
     * @return string
     */
    protected function whereGroup(array $array, $whereClause)
    {
        $group =$this->query->wrapColumn($array);
        return " GROUP BY {$group}";
    }

    /**
     * Where ORDER
     *
     * @param  array $array
     * @param  string $whereClause
     * @return string
     */
    protected function whereOrder($array, $whereClause)
    {
        $rsort = '/(^[a-zA-Z0-9_\-\.]*)(\s*(DESC|ASC))?/';

        if (is_array($array)) {
            if (
                isset($array[1]) &&
                is_array($array[1])
            ) {
                return " ORDER BY FIELD({$this->query->wrapColumn($array[0])}, {$this->query->wrapArray($array[1])})";
            } else {
                $stack = [];

                foreach ($array as $column) {
                    preg_match($rsort, $column, $arrayMatch);

                    array_push(
                        $stack,
                        "{str_replace('.', '`.`', $arrayMatch[1])}".(isset($arrayMatch[3]) ? ' '.$arrayMatch[3] : '')
                    );
                }

                return " ORDER BY {$this->query->wrapValue(implode($stack, ','))}";
            }
        } else {
            preg_match($rsort, $array, $arrayMatch);

            return " ORDER BY {$this->query->wrapValue(str_replace('.', '`.`', $arrayMatch[1]))}".
            (isset($arrayMatch[3]) ? ' '.$arrayMatch[3] : '');
        }
    }

    /**
     * Where HAVING
     *
     * @param  array  $array
     * @param  string $whereClause
     * @return string
     */
    protected function whereHaving(array $array, $whereClause)
    {
        return " HAVING {$this->whereDataImplode($array, '')}";
    }

    /**
     * Where LIMIT
     *
     * @param  array $array
     * @param  string $whereClause
     * @return string
     */
    protected function whereLimit($array, $whereClause)
    {
        if (is_numeric($array)) {
            $whereClause .= " LIMIT {$array}";
        }

        if (is_array($array) && is_numeric($array[0]) && is_numeric($array[1])) {
            $whereClause .= " LIMIT {$array[0]}, {$array[1]}";
        }

        return $whereClause;
    }

    /**
     * Where LIKE
     *
     * @param  array  $array
     * @param  string $whereClause
     * @return array
     */
    protected function whereLike($array, $whereClause)
    {
        if (is_array($array)) {
            $arrayOR = $array['OR'];
            $arrayAND = $array['AND'];
            $clauseWrap = [];

            if (isset($arrayOR) || isset($arrayAND)) {
                $connector = $arrayOR ? 'OR' : 'AND';
                $array = $arrayOR ? $arrayOR : $arrayAND;
            } else {
                $connector = 'AND';
            }

            foreach ($array as $column => $keyword) {
                $keyword = is_array($keyword) ? $keyword : [$keyword];

                foreach ($keyword as $key) {
                    //TODO change behavior
                    preg_match('/(%?)([a-zA-Z0-9_\-\.]*)(%?)((\[!\])?)/', $column, $columnMatch);

                    if ($columnMatch[1] === '' && $columnMatch[3] === '') {
                        $columnMatch[1] = '%';
                        $columnMatch[3] = '%';
                    }

                    $clauseWrap[] = $this->query->wrapColumn($columnMatch[2]).
                    ($columnMatch[4] !== '' ? 'NOT' : '').' LIKE '.
                    $this->query->wrapValue($columnMatch[1].$key.$columnMatch[3]);
                }
            }

            return ($whereClause !== '' ? ' AND ' : ' WHERE ')."({implode($clauseWrap, ' '.$connector.' ')})";
        }
    }

    /**
     * Where MATCH
     *
     * @param  array  $array
     * @param  string $whereClause
     * @return string
     */
    protected function whereMatch(array $array, $whereClause)
    {
        if (is_array($array) && isset($array['columns'], $array['keyword'])) {

            $columns = $this->query->wrapValue(str_replace('.', '`.`', implode($array['columns'], '", "')));
            $keyword = $this->query->wrapValue($array['keyword']);

            return ($whereClause !== '' ? ' AND ' : ' WHERE ')." MATCH ({$columns}) AGAINST ({$keyword})";
        }
    }

    /**
     * Data implode
     *
     * @param  array  $data
     * @param  string $conjunctor
     * @return string
     */
    public function whereDataImplode(array $data, $conjunctor)
    {
        $wheres = [];

        foreach ($data as $key => $value) {
            $type = gettype($value);

            if (preg_match("/^(AND|OR)\s*#?/i", $key, $relationMatch) && $type == 'array') {

                $wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ?
                "({$this->whereDataImplode($value, ' ' . $relationMatch[1])})" :
                "({$this->innerConjunct($value, ' ' . $relationMatch[1], $conjunctor)})";

            } else {
                preg_match('/(#?)([\w\.]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<)\])?/i', $key, $match);
                $column = $this->query->wrapValue($match[2]);

                if (isset($match[4])) {
                    if ($match[4] === '!') {
                        $wheres[] = $this->whereDataImplodeSwitch($column, $value, $type, $key, true);
                    } else {
                        if ($match[4] === '<>' || $match[4] === '><') {
                            if ($type === 'array') {
                                if ($match[4] === '><') {
                                    $column .= ' NOT';
                                }

                                if (is_numeric($value[0]) && is_numeric($value[1])) {
                                    $wheres[] = "({$column} BETWEEN {$value[0]} AND {$value[1]})";
                                } else {
                                    $wheres[] = "({$column} BETWEEN {$this->query->wrapValue($value[0])} AND 
                                        {$this->query->wrapValue($value[1])})";
                                }
                            }
                        } else {
                            if (is_numeric($value)) {
                                $wheres[] = "{$column} {$match[4]} {$value}";
                            } else {
                                if ($datetime = strtotime($value)) {
                                    $date = date($this->query->getDateFormat(), $datetime);
                                    $wheres[] = "{$column} {$match[4]} {$this->query->wrapValue($date)}";
                                } else {
                                    if (strpos($key, '#') === 0) {
                                        $wheres[] =
                                        "{$column} {$match[4]} {$this->query->wrapFunctionName($key, $value)}";
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (is_int($key)) {
                        $wheres[] = $this->query->wrapValue($value);
                    } else {
                        $wheres[] = $this->whereDataImplodeSwitch($column, $value, $type, $key);
                    }
                }
            }
        }

        return implode($conjunctor . ' ', $wheres);
    }

    /**
     * Join array elements with a string
     *
     * @param  string $data
     * @param  string $conjunctor
     * @param  string $outerConjunctor
     * @return string
     */
    protected function innerConjunct(array $data, $conjunctor, $outerConjunctor)
    {
        $haystack = [];

        foreach ($data as $value) {
            $haystack[] = "({$this->whereDataImplode($value, $conjunctor)})";
        }

        return implode($outerConjunctor.' ', $haystack);
    }

    /**
     * [whereDataImplodeSwitch description]
     *
     * @param  string  $column
     * @param  array   $value
     * @param  string  $type
     * @param  boolean $not
     * @return string
     */
    protected function whereDataImplodeSwitch($column, array $value, $type, $key = null, $not = false)
    {
        switch ($type) {
            case 'NULL':
                $operator = ($not ? 'IS NOT NULL' : 'IS NULL');
                return "{$column} {$operator}";
                break;

            case 'array':
                $operator = ($not ? 'NOT IN' : 'IN');
                return "{$column} {$operator} ({$this->query->wrapArray($value)})";
                break;

            case 'integer':
            case 'double':
                $operator = ($not ? '!=' : '=');
                return "{$column} {$operator} {$value}";
                break;

            case 'boolean':
                $operator = ($not ? '!=' : '=');
                return "{$column} {$operator} {($value ? '1' : '0')}";
                break;

            case 'string':
                $operator = ($not ? '!=' : '=');
                return "{$column} {$operator} {$this->query->wrapFunctionName($key, $value)}";
                break;

            case 'object':
                return "{$column} = {$value->query}";
                break;
        }
    }
}
