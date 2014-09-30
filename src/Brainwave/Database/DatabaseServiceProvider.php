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

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Database\DatabaseQuery;
use \Brainwave\Database\DatabaseManager;

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

        if ($app['settings']['db.frozen'] === false) {
            $app['db'] = function () use ($app) {
                return new DatabaseManager(
                    $app['settings']['db']
                );
            };
            $this->registerDatabaseQuery($app['db']);
        } else {
            return 'Database is frozen. TODO add debuging';
        }
    }

    protected function registerDatabaseQuery(DatabaseManager $databaseManager)
    {
        $this->app['db.query'] = function () use ($databaseManager) {
            return new DatabaseQuery($databaseManager);
        };
    }

    protected function registerRedisDatabase()
    {
        $this->appp['redis'] = function () {
            //return new RedisDatabase($app['settings']['database.redis']);
        };
    }
}
