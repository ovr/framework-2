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

use \Pimple\Container;
use \Brainwave\Support\Arr;
use \Brainwave\Database\Connection\Connection;
use \Brainwave\Database\Connectors\MSSQLConnector;
use \Brainwave\Database\Connectors\MySqlConnector;
use \Brainwave\Database\Connectors\OracleConnector;
use \Brainwave\Database\Connectors\SQLiteConnector;
use \Brainwave\Database\Connectors\SybaseConnector;
use \Brainwave\Database\Connectors\MariaDBConnector;
use \Brainwave\Database\Connectors\SqlServerConnector;
use \Brainwave\Database\Connectors\PostgreSQLConnector;
use \Brainwave\Database\Connectors\GoogleCloudConnector;

/**
 * ConnectionFactory
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class ConnectionFactory
{
   /**
     * The container instance.
     *
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * Create a new connection factory instance.
     *
     * @param  \Pimple\Container $container
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param  array  $config
     * @param  string $name
     *
     * @return Connection
     */
    public function make(array $config, $name = null)
    {
        $config = $this->parseConfig($config, $name);

        return $this->createSingleConnection($config);
    }

    /**
     * Parse and prepare the database configuration.
     *
     * @param  array  $config
     * @param  string $name
     *
     * @return array
     */
    protected function parseConfig(array $config, $name)
    {
        return Arr::arrayAdd(Arr::arrayAdd($config, 'prefix', ''), 'name', $name);
    }

    /**
     * Create a single database connection instance.
     *
     * @param  array $config
     *
     * @return Connection
     */
    protected function createSingleConnection(array $config)
    {
        $pdo = $this->createConnector($config)->connect($config);

        return $this->createConnection(
            $pdo,
            $config['dbname'],
            $config['prefix'],
            $config
        );
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param  array $config
     *
     * @return \Brainwave\Database\Connectors\Interfaces\ConnectorInterface
     *
     * @throws \InvalidArgumentException
     */
    public function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException("A driver must be specified.");
        }

        switch ($config['driver'])
        {
            case 'mysql':
                $connector = new MySqlConnector();
                break;
            case 'mariadb':
                $connector = new MariaDBConnector();
                break;
            case 'pgsql':
                $connector = new PostgreSQLConnector();
                break;
            case 'mssql':
                $connector = new MSSQLConnector();
                break;
            case 'sybase':
                $connector = new SybaseConnector();
                break;
            case 'cloudsql':
                $connector = new GoogleCloudConnector();
                break;
            case 'sqlite':
                $connector = new SQLiteConnector();
                break;
            case 'sqlsrv':
                $connector = new SqlServerConnector();
                break;
            case 'oracle':
                $connector = new OracleConnector();
                break;
            default:
                throw new \InvalidArgumentException("Unsupported driver [{$config['driver']}]");
        }

        $this->container["db.connector.{$config['driver']}"] = function ($connector) {
            return $connector;
        };

        return $connector;
    }

    /**
     * Create a new connection instance.
     *
     * @param  \PDO    $connection
     * @param  string  $database
     * @param  string  $prefix
     * @param  array   $config
     *
     * @return Connection
     *
     * @throws \InvalidArgumentException
     */
    protected function createConnection(
        \PDO $connection,
        $database,
        $prefix = '',
        array $config = array()
    ) {
        return new Connection($connection, $database, $prefix, $config);
    }
}
