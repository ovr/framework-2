<?php
namespace Brainwave\Cache;

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

use \Brainwave\Cache\Driver\AbstractCache;
use \Brainwave\Cache\Interfaces\CacheInterface;
use \Brainwave\Exception\CacheException\CacheException;

/**
 * CacheManager
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class CacheManager
{
    private $drivers;
    private $options;

    /**
     * Constructor.
     * @param array $drivers The list of available drivers, key=driver name, value=driver class
     * @param array $options Options to pass to the driver
     */
    public function __construct(array $drivers, array $options = array())
    {
        $this->drivers = $drivers;
        $this->options = $options;
    }

    /**
     * Builder.
     * @param string $driver The cache driver to use
     * @param array $options Options to pass to the driver
     * @return AbstractCache
     */
    public function getCache($driver, array $options = array())
    {
        if (!$this->driverExists($driver)) {
            throw new CacheException('The cache driver "'.$driver.'" is not supported by the bundle.');
        }

        $class = $this->drivers[$driver];

        if (!$class::isSupported()) {
            throw new CacheException('The cache driver "'.$driver.'" is not supported by your running configuration.');
        }

        $options = array_merge($this->options, $options);

        $cache = new $class($options);

        if (!$cache instanceof CacheInterface) {
            throw new CacheException('The cache driver "'.$driver.'" must implement CacheInterface.');
        }

        return $cache;
    }

    /**
     * Check if the given driver is supported
     * @param string $driver
     * @return bool
     */
    public function driverExists($driver)
    {
        return isset($this->drivers[$driver]);
    }
}
