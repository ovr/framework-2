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

use Brainwave\Contracts\Filesystem\Connector as ConnectorContract;
use League\Flysystem\Adapter\Rackspace;
use OpenCloud\ObjectStore\Resource\Container;
use OpenCloud\OpenStack;

/**
 * ConnectionFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class RackspaceConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return \League\Flysystem\Adapter\Rackspace
     */
    public function connect(array $config)
    {
        $auth = $this->getAuth($config);
        $client = $this->getClient($auth);

        return $this->getAdapter($client);
    }

    /**
     * Get the authentication data.
     *
     * @param array $config
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('username', $config) || ! array_key_exists('password', $config)) {
            throw new \InvalidArgumentException('The rackspace connector requires authentication.');
        }

        if (!array_key_exists('endpoint', $config) || ! array_key_exists('container', $config)) {
            throw new \InvalidArgumentException('The rackspace connector requires configuration.');
        }

        return array_only($config, ['username', 'password', 'endpoint', 'container']);
    }

    /**
     * Get the rackspace client.
     *
     * @param array $auth
     *
     * @return \OpenCloud\ObjectStore\Resource\Container
     */
    protected function getClient(array $auth)
    {
        $client = new OpenStack($auth['endpoint'], [
            'username' => $auth['username'],
            'password' => $auth['password'],
        ]);

        return $client->objectStoreService('cloudFiles', 'LON')->getContainer($auth['container']);
    }

    /**
     * Get the rackspace adapter.
     *
     * @param \OpenCloud\ObjectStore\Resource\Container $client
     *
     * @return \League\Flysystem\Adapter\Rackspace
     */
    protected function getAdapter(Container $client)
    {
        return new Rackspace($client);
    }
}
