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
use League\Flysystem\Adapter\Ftp;

/**
 * FtpConnector
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class FtpConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return \League\Flysystem\Adapter\Ftp
     */
    public function connect(array $config)
    {
        return $this->getAdapter($config);
    }
    /**
     * Get the ftp adapter.
     *
     * @param array $config
     *
     * @return \League\Flysystem\Adapter\Ftp
     */
    protected function getAdapter(array $config)
    {
        return new Ftp($config);
    }
}
