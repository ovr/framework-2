<?php
namespace Brainwave\Database\Connection;

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

use \PDO;
use \Brainwave\Support\Str;
use \Brainwave\Support\Arr;
use \Brainwave\Cache\CacheManager;
use \Brainwave\Database\Grammar\Builder;
use \Brainwave\Database\Exception\ConnectException;
use \Brainwave\Database\Connection\Interfaces\ConnectionInterface;

/**
 * Connection
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class Connection implements ConnectionInterface
{
    /**
     * The active PDO connection.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The query grammar implementation.
     *
     * @var \Brainwave\Database\Grammar\Builder
     */
    protected $queryGrammar;

    /**
     * The cache manager instance.
     *
     * @var \Brainwave\Cache\CacheManager|\Closure
     */
    protected $cache;

    /**
     * The default fetch mode of the connection.
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_ASSOC;

    /**
     * Indicates if the connection is in a "dry run".
     *
     * @var bool
     */
    protected $pretending = false;

    /**
     * The number of active transactions.
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * All of the queries run against the connection.
     *
     * @var array
     */
    protected $queryLog = [];

    /**
     * Indicates whether queries are being logged.
     *
     * @var bool
     */
    protected $loggingQueries = true;

    /**
     * The name of the connected database.
     *
     * @var string
     */
    protected $database;

    /**
     * The table prefix for the connection.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * The table prefix for query.
     *
     * @var array
     */
    protected $alias = [];

    /**
     * The reconnector instance for the connection.
     *
     * @var callable
     */
    protected $reconnector;

    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Create a new database connection instance.
     *
     * @param  \PDO     $pdo
     * @param  string   $database
     * @param  string   $tablePrefix
     * @param  array    $config
     * @return void
     */
    public function __construct(
        PDO $pdo,
        $database = '',
        $tablePrefix = '',
        array $config = []
    ) {
        $this->pdo = $pdo;

        // First we will setup the default properties. We keep track of the DB
        // name we are connected to since it is needed when some reflective
        // type are run such as checking whether a table exists.
        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;
    }

    /**
     * Create a alias for tableprefix in query
     *
     * @param  string $table table name
     * @param  string $alias alias name
     * @return boolean|null
     */
    public function setAlias($table, $alias)
    {
        $this->alias[$table] = $alias;
    }

    /**
     * Get alias for table
     *
     * @param  string $table
     * @return string|array
     */
    public function getAlias($table)
    {
        $alias = array_filter($this->alias[$table]);

        return (empty($alias)) ? '' : $alias;
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Brainwave\Database\Exception\ConnectException
     */
    public function run($query, $bindings, \Closure $callback)
    {
        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        // Here we will run this query. If an exception occurs we'll determine if it was
        // caused by a connection that has been lost. If that is the cause, we'll try
        // to re-establish connection and re-run the query with a fresh connection.
        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (ConnectException $e) {
            $result = $this->tryAgainIfCausedByLostConnection(
                $e,
                $query,
                $bindings,
                $callback
            );
        }

        // Once we have run the query we will calculate the time that it took to run and
        // then log the query, bindings, and execution time so we will report them on
        // the event that the developer needs them. We'll log time in milliseconds.
        $time = $this->getElapsedTime($start);

        $this->logQuery($query, $bindings, $time);

        return $result;
    }

    /**
     * Run a SQL statement.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Brainwave\Database\Exception\ConnectException
     */
    protected function runQueryCallback($query, $bindings, \Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL and time in our memory.
        try {
            $result = $callback($this, $query, $bindings);

            // If an exception occurs when attempting to run a query, we'll format the error
            // message, which will make this exception a lot more helpful to the developer
            // instead of just the database's errors.
        } catch (\Exception $e) {
            throw new ConnectException(
                $query,
                $this->prepareBindings($bindings),
                $e
            );
        }

        return $result;
    }

    /**
     * Handle a query exception that occurred during query execution.
     *
     * @param  \Brainwave\Database\Exception\ConnectException  $e
     * @param  string    $query
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Brainwave\Database\Exception\ConnectException
     */
    protected function tryAgainIfCausedByLostConnection(
        ConnectException $e,
        $query,
        $bindings,
        \Closure $callback
    ) {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();

            return $this->runQueryCallback($query, $bindings, $callback);
        }

        throw $e;
    }

    /**
     * Determine if the given exception was caused by a lost connection.
     *
     * @param  \Brainwave\Database\Exception\ConnectException
     * @return bool
     */
    protected function causedByLostConnection(ConnectException $e)
    {
        return Str::contains($e->getPrevious()->getMessage(), 'server has gone away');
    }

    /**
     * Disconnect from the underlying PDO connection.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->setPdo(null);
    }

    /**
     * Set the reconnect instance on the connection.
     *
     * @param  callable  $reconnector
     * @return $this
     */
    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;

        return $this;
    }

    /**
     * Reconnect to the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            return call_user_func($this->reconnector, $this);
        }

        throw new \LogicException("Lost connection and no reconnector available.");
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     *
     * @return void
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->getPdo())) {
            $this->reconnect();
        }
    }

    /**
         * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(\Closure $callback)
    {
        $this->beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.
        try {
            $result = $callback($this);

            $this->commit();

            // If we catch an exception, we will roll back so nothing gets messed
            // up in the database. Then we'll re-throw the exception so it can
            // be handled how the developer sees fit for their applications.
        } catch (\Exception $e) {
            $this->rollBack();

            throw $e;
        }

        return $result;
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        ++$this->transactions;

        if ($this->transactions == 1) {
            $this->pdo->beginTransaction();
        }
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactions == 1) {
            $this->pdo->commit();
        }

        --$this->transactions;
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactions == 1) {
            $this->transactions = 0;

            $this->pdo->rollBack();
        } else {
            --$this->transactions;
        }
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }

    /**
     * Get the default fetch mode for the connection.
     *
     * @return int
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * Set the default fetch mode for the connection.
     *
     * @param  int  $fetchMode
     * @return int
     */
    public function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;
    }

    /**
     * Set the PDO connection.
     *
     * @param  \PDO|null  $pdo
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Get the query grammar used by the connection.
     *
     * @return \Brainwave\Database\Grammar\Builder
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * Set the query grammar used by the connection.
     *
     * @param  \Brainwave\Database\Grammar\Builder
     * @return void
     */
    public function setQueryGrammar(Builder $grammar)
    {
        $this->queryGrammar = $grammar;
    }

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * Get an option from the configuration options.
     *
     * @param  string  $option
     * @return mixed
     */
    public function getConfig($option)
    {
        return Arr::arrayGet($this->config, $option);
    }

    /**
     * Get the PDO driver name.
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(\Closure $callback)
    {
        $this->pretending = true;

        $this->queryLog = array();

        // Basically to make the database connection "pretend", we will just return
        // the default values for all the query methods, then we will return an
        // array of queries that were "executed" within the Closure callback.
        $callback($this);

        $this->pretending = false;

        return $this->queryLog;
    }

    /**
     * Determine if the connection in a "dry run".
     *
     * @return bool
     */
    public function pretending()
    {
        return $this->pretending === true;
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of the DateTime class into an actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof \DateTime) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif ($value === false) {
                $bindings[$key] = 0;
            }
        }

        return $bindings;
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function statement($query, $bindings = array())
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $bindings = $this->prepareBindings($bindings);

            return $this->getPdo()->prepare($query)->execute($bindings);
        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = array())
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
            $statement = $this->getPdo()->prepare($query);

            $statement->execute($this->prepareBindings($bindings));

            return $statement->rowCount();
        });
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @param  double  $time
     * @return void
     */
    public function logQuery($query, $bindings, $time = null)
    {
        if (!$this->loggingQueries) {
            return;
        }

        if ($time === null) {
            $date = new \DateTime();
            $query = $date->format("Y-m-d H:i:s:u") . $query;
        }

        $this->queryLog[] = compact('query', 'bindings', 'time');
    }

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     *
     * @return void
     */
    public function flushQueryLog()
    {
        $this->queryLog = array();
    }

    /**
     * Enable the query log on the connection.
     *
     * @return void
     */
    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }

    /**
     * Disable the query log on the connection.
     *
     * @return void
     */
    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public function logging()
    {
        return $this->loggingQueries;
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->database;
    }

    /**
     * Set the name of the connected database.
     *
     * @param  string  $database
     * @return string
     */
    public function setDatabaseName($database)
    {
        $this->database = $database;
    }

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the table prefix in use by the connection.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        $this->getQueryGrammar()->setTablePrefix($prefix);
    }

    /**
     * Get the cache manager instance.
     *
     * @return \Brainwave\Cache\CacheManager|\Closure
     */
    public function getCacheManager()
    {
        if ($this->cache instanceof \Closure) {
            $this->cache = call_user_func($this->cache);
        }

        return $this->cache;
    }

    /**
     * Set the cache manager instance on the connection.
     *
     * @param  \Brainwave\Cache\CacheManager|\Closure  $cache
     * @return void
     */
    public function setCacheManager($cache)
    {
        if ($cache instanceof CacheManager || $cache instanceof \Closure) {
            $this->cache = $cache;
        }
    }

    /**
     * Get the elapsed time since a given starting point.
     *
     * @param  int    $start
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * All infos about \PDO
     *
     * @return string
     */
    public function info()
    {
        ($this->config['driver'] === 'sqlite') ?
        $pdoInfo = [
            'file' => 'SERVER_INFO',
            'file status' => 'CONNECTION_STATUS'
        ] :
        $pdoInfo = [
            'server' => 'SERVER_INFO',
            'connection' => 'CONNECTION_STATUS'
        ];

        $output = [
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
        ];

        $output = array_merge($pdoInfo, $output);

        foreach ($output as $key => $value) {
            $output[$key] = $this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
        }

        return $output;
    }
}
