<?php
namespace Brainwave\Database;

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
    protected $app;

    public function register(Container $app)
    {
        $this->app = $app;

        if ($this->app['settings']['database::frozen']) {
            $this->app['db'] = function () {
                return 'Database is frozen.';
            };
        } else {

            $this->registerConnectionFactory();

            // The database manager is used to resolve various connections, since multiple
            // connections might be managed. It also implements the connection resolver
            // interface which may be used by other components requiring connections.
            $this->app['db'] = function ($app) {
                $manager = new DatabaseManager(
                    $app,
                    $app['db.factory']
                );
                return $manager;
            };

            $this->registerDatabaseQuery();
        }
    }

    protected function registerDatabaseQuery()
    {
        $app = $this->app;
        $type = $app['db']->getConnections();

        $app['db.query'] = function ($app) {
            return new Query($app['db']->connection());
        };

        foreach ($type as $driver => $value) {
            $app["db.{$driver}.query"] = function ($app) {
                return new Query($app['db']->connection($driver));
            };
        }
    }

    protected function registerConnectionFactory()
    {
        $app = $this->app;
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app['db.factory'] = function ($app) {
            return new ConnectionFactory($app);
        };
    }
}
