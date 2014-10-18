<?php
namespace Brainwave\Database\Migrations\Driver;

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

use \Brainwave\Database\Migrations\Driver\Interfaces\DriverInterface;

/**
 * MongoDriver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class MongoDriver implements DriverInterface
{
    /**
     * @var \MongoDb
     */
    protected $connection    = null;

    /**
     * @var string
     */
    protected $tableName     = null;

    /**
     * Constructor
     *
     * @param \MongoDb $connection
     * @param string $tableName
     */
    public function __construct(\MongoDb $connection, $tableName)
    {
        $this->connection    = $connection;
        $this->tableName     = $tableName;
    }

    /**
     * Fetch all
     *
     * @return array
     */
    public function fetchAll()
    {
        $cursor = $this->connection->selectCollection($this->tableName)->find();
        $versions = array();

        foreach ($cursor as $version) {
            $versions[] = $version['version'];
        }

        return $versions;
    }

    /**
     * Up
     *
     * @param Migration $migration
     * @return self
     */
    public function up(Migration $migration)
    {
        $this->connection->selectCollection($this->tableName)->insert(array(
            'version' => $migration->getVersion()
        ));

        return $this;
    }

    /**
     * Down
     *
     * @param Migration $migration
     * @return self
     */
    public function down(Migration $migration)
    {
        $this->connection->selectCollection($this->tableName)->remove(array(
            'version' => $migration->getVersion()
        ));

        return $this;
    }

    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        $tableName = $this->tableName;
        return array_filter(
            $this->connection->getCollectionNames(),
            function ($collection) use ($tableName) {
                return $collection === $tableName;
            }
        );
    }

    /**
     * Create Schema
     *
     * @return MongoDriver
     */
    public function createSchema()
    {
        $this->connection->selectCollection($this->tableName)->ensureIndex(
            'version',
            array('unique' => 1)
        );
        return $this;
    }
}
