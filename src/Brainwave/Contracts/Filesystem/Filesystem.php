<?php
namespace Brainwave\Contracts\Filesystem;

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

/**
 * Filesystem
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Filesystem
{
    /**
     * The public visibility setting.
     *
     * @var string
     */
    const VISIBILITY_PUBLIC = 'public';

    /**
     * The private visibility setting.
     *
     * @var string
     */
    const VISIBILITY_PRIVATE = 'private';

    /**
     * Determine if a file exists.
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists($path);

    /**
     * Get the contents of a file.
     *
     * @param string $path
     *
     * @return false|string
     *
     * @throws FileNotFoundException
     */
    public function get($path);

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     *
     * @return boolean
     */
    public function put($path, $contents, $visibility = null);

    /**
     * Get the visibility for the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getVisibility($path);

    /**
     * Set the visibility for the given path.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return void
     */
    public function setVisibility($path, $visibility);

    /**
     * Prepend to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return boolean
     */
    public function prepend($path, $data);

    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return boolean
     */
    public function append($path, $data);

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     *
     * @return boolean
     */
    public function delete($paths);

    /**
     * Copy a file to a new location.
     *
     * @param string $from
     * @param string $to
     *
     * @return boolean
     */
    public function copy($from, $to);

    /**
     * Move a file to a new location.
     *
     * @param string $from
     * @param string $to
     *
     * @return boolean
     */
    public function move($from, $to);

    /**
     * Get the file size of a given file.
     *
     * @param string $path
     *
     * @return false|integer
     */
    public function size($path);

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     *
     * @return false|integer
     */
    public function lastModified($path);

    /**
     * Get an array of all files in a directory.
     *
     * @param string|null $directory
     * @param boolean     $recursive
     *
     * @return array
     */
    public function files($directory = null, $recursive = false);

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allFiles($directory = null);

    /**
     * Get all of the directories within a given directory.
     *
     * @param string|null $directory
     * @param boolean     $recursive
     *
     * @return array
     */
    public function directories($directory = null, $recursive = false);

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allDirectories($directory = null);

    /**
     * Create a directory.
     *
     * @param string $path
     *
     * @return boolean
     */
    public function makeDirectory($path);

    /**
     * Recursively delete a directory.
     *
     * @param string $directory
     *
     * @return boolean
     */
    public function deleteDirectory($directory);
}
