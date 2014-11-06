<?php
namespace Brainwave\Cache\Adapter;

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

use \Brainwave\Support\Arr;
use \Brainwave\Filesystem\Interfaces\FilesystemInterface;
use \Brainwave\Contracts\Cache\Adapter as AdapterContract;

/**
 * FileCache
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class FileCache implements AdapterContract
{
    /**
     * The Brainwave Filesystem instance.
     *
     * @var FilesystemInterface
     */
    protected $files;

    /**
     * The file cache directory
     *
     * @var string
     */
    protected $directory;

    /**
     * Check if the cache driver is supported
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return true;
    }

    /**
     * Create a new file cache store instance.
     *
     * @param  FilesystemInterface $files
     * @param  string              $directory
     *
     * @return AdapterContract
     */
    public function __construct(FilesystemInterface $files, $directory)
    {
        $this->files = $files;
        $this->directory = $directory;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Arr::arrayGet($this->getPayload($key), 'data', null);
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param  string $key
     *
     * @return array
     */
    protected function getPayload($key)
    {
        $path = $this->path($key);

        // If the file doesn't exists, we obviously can't return the cache so we will
        // just return null. Otherwise, we'll get the contents of the file and get
        // the expiration UNIX timestamps from the start of the file's contents.
        if (!$this->files->exists($path)) {
            return array('data' => null, 'time' => null);
        }

        try {
            $expire = substr($contents = $this->files->get($path), 0, 10);
        } catch (\Exception $e) {
            return array('data' => null, 'time' => null);
        }

        // If the current time is greater than expiration timestamps we will delete
        // the file and return null. This helps clean up the old files and keeps
        // this directory much cleaner for us as old files aren't hanging out.
        if (time() >= $expire) {
            $this->forget($key);

            return array('data' => null, 'time' => null);
        }

        $data = unserialize(substr($contents, 10));

        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on the cache. We'll round this out.
        $time = ceil(($expire - time()) / 60);

        return compact('data', 'time');
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $minutes
     *
     * @return void
     */
    public function set($key, $value, $minutes)
    {
        $value = $this->expiration($minutes).serialize($value);

        $this->createCacheDirectory($path = $this->path($key));

        $this->files->put($path, $value);
    }

    /**
     * Create the file cache directory if necessary.
     *
     * @param  string $path
     *
     * @return void
     */
    protected function createCacheDirectory($path)
    {
        try {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        } catch (\Exception $e) {
            //
        }
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer $value
     *
     * @return int
     */
    public function increment($key, $value = 1)
    {
        $raw = $this->getPayload($key);

        $int = ((int) $raw['data']) + $value;

        $this->set($key, $int, (int) $raw['time']);

        return $int;
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  integer $value
     *
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever($key, $value)
    {
        return $this->set($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     *
     * @return void
     */
    public function forget($key)
    {
        $file = $this->path($key);

        if ($this->files->exists($file)) {
            $this->files->delete($file);
        }
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        foreach ($this->files->directories($this->directory) as $directory) {
            $this->files->deleteDirectory($directory);
        }
    }

    /**
     * Get the full path for the given cache key.
     *
     * @param  string $key
     *
     * @return string
     */
    protected function path($key)
    {
        $parts = array_slice(str_split($hash = md5($key), 2), 0, 2);

        return $this->directory.'/'.join('/', $parts).'/'.$hash;
    }

    /**
     * Get the expiration time based on the given minutes.
     *
     * @param  int $minutes
     *
     * @return int
     */
    protected function expiration($minutes)
    {
        if ($minutes === 0) {
            return 9999999999;
        }

        return time() + ($minutes * 60);
    }

    /**
     * Get the working directory of the cache.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return '';
    }
}
