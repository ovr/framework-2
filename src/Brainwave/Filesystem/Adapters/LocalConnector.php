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

use \League\Flysystem\Adapter\Local;
use \Brainwave\Filesystem\Adapters\Interfaces\ConnectorInterface;

/**
 * ConnectionFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class LocalConnector implements ConnectorInterface
{
    /**
     * Establish an adapter connection.
     *
     * @param  array $config
     *
     * @return \League\Flysystem\Adapter\Local
     */
    public function connect(array $config)
    {
        $config = $this->getConfig($config);

        return $this->getAdapter($config);
    }

    /**
     * Get the configuration.
     *
     * @param  array $config
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('path', $config)) {
            throw new \InvalidArgumentException('The local connector requires a path.');
        }

        return array_only($config, ['path']);
    }

    /**
     * Get the local adapter.
     *
     * @param  array $config
     *
     * @return \League\Flysystem\Adapter\Local
     */
    protected function getAdapter(array $config)
    {
        return new Local($config['path']);
    }
}
