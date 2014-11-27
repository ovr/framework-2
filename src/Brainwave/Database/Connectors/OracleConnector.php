<?php
namespace Brainwave\Database\Connectors;

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

use \Brainwave\Database\Connectors\Connectors;
use \Brainwave\Database\Connectors\Interfaces\ConnectorInterface;

/**
 * OracleConnector
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class OracleConnector extends Connectors implements ConnectorInterface
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

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $connection = $this->createConnection($dsn, $config, $this->getOptions($config));

        // Next we will set the "names" and "collation" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $charset = $config['charset'];

        $connection->prepare("set names '$charset'")->execute();

        $connection->prepare("set sql_mode='ANSI_QUOTES'")->execute();

        return $connection;
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param  array   $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        extract($config);

        return isset($config['port']) ?
        "oci:host={$server};port={$port};dbname={$dbname}" :
        "oci:host={$server};dbname={$dbname}";
    }
}
