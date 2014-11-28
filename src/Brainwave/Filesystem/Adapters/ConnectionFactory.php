<?php
namespace Brainwave\Filesystem\Adapters;

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

use \Brainwave\Filesystem\Adapters\FtpConnector;
use \Brainwave\Filesystem\Adapters\NullConnector;
use \Brainwave\Filesystem\Adapters\AwsS3Connector;
use \Brainwave\Filesystem\Adapters\LocalConnector;
use \Brainwave\Filesystem\Adapters\RackspaceConnector;

/**
 * ConnectionFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class ConnectionFactory
{

    protected $defaultDriver = [
        'awss3'     => 'AwsS3',
        'local'     => 'Local',
        'null'      => 'Null',
        'rackspace' => 'Rackspace',
        'ftp'       => 'Ftp',
    ];

    /**
     * Establish an adapter connection.
     *
     * @param  array $config
     *
     * @return \League\Flysystem\AdapterInterface
     */
    public function make(array $config)
    {
        return $this->createConnector($config)->connect($config);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param  array $config
     *
     * @return \Brainwave\Contracts\Filesystem\Connector
     *
     * @throws \InvalidArgumentException
     */
    public function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException("A driver must be specified.");
        }

        if (isset($this->defaultDriver[$config['driver']])) {
            return new $this->defaultDriver[$config['driver']].Connector();
        }

        throw new \InvalidArgumentException("Unsupported driver [{$config['driver']}]");
    }
}
