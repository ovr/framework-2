<?php
namespace Brainwave\Database;

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
use \Brainwave\Database\Query;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Database\DatabaseManager;
use \Brainwave\Database\Connection\ConnectionFactory;

/**
 * Database ServiceProvider
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    protected $container;

    public function register(Container $container)
    {
        $this->container= $container;

        if ($this->container['settings']['database::frozen']) {
            $this->container['db'] = function () {
                return 'Database is frozen.';
            };
        } else {
            $this->registerConnectionFactory($container);

            // The database manager is used to resolve various connections, since multiple
            // connections might be managed. It also implements the connection resolver
            // interface which may be used by other components requiring connections.
            $this->container['db'] = function ($container) {
                $manager = new DatabaseManager(
                    $container,
                    $container['db.factory']
                );
                return $manager;
            };

            $this->registerDatabaseQuery();
        }
    }

    protected function registerDatabaseQuery()
    {
        $container= $this->container;
        $type = $container['db']->getConnections();

        $container['db.query'] = function ($container) {
            return new Query($container['db']->connection());
        };

        foreach ($type as $driver => $value) {
            $container["db.{$driver}.query"] = function ($container) {
                return new Query($container['db']->connection($driver));
            };
        }
    }

    /**
     * @param Container $container
     */
    protected function registerConnectionFactory($container)
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $container['db.factory'] = function ($container) {
            return new ConnectionFactory($container);
        };
    }
}
