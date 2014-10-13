<?php
namespace Brainwave\Database\Connectors;

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

use \Brainwave\Database\Connectors\Connectors;
use \Brainwave\Database\Connectors\Interfaces\ConnectorInterface;

/**
 * MSSQLConnector
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class MSSQLConnector extends Connectors implements ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $connection = $this->createConnection($dsn, $config, $options);

        // Next we will set the "names" on the clients connections so
        // a correct character set will be used by this client.
        $charset = $config['charset'];

        $connection->prepare("set names '$charset'")->execute();

        // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
        $connection->prepare("set quoted_identifier on")->execute();
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param  array   $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        return $this->configIsWin() ? $this->getSqlsrvDsn($config) : $this->getDblibDsn($config);
    }

    /**
     * Determine if the given configuration array is Win.
     *
     * @return bool
     */
    protected function configIsWin()
    {
        return (strstr(PHP_OS, 'WIN')) ? true : false;
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getSqlsrvDsn(array $config)
    {
        extract($config);

        return isset($config['port']) ?
        "sqlsrv:server={$server},$port;database={$dbname}" :
        "sqlsrv:server={$server};database={$dbname}";
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDblibDsn(array $config)
    {
        extract($config);

        return isset($config['port']) ?
        "dblib:host={$server}:$port;database={$dbname}" :
        "dblib:host={$server};database={$dbname}";
    }
}
