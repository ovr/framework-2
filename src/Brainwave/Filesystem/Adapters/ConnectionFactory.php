<?php
namespace Brainwave\Filesystem\Adapters;

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
 * ConnectionFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class ConnectionFactory
{
    /**
     * Establish an adapter connection.
     *
     * @param  array  $config
     * @return \League\Flysystem\AdapterInterface
     */
    public function make(array $config)
    {
        return $this->createConnector($config)->connect($config);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param  array  $config
     * @return \Brainwave\Filesystem\Adapters\Interfaces\ConnectorInterface
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
            case 'awss3':
                return new AwsS3Connector();
            case 'local':
                return new LocalConnector();
            case 'null':
                return new NullConnector();
            case 'rackspace':
                return new RackspaceConnector();
        }

        throw new \InvalidArgumentException("Unsupported driver [{$config['driver']}]");
    }
}
