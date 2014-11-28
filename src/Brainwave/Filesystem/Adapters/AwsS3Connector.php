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

use \Aws\S3\S3Client;
use \League\Flysystem\Adapter\AwsS3;
use \Brainwave\Contracts\Filesystem\Connector as ConnectorContract;

/**
 * AwsS3Connector
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class AwsS3Connector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param  array $config
     *
     * @return \League\Flysystem\Adapter\AwsS3
     */
    public function connect(array $config)
    {
        $auth = $this->getAuth($config);
        $client = $this->getClient($auth);
        $config = $this->getConfig($config);

        return $this->getAdapter($client, $config);
    }

    /**
     * Get the authentication data.
     *
     * @param  array $config
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('key', $config) || ! array_key_exists('secret', $config)) {
            throw new \InvalidArgumentException('The awss3 connector requires authentication.');
        }

        if (array_key_exists('region', $config) && array_key_exists('base_url', $config)) {
            return array_only($config, array('key', 'secret', 'region', 'base_url'));
        }

        if (array_key_exists('region', $config)) {
            return array_only($config, array('key', 'secret', 'region'));
        }

        if (array_key_exists('base_url', $config)) {
            return array_only($config, array('key', 'secret', 'base_url'));
        }

        return array_only($config, array('key', 'secret'));
    }

    /**
     * Get the awss3 client.
     *
     * @param  array $auth
     *
     * @return \Aws\S3\S3Client
     */
    protected function getClient(array $auth)
    {
        return S3Client::factory($auth);
    }

    /**
     * Get the configuration.
     *
     * @param  array $config
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        if (!array_key_exists('bucket', $config)) {
            throw new \InvalidArgumentException('The awss3 connector requires a bucket.');
        }

        return array_only($config, ['bucket', 'prefix']);
    }

    /**
     * Get the awss3 adapter.
     *
     * @param  \Aws\S3\S3Client $client
     * @param  array            $config
     *
     * @return \League\Flysystem\Adapter\AwsS3
     */
    protected function getAdapter(S3Client $client, array $config)
    {
        return new AwsS3($client, $config['bucket'], $config['prefix']);
    }
}
