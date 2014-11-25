<?php
namespace Brainwave\Filesystem;

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

use \Brainwave\Collection\Collection;
use \League\Flysystem\AdapterInterface;
use \League\Flysystem\FilesystemInterface;
use \Brainwave\Filesystem\Interfaces\FilesystemInterface as Cloud;
use \Brainwave\Filesystem\Exception\FileNotFoundException;

/**
 * FilesystemAdapter
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class FilesystemAdapter implements FilesystemInterface, Cloud
{

    /**
     * The Flysystem filesystem implementation.
     *
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $driver;

    /**
     * Create a new filesystem adapter instance.
     *
     * @param  \League\Flysystem\FilesystemInterface $driver
     *
     * @return void
     */
    public function __construct(FilesystemInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Determine if a file exists.
     *
     * @param  string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return $this->driver->has($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string $path
     *
     * @return \League\Flysystem\Handler
     *
     * @throws \Brainwave\Filesystem\Exception\FileNotFoundException;
     */
    public function get($path)
    {
        try {
            return $this->driver->read($path);
        } catch (\League\Flysystem\FileNotFoundException $e) {
            throw new FileNotFoundException($path, $e->getCode(), $e);
        }
    }

    /**
     * Write the contents of a file.
     *
     * @param  string $path
     * @param  string $contents
     * @param  string $visibility
     *
     * @return bool
     */
    public function put($path, $contents, $visibility = null)
    {
        return $this->driver->put($path, $contents, $this->parseVisibility($visibility));
    }

    /**
     * Get the visibility for the given path.
     *
     * @param  string $path
     *
     * @return string
     */
    public function getVisibility($path)
    {
        if ($this->driver->getVisibility($path) == AdapterInterface::VISIBILITY_PUBLIC) {
            return Cloud::VISIBILITY_PUBLIC;
        }

        return Cloud::VISIBILITY_PRIVATE;
    }

    /**
     * Set the visibility for the given path.
     *
     * @param  string $path
     * @param  string $visibility
     *
     * @return void
     */
    public function setVisibility($path, $visibility)
    {
        return $this->driver->setVisibility($path, $this->parseVisibility($visibility));
    }

    /**
     * Prepend to a file.
     *
     * @param  string $path
     * @param  string $data
     *
     * @return boolean
     */
    public function prepend($path, $data)
    {
        return $this->put($path, $data . PHP_EOL . $this->get($path));
    }

    /**
     * Append to a file.
     *
     * @param  string $path
     * @param  string $data
     *
     * @return boolean
     */
    public function append($path, $data)
    {
        return $this->put($path, $this->get($path) . PHP_EOL . $data);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array $paths
     *
     * @return boolean
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        foreach ($paths as $path) {
            $this->driver->delete($path);
        }

        return true;
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string $from
     * @param  string $to
     *
     * @return bool
     */
    public function copy($from, $to)
    {
        return $this->driver->copy($from, $to);
    }

    /**
     * Move a file to a new location.
     *
     * @param  string $from
     * @param  string $to
     *
     * @return boolean|null
     */
    public function move($from, $to)
    {
        $this->driver->copy($from, $to);

        $this->driver->delete($from);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string $path
     *
     * @return false|array
     */
    public function size($path)
    {
        return $this->driver->getSize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string $path
     *
     * @return false|array
     */
    public function lastModified($path)
    {
        return $this->driver->getTimestamp($path);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string|null $directory
     *
     * @param  bool  $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, 'file');
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string|null $directory
     *
     * @return array
     */
    public function allFiles($directory = null)
    {
        return $this->files($directory, true);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string|null $directory
     * @param  bool        $recursive
     *
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, 'dir');
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param  string|null $directory
     * @param  bool        $recursive
     *
     * @return array
     */
    public function allDirectories($directory = null, $recursive = false)
    {
        return $this->directories($directory, true);
    }

    /**
     * Create a directory.
     *
     * @param  string $path
     *
     * @return bool
     */
    public function makeDirectory($path)
    {
        return $this->driver->createDir($path);
    }

    /**
     * Recursively delete a directory.
     *
     * @param  string $directory
     *
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        return $this->driver->deleteDir($directory);
    }

    /**
     * Filter directory contents by type.
     *
     * @param  array  $contents
     * @param  string $type
     *
     * @return array
     */
    protected function filterContentsByType($contents, $type)
    {
        $contents = Collection::makeNew($contents);

        $contents = $contents->filter(function ($value) use ($type) {
            return $value['type'] == $type;
        })->map(function ($value) {
            return $value['path'];
        });

        return $contents->values()->all();
    }

    /**
     * Parse the given visibility value.
     *
     * @param  string|null $visibility
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseVisibility($visibility)
    {
        if (is_null($visibility)) {
            return;
        }

        switch ($visibility) {
            case Cloud::VISIBILITY_PUBLIC:
                return AdapterInterface::VISIBILITY_PUBLIC;

            case Cloud::VISIBILITY_PRIVATE:
                return AdapterInterface::VISIBILITY_PRIVATE;
        }

        throw new \InvalidArgumentException('Unknown visibility: ' . $visibility);
    }
}
