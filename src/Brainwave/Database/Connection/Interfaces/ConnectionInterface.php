<?php
namespace Brainwave\Database\Connection\Interfaces;

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

/**
 * ConnectionInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
interface ConnectionInterface
{
    /**
     * Create a alias for tableprefix in query
     *
     * @param  string $table table name
     * @param  string $alias alias name
     * @return bool
     */
    public function setAlias($table, $alias);

    /**
     * Get alias for table
     *
     * @param  string $table
     * @return string|array
     */
    public function getAlias($table);

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
    public function run($query, $bindings, \Closure $callback);

    /**
     * Reconnect to the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function reconnect();

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(\Closure $callback);

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit();

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack();

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel();

    /**
     * Set the PDO connection.
     *
     * @param  \PDO|null  $pdo
     * @return $this
     */
    public function setPdo($pdo);

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo();

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get an option from the configuration options.
     *
     * @param  string  $option
     * @return mixed
     */
    public function getConfig($option);

    /**
     * Get the PDO driver name.
     *
     * @return string
     */
    public function getDriverName();

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(\Closure $callback);

    /**
     * Determine if the connection in a "dry run".
     *
     * @return bool
     */
    public function pretending();

    /**
     * Log a query in the connection's query log.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @param  $time
     * @return void
     */
    public function logQuery($query, $bindings, $time = null);

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog();

    /**
     * Clear the query log.
     *
     * @return void
     */
    public function flushQueryLog();

    /**
     * Enable the query log on the connection.
     *
     * @return void
     */
    public function enableQueryLog();

    /**
     * Disable the query log on the connection.
     *
     * @return void
     */
    public function disableQueryLog();

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public function logging();

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName();

    /**
     * Set the name of the connected database.
     *
     * @param  string  $database
     * @return string
     */
    public function setDatabaseName($database);

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix();

    /**
     * Set the table prefix in use by the connection.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setTablePrefix($prefix);

    /**
     * Get the cache manager instance.
     *
     * @return \Brainwave\Cache\CacheManager|\Closure
     */
    public function getCacheManager();

    /**
     * Set the cache manager instance on the connection.
     *
     * @param  \Brainwave\Cache\CacheManager|\Closure  $cache
     * @return void
     */
    public function setCacheManager($cache);
}
