<?php
namespace Brainwave\Database\Connection;

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

use \Brainwave\Database\Connection\Interfaces\ConnectionResolverInterface;

/**
 * ConnectionResolver
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * All of the registered connections.
     *
     * @var array
     */
    protected $connections = array();

    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default;

    /**
     * Create a new connection resolver instance.
     *
     * @param  array  $connections
     * @return void
     */
    public function __construct(array $connections = array())
    {
        foreach ($connections as $name => $connection) {
            $this->addConnection($name, $connection);
        }
    }

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Brainwave\Database\Connection
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        return $this->connections[$name];
    }

    /**
     * Add a connection to the resolver.
     *
     * @param  string  $name
     * @param  Connection  $connection
     * @return void
     */
    public function addConnection($name, Connection $connection)
    {
        $this->connections[$name] = $connection;
    }

    /**
     * Check if a connection has been registered.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
    }

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }
}
