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
use \PDOException;
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
    protected $pdo;

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
        return ' "' . str_replace('.', '

            "."', preg_replace('/(^#|\(JSON\))/', '', $string)) . '" ';
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

    protected function arrayQuote($array)
    {
        $temp = array();

        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : $this->pdo->quote($value);
        }

        return implode($temp, ',');
    }

    protected function innerConjunct($data, $conjunctor, $outerConjunctor)
    {
        $haystack = array();

        foreach ($data as $value) {
            $haystack[] = '(' . $this->dataImplode($value, $conjunctor) . ')';
        }

        return implode($outerConjunctor . ' ', $haystack);
    }

    protected function fnQuote($column, $string)
    {
        return (strpos($column, '#') === 0 && preg_match('/^[A-Z0-9\_]*\([^)]*\)$/', $string)) ?

            $string :

            $this->quote($string);
    }

    /**
     * [dataImplode description]
     * @param  [type] $part      [description]
     * @param  [type] $separator [description]
     * @return [type]            [description]
     */
    public function dataImplode($data, $conjunctor, $outerConjunctor = null)
    {
        $wheres = array();

        foreach ($data as $key => $value) {
            $type = gettype($value);

            if (
                preg_match("/^(AND|OR)\s*#?/i", $key, $relation_match) &&
                $type == 'array'
            ) {
                $wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ?
                    '(' . $this->dataImplode($value, ' ' . $relation_match[1]) . ')' :
                    '(' . $this->inner_conjunct($value, ' ' . $relation_match[1], $conjunctor) . ')';
            } else {
                preg_match('/(#?)([\w\.]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<)\])?/i', $key, $match);
                $column = $this->column_quote($match[2]);

                if (isset($match[4])) {
                    if ($match[4] == '!') {
                        switch ($type)
                        {
                            case 'NULL':
                                $wheres[] = $column . ' IS NOT NULL';
                                break;

                            case 'array':
                                $wheres[] = $column . ' NOT IN (' . $this->array_quote($value) . ')';
                                break;

                            case 'integer':
                            case 'double':
                                $wheres[] = $column . ' != ' . $value;
                                break;

                            case 'boolean':
                                $wheres[] = $column . ' != ' . ($value ? '1' : '0');
                                break;

                            case 'string':
                                $wheres[] = $column . ' != ' . $this->fnQuote($key, $value);
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
                    } else {
                        if ($match[4] == '<>' || $match[4] == '><') {
                            if ($type == 'array') {
                                if ($match[4] == '><') {
                                    $column .= ' NOT';
                                }

                                if (is_numeric($value[0]) && is_numeric($value[1])) {
                                    $wheres[] = '(' . $column . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
                                } else {
                                    $wheres[] = '(' . $column . ' BETWEEN ' .
                                        $this->quote($value[0]) . ' AND ' . $this->quote($value[1]) . ')';
                                }
                            }
                        } else {
                            if (is_numeric($value)) {
                                $wheres[] = $column . ' ' . $match[4] . ' ' . $value;
                            } else {
                                $datetime = strtotime($value);

                                if ($datetime) {
                                    $wheres[] = $column . ' ' . $match[4] . ' ' .
                                    $this->quote(date('Y-m-d H:i:s', $datetime));
                                } else {
                                    if (strpos($key, '#') === 0) {
                                        $wheres[] = $column . ' ' . $match[4] . ' ' . $this->fnQuote($key, $value);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (is_int($key)) {
                        $wheres[] = $this->quote($value);
                    } else {
                        switch ($type) {
                            case 'NULL':
                                $wheres[] = $column . ' IS NULL';
                                break;

                            case 'array':
                                $wheres[] = $column . ' IN (' . $this->array_quote($value) . ')';
                                break;

                            case 'integer':
                            case 'double':
                                $wheres[] = $column . ' = ' . $value;
                                break;

                            case 'boolean':
                                $wheres[] = $column . ' = ' . ($value ? '1' : '0');
                                break;

                            case 'string':
                                $wheres[] = $column . ' = ' . $this->fnQuote($key, $value);
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
                }
            }
        }

        return implode($conjunctor . ' ', $wheres);
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
            $whereKeys = array_keys($where);
            $whereAND = preg_grep("/^AND\s*#?$/i", $whereKeys);
            $whereOR = preg_grep("/^OR\s*#?$/i", $whereKeys);

            $single_condition = array_diff_key($where, array_flip(
                explode(' ', 'AND OR GROUP ORDER HAVING LIMIT LIKE MATCH')
            ));

            if ($single_condition != array()) {
                $whereClause = ' WHERE ' . $this->dataImplode($single_condition, '');
            }

            if (!empty($whereAND)) {
                $value = array_values($whereAND);
                $whereClause = ' WHERE ' . $this->dataImplode($where[ $value[0] ], ' AND');
            }

            if (!empty($whereOR)) {
                $value = array_values($whereOR);
                $whereClause = ' WHERE ' . $this->dataImplode($where[ $value[0] ], ' OR');
            }

            if (isset($where['LIKE'])) {
                $LIKE = $where['LIKE'];

                if (is_array($LIKE)) {
                    $is_OR = isset($LIKE['OR']);
                    $clause_wrap = array();

                    if ($is_OR || isset($LIKE['AND'])) {
                        $connector = $is_OR ? 'OR' : 'AND';
                        $LIKE = $is_OR ? $LIKE['OR'] : $LIKE['AND'];
                    } else {
                        $connector = 'AND';
                    }

                    foreach ($LIKE as $column => $keyword) {
                        $keyword = is_array($keyword) ? $keyword : array($keyword);

                        foreach ($keyword as $key) {
                            preg_match('/(%?)([a-zA-Z0-9_\-\.]*)(%?)((\[!\])?)/', $column, $column_match);

                            if ($column_match[1] == '' && $column_match[3] == '') {
                                $column_match[1] = '%';
                                $column_match[3] = '%';
                            }

                            $clause_wrap[] =
                                $this->column_quote($column_match[2]) .
                                ($column_match[4] != '' ? ' NOT' : '') . ' LIKE ' .
                                $this->quote($column_match[1] . $key . $column_match[3]);
                        }
                    }

                    $whereClause .= ($whereClause != '' ? ' AND ' : ' WHERE ') .
                    '(' . implode($clause_wrap, ' ' . $connector . ' ') . ')';
                }
            }

            if (isset($where['MATCH'])) {
                $MATCH = $where['MATCH'];

                if (is_array($MATCH) && isset($MATCH['columns'], $MATCH['keyword'])) {
                    $whereClause .= ($whereClause != '' ? ' AND ' : ' WHERE ') .
                    ' MATCH ("' . str_replace('.', '"."', implode($MATCH['columns'], '", "')) . '") AGAINST (' .
                        $this->quote($MATCH['keyword']) . ')';
                }
            }

            if (isset($where['GROUP'])) {
                $whereClause .= ' GROUP BY ' . $this->column_quote($where['GROUP']);
            }

            if (isset($where['ORDER'])) {
                $rsort = '/(^[a-zA-Z0-9_\-\.]*)(\s*(DESC|ASC))?/';
                $ORDER = $where['ORDER'];

                if (is_array($ORDER)) {
                    if (
                        isset($ORDER[1]) &&
                        is_array($ORDER[1])
                    ) {
                        $whereClause .= ' ORDER BY FIELD(' . $this->column_quote($ORDER[0]) .
                            ', ' . $this->array_quote($ORDER[1]) . ')';
                    } else {
                        $stack = array();

                        foreach ($ORDER as $column) {
                            preg_match($rsort, $column, $order_match);

                            array_push(
                                $stack,
                                '"' . str_replace('.', '"."', $order_match[1]) . '"' .
                                (isset($order_match[3]) ? ' ' . $order_match[3] : '')
                            );
                        }

                        $whereClause .= ' ORDER BY ' . implode($stack, ',');
                    }
                } else {
                    preg_match($rsort, $ORDER, $order_match);

                    $whereClause .= ' ORDER BY "' .
                    str_replace('.', '"."', $order_match[1]) . '"' .
                    (isset($order_match[3]) ? ' ' .
                    $order_match[3] : '');
                }

                if (isset($where['HAVING'])) {
                    $whereClause .= ' HAVING ' . $this->dataImplode($where['HAVING'], '');
                }
            }

            if (isset($where['LIMIT'])) {
                $LIMIT = $where['LIMIT'];

                if (is_numeric($LIMIT)) {
                    $whereClause .= ' LIMIT ' . $LIMIT;
                }

                if (
                    is_array($LIMIT) &&
                    is_numeric($LIMIT[0]) &&
                    is_numeric($LIMIT[1])
                ) {
                    $whereClause .= ' LIMIT ' . $LIMIT[0] . ',' . $LIMIT[1];
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
     * [setQueryString description]
     * @param [type] $query [description]
     */
    public function setQueryString($query)
    {
        $this->queryString = $query;
    }

    /**
     * [getPdo description]
     * @return [type] [description]
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function beginTransaction()
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->beginTransaction();
        }
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function rollback()
    {
        if ($this->pdo->inTransaction()) {
            try {
                return $this->pdo->rollBack();
            } catch (PDOException $ex) {
                echo $ex->getMessage();
                return false;
            }
        }
    }

    /**
     * [beginTransaction description]
     * @return [type] [description]
     */
    public function commit()
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->commit();
        }
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
     * [debugPDO description]
     * @param  [type] $raw_sql    [description]
     * @param  [type] $parameters [description]
     * @return [type]             [description]
     */
    public function debugPDO($rawSql, $parameters)
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

        $rawSql = preg_replace($keys, $values, $rawSql, 1, $count);

        return $rawSql;
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
