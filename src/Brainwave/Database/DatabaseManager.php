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

use \PDO;
use \Predis\Client;

/**
 * DatabaseManager
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class DatabaseManager
{

    /**
     * DB connections
     * To avoid multiple connection to same database
     * @var array
     */
    static private $connections = [];

    /**
     * DB type
     * @var string
     */
    protected $databaseType;

    /**
     * [$pdo description]
     * @var [type]
     */
    public $pdo;

    /**
     * DB server address
     * For MySQL, MariaDB, MSSQL, Sybase, PostgreSQL, Oracle, Google Cloud SQL
     * @var string
     */
    protected $server;

     /**
     * DB username
     * @var string
     */
    protected $username;

    /**
     * DB password
     * @var string
     */
    protected $password;

    /**
     * SQLite File
     * @var string
     */
    protected $databaseFile;

    /**
     * DB port
     * @var int
     */
    protected $port;

    /**
     * DB encoding
     * @var string
     */
    protected $charset;

    /**
     * DB NAME
     * @var string
     */
    protected $databaseName;

    /**
     * For debug
     * @var string
     */
    protected $logQueries = true;

    /**
     * [$queryHistory description]
     * @var [type]
     */
    public $queryHistory = [];

    /**
     * Variable
     * @var string
     */
    protected $queryString;

    /**
     * [$AGGREGATIONS description]
     * @var array
     */
    private static $AGGREGATIONS = ['AVG','SUM','MIN','COUNT','MAX'];

    /**
     * All settings for PDO
     * @param array $options
     */
    public function __construct($options = null)
    {
        if (isset(self::$connections[$this->databaseName])) {
            return true;
        }

        try {
            $commands = [];

            if (is_string($options) && !empty($options)) {
                if (strtolower($this->databaseType) == 'sqlite') {
                    $this->databaseFile = $options['dbname'];
                } else {
                    $this->databaseName = $options['dbname'];
                }
            }

            $this->databaseType = $options['type'];
            $this->port = $options['port'];
            $this->charset = (!empty($options['charset'])) ? $options['charset'] : $app['settings']['charset'];

            if (!empty($options['server'])) {
                $this->server = $options['server'];
            }
            if (!empty($options['username'])) {
                $this->username = $options['username'];
            }
            if (!empty($options['password'])) {
                $this->password = $options['password'];
            }

            if (isset($this->port) && is_int($this->port * 1)) {
                $port = $this->port;
            }

            $set_charset = "SET NAMES '" . $this->charset . "'";
            $type = strtolower($this->databaseType);
            $is_port = isset($port);

            switch ($type)
            {
                case 'mariadb':
                    $type = 'mysql';
                    //no break
                case 'mysql':
                    // Make MySQL using standard quoted identifier
                    $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                    //no break
                case 'cloudsql':
                    $dsn = 'mysql:unix_socket=/cloudsql/' . $this->server . ';dbname=' . $this->databaseName;
                    $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                    $commands[] = $set_charset;
                    break;
                case 'pgsql':
                    $dsn = $type . ':host=' . $this->server.
                    ($is_port ? ';port=' . $port : '') . ';dbname=' . $this->databaseName;
                    $commands[] = $set_charset;
                    break;
                case 'sybase':
                    $dsn = 'dblib:host=' . $this->server.
                    ($is_port ? ':' . $port : '') . ';dbname=' . $this->databaseName;
                    $commands[] = $set_charset;
                    break;
                case 'oracle':
                    $dsn = 'oci:dbname=//' . $this->server.
                    ($is_port ? ':' . $port : ':1521').'/'.$this->databaseName.';charset=' . $this->charset;
                    break;
                case 'mssql':
                    $dsn = strpos(PHP_OS, 'WIN') !== false ?
                        'sqlsrv:server=' . $this->server . ($is_port ? ',' . $port : '').
                        ';database=' . $this->databaseName :
                        'dblib:host=' . $this->server . ($is_port ? ':' . $port : '').
                        ';dbname=' . $this->databaseName;

                    // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                    $commands[] = 'SET QUOTED_IDENTIFIER ON';
                    $commands[] = $set_charset;
                    break;
                case 'sqlite':
                    $dsn = $type . ':' . $this->databaseFile;
                    $this->username = null;
                    $this->password = null;
                    break;
            }

            $this->pdo = new PDO(
                $dsn,
                $this->username,
                $this->password,
                $options['option']
            );

            foreach ($commands as $value) {
                $this->pdo->exec($value);
            }
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * [exec description]
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function exec($query)
    {
        $this->queryString = $query;
        $this->addQueryToHistory($query);

        return $this->pdo->exec($query);
    }

    /**
     * [quote description]
     * @param  [type]  $string      [description]
     * @param  boolean $is_function [description]
     * @return [type]               [description]
     */
    public function quote($string, $is_function = false)
    {
        return $is_function ? $string : $this->pdo->quote($string);
    }

    /**
     * [columnQuote description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    protected function columnQuote($string)
    {
        return ' "' . str_replace('.', '"."', preg_replace('/(^#|\(JSON\))/', '', $string)) . '" ';
    }

    /**
     * [columnPush description]
     * @param  [type] $columns [description]
     * @return [type]          [description]
     */
    protected function columnPush($columns)
    {
        if ($columns == '*') {
            return $columns;
        }

        if (is_string($columns) || is_int($columns)) {
            $columns = [(string) $columns];
        }

        $stack = [];

        foreach ($columns as $key => $value) {
            preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-\*]*)\)/i', $value, $match);

            if (isset($match[1], $match[2])) {
                if (in_array(strtoupper($match[1]), self::$AGGREGATIONS)) {
                    array_push(
                        $stack,
                        $match[1]  . '(' .( $match[2] == '*' ?
                        $match[2] :
                        $this->columnQuote($match[2])) . ')'
                    );
                } else {
                    array_push(
                        $stack,
                        $this->columnQuote($match[1]) . ' AS ' . $this->columnQuote($match[2])
                    );
                }
            } else {
                if ($value == "1") {
                    array_push($stack, $value);
                } else {
                    array_push($stack, $this->columnQuote($value));
                }
            }
        }

        return implode($stack, ',');
    }

    /**
     * [dataImplode description]
     * @param  [type] $part      [description]
     * @param  [type] $separator [description]
     * @return [type]            [description]
     */
    public function dataImplode($part, $separator = null)
    {
        $result = [];
        $separator = isset($separator) ? trim($separator): $separator;

        if (is_array($part)) {
            // boolean block and value lists
            foreach ($part as $key => $value) {
                $key = is_string($key) ? trim($key) : $key;
                if ($key === 'ORDER' || $key === 'GROUP' ||$key === 'HAVING'
                 ||$key === 'LIMIT' ||$key === 'LIKE' ||$key === 'MATCH' ) {
                    break;
                } elseif ($key === 'AND' || $key === 'OR') {
                    if (isset($separator)) {
                        $result[] = ' ('.$this->dataImplode($value, $key). ') ';
                    } else {
                        $result[] = $this->dataImplode($value, $key);
                    }
                } elseif (is_int($key)) {
                    if ($separator === 'OR') {
                        $result[] = ' ('.$this->dataImplode($value, 'AND'). ') ';
                    } elseif ($separator === 'AND') {
                        $result[] = ' ('.$this->dataImplode($value, 'OR'). ') ';
                    } else {
                        $result[] = $this->dataImplode($value, ',') ;
                    }
                } else {
                    // not a key less array or a boolean block
                    $result[] = $this->getTerm($key, $value);
                }
            }
        } else {
            // single value
            $result[] = $this->quote($part);
        }
         return str_replace("  ", " ", implode((isset($separator) ? $separator : 'AND'), $result));
    }

    /**
     * [getTerm description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    private function getTerm($key, $value)
    {
        $not = '';
        preg_match('/(#?)([\w\.])(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<)\])?/i', $key, $match);

        $is_function = isset($match[3]);

        if (isset($match[4])) {
            if ($match[4] == '') {
                return $this->columnQuote($key).'='.$this->quote($value, $is_function);
            } else {
                // not block
                if ($match[4] == '!') {
                    switch (gettype($value)) {
                        case 'NULL':
                        case 'array':
                            $not = 'NOT ';
                            break;
                        case 'integer':
                        case 'double':
                        case 'string':
                            $not = '!';
                            break;
                    }
                }
            }
        }

        switch (gettype($value)){
            case 'NULL':
                return $this->columnQuote($key).' IS '.$not.$this->quote($value, $is_function);
                break;
            case 'array':
                if (isset($match[4]) && $match[3] === '<>' && count($value) == 2) {
                    return ' ('.$this->columnQuote($match[1]).' BETWEEN '.
                    $this->quote(
                        $value[0],
                        $is_function
                    ).' AND '.$this->quote($value[1], $is_function).') ';
                } else {
                    return $this->columnQuote($match[1]).$not.' IN ('.$this->dataImplode($value, ',').') ';
                }
                break;
            case 'string':
                // for the date feature you need a condition (e.g. [=] or [!=])
                if (isset($match[3])) {
                    $datetime = strtotime($value);
                    if ($datetime) {
                        $value = date('Y-m-d H:i:s', $datetime);
                    }
                }
                //no break
            case 'integer':
            case 'double':
                if (isset($match[4]) && $match[4] !== '!') {
                    return $this->columnQuote($match[1]).' '.$match[4].' '.
                    $this->quote($value, $is_function).' ';
                } else {
                    return $this->columnQuote(
                        $match[1]
                    ).$not.'= '.$this->quote($value, $is_function).' ';
                }
                break;
            case 'boolean':
                $wheres[] = $column . ' = ' . ($value ? '1' : '0');
                break;

            /*
             * Adding option for Select Query to be used in Where Statement
             * Example
             *
             * "AND" => [
             *      "employees.company" => (object)['query'=>"(SELECT id FROM companies WHERE email = '".$email."')"],
             * ],
             *
             * Assuming in this example that companies.email is an unique field
             */
            case 'object':
                $wheres[] = $column . ' = ' . $value->query;
                break;
        }

        throw new \Exception('Unknown term type');
    }

    /**
     * [whereClause description]
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    protected function whereClause($where)
    {
        $whereClause = '';

        if (is_array($where)) {
            $single_condition = array_diff_key($where, array_flip(
                explode(' ', 'AND OR GROUP ORDER HAVING LIMIT LIKE MATCH')
            ));

            if ($single_condition != []) {
                $whereClause = ' WHERE ' . $this->dataImplode($single_condition, '');
            }

            if (isset($where['AND'])) {
                $whereClause = ' WHERE ' . $this->dataImplode($where['AND'], 'AND ');
            }

            if (isset($where['OR'])) {
                $whereClause = ' WHERE ' . $this->dataImplode($where['OR'], 'OR ');
            }

            if (isset($where['LIKE'])) {
                $like_query = $where['LIKE'];
                if (is_array($like_query)) {
                    $is_OR = isset($like_query['OR']);

                    if ($is_OR || isset($like_query['AND'])) {
                        $connector = $is_OR ? 'OR' : 'AND';
                        $like_query = $is_OR ? $like_query['OR'] : $like_query['AND'];
                    } else {
                        $connector = 'AND';
                    }

                    $clause_wrap = [];
                    foreach ($like_query as $column => $keyword) {
                        if (is_array($keyword)) {
                            foreach ($keyword as $key) {
                                $clause_wrap[] = $this->columnQuote($column).' LIKE '.
                                $this->quote('%' . $key . '%');
                            }
                        } else {
                            $clause_wrap[] = $this->columnQuote($column).' LIKE '.
                            $this->quote('%' . $keyword . '%');
                        }
                    }
                    $whereClause .= (
                        $whereClause != '' ?
                        ' AND ' :
                        ' WHERE ') . '(' . implode($clause_wrap, ' ' . $connector . ' ') . ')';
                }
            }

            if (isset($where['MATCH'])) {
                $match_query = $where['MATCH'];
                if (
                    is_array($match_query) &&
                    isset($match_query['columns']) &&
                    isset($match_query['keyword'])
                ) {
                    $whereClause .= (
                        $whereClause != '' ?
                        ' AND ' :
                        ' WHERE ').' MATCH ("' . str_replace(
                            '.',
                            '"."',
                            implode($match_query['columns'], '", "')
                        ) . '") AGAINST (' . $this->quote($match_query['keyword']) . ')';
                }
            }
            if (isset($where['GROUP'])) {
                $whereClause .= ' GROUP BY ' . $this->columnQuote($where['GROUP']);

                if (isset($where['HAVING'])) {
                    $whereClause .= ' HAVING ' . $this->dataImplode($where['HAVING'], '');
                }
            }

            if (isset($where['ORDER'])) {

                $whereClause .= ' ORDER BY ';
                $order_by_declaration = $where['ORDER'];

                if (is_string($order_by_declaration)) {
                    $order_by_declaration = explode(',', $where['ORDER']);
                }

                $order = [];

                foreach ($order_by_declaration as $value) {
                    preg_match('/(^[a-zA-Z0-9_\-\.]*)(\s*(DESC|ASC))?/', trim($value), $order_match);

                    $order_by[] = $this->columnQuote($value) .' '. (isset($order_match[3]) ? $order_match[3] : '');
                }

                $whereClause .= implode(' , ', $order_by);
            }

            if (isset($where['LIMIT'])) {
                if (is_numeric($where['LIMIT'])) {
                    $whereClause .= ' LIMIT ' . $where['LIMIT'];
                }

                if (
                    is_array($where['LIMIT']) &&
                    is_numeric($where['LIMIT'][0]) &&
                    is_numeric($where['LIMIT'][1])
                ) {
                    $whereClause .= ' LIMIT ' . $where['LIMIT'][0] . ',' . $where['LIMIT'][1];
                }
            }
        } else {
            if ($where != null) {
                $whereClause .= ' ' . $where;
            }
        }

        return $whereClause;
    }

    /**
     * [selectContext description]
     * @param  [type] $table     [description]
     * @param  [type] $join      [description]
     * @param  [type] $columns   [description]
     * @param  [type] $where     [description]
     * @param  [type] $column_fn [description]
     * @return [type]            [description]
     */
    protected function selectContext($table, $join, &$columns = null, $where = null, $column_fn = null)
    {
        $table = '"' . $table . '"';
        $join_key = is_array($join) ? array_keys($join) : null;

        if (
            isset($join_key[0]) &&
            strpos($join_key[0], '[') === 0
        ) {
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
                            $relation = 'ON '.$table.'."'.key($relation).'" = "'.
                            $match[3] . '"."' . current($relation) . '"';
                        }
                    }

                    $table_join[] = $join_array[$match[2]] . ' JOIN "' . $match[3] . '" ' . $relation;
                }
            }

            $table .= ' ' . implode($table_join, ' ');
        } else {
            if (is_null($columns)) {
                if (is_null($where)) {
                    if (
                        is_array($join) &&
                        isset($column_fn)
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

        if (isset($column_fn)) {
            if ($column_fn == 1) {
                $column = '1';

                if (is_null($where)) {
                    $where = $columns;
                } else {
                    $where = $join;
                }
            } else {
                if (empty($columns)) {
                    $columns = '*';
                    $where = $join;
                }

                $column = $column_fn . '(' . $this->columnPush($columns) . ')';
            }
        } else {
            $column = $this->columnPush($columns);
        }

        return 'SELECT ' . $column . ' FROM ' . $table . $this->whereClause($where);
    }

    /**
     * [lastQuery description]
     * @return [type] [description]
     */
    public function lastQuery()
    {
        return "SET SQL_MODE=ANSI_QUOTES; " .$this->queryString;
    }

    /**
     * [info description]
     * @return [type] [description]
     */
    public function info()
    {
        $output = [
            'server' => 'SERVER_INFO',
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS'
        ];

        foreach ($output as $key => $value) {
            $output[ $key ] = $this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
        }

        return $output;
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function rollback()
    {
        $this->pdo->rollback();
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function error()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * [addQueryToHistory description]
     * @param [type]  $query     [description]
     * @param boolean $timestamp [description]
     */
    protected function addQueryToHistory($query, $timestamp = true)
    {
        if ($this->logQueries == false) {
            return;
        }

        if ($timestamp) {
            $date = new DateTime();
            $query = $date->format("Y-m-d H:i:s:u") . $query;
        }

        $this->queryHistory[] = $query;
    }

    /**
     * [getQueryToHistory description]
     * @return [type] [description]
     */
    public function getQueryToHistory()
    {
        return $this->queryHistory;
    }

    /**
     * [logQueries description]
     * @param  [type] $true [description]
     * @return [type]       [description]
     */
    public function logQueries($true)
    {
        $this->logQueries = $true;
    }

    /**
     * [debugPDO description]
     * @param  [type] $raw_sql    [description]
     * @param  [type] $parameters [description]
     * @return [type]             [description]
     */
    public function debugPDO($raw_sql, $parameters)
    {
        $keys = [];
        $values = $parameters;

        foreach ($parameters as $key => $value) {

            // check if named parameters (':param') or anonymous parameters ('?') are used
            if (is_string($key)) {
                $keys[] = '/:'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }

            // bring parameter into human-readable format
            if (is_string($value)) {
                $values[$key] = "'" . $value . "'";
            } elseif (is_array($value)) {
                $values[$key] = implode(',', $value);
            } elseif (is_null($value)) {
                $values[$key] = 'NULL';
            }
        }

        $raw_sql = preg_replace($keys, $values, $raw_sql, 1, $count);

        return $raw_sql;
    }

    /**
     * [__get description]
     * @param  [type] $prop [description]
     * @return [type]       [description]
     */
    public function __get($prop)
    {
        return $this->$prop;
    }
}
