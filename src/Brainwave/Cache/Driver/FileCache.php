<?php
namespace Brainwave\Cache\Driver;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Cache\Driver\AbstractCache;

/**
 * FileCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class FileCache extends AbstractCache
{
    /**
     * @var array $cacheDir
     */
    private $cacheDir;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = array())
    {
        // Cache directory is required
        if (!isset($options['cache_dir'])) {
            throw new \InvalidArgumentException('The option "cache_dir" must be passed to the FileCache constructor.');
        }
        $this->setCacheDir($options['cache_dir']);
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported()
    {
        return function_exists('file_put_contents');
    }

    /**
     * Sets the cache directory to use.
     *
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        if (!$cacheDir) {
            throw new \InvalidArgumentException('The parameter $cacheDir must not be empty.');
        }

        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true)) {
            throw new \RuntimeException('Unable to create the directory "'.$cacheDir.'"');
        }

        // remove trailing slash
        if (in_array(substr($cacheDir, -1), array('\\', '/'))) {
            $cacheDir = substr($cacheDir, 0, -1);
        }

        $this->cacheDir = $cacheDir;
    }

    /**
     * Gets the cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Get the file name from a cache id.
     *
     * @param string $id
     */
    protected function getFileName($key)
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . md5($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        foreach (glob($this->cacheDir . "/*") as $filename) {
            unlink($filename);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $filename = $this->getFileName($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return !!$this->fetch($this->getFileName($key));
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        $filename = $this->getFileName($key);

        if (!file_exists($filename)) {
            return false;
        }

        $content = unserialize(file_get_contents($filename));

        if ($this->isContentAlive($content, $filename)) {
            return $content['data'];
        } else {
            $this->delete($key);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $var = null, $ttl = 0)
    {
        $content = array('data' => $var, 'ttl' => (int) $ttl);
        return (bool) file_put_contents($this->getFileName($key), serialize($content));
    }

    protected function isContentAlive($content, $filename)
    {
        return ($content['ttl'] === 0) || ((time() - filemtime($filename)) < $content['ttl']);
    }
}
