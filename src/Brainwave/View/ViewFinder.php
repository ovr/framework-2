<?php
namespace Brainwave\View;

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

use \Brainwave\Filesystem\Filesystem;
use \Brainwave\Contracts\View\Interfaces\ViewFinderInterface;

/**
 * View
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class ViewFinder implements ViewFinderInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Brainwave\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The array of active view paths.
     *
     * @var array
     */
    protected $paths;

    /**
     * The array of views that have been located.
     *
     * @var array
     */
    protected $views = [];

    /**
     * The namespace to file path hints.
     *
     * @var array
     */
    protected $hints = [];

    /**
     * Register a view extension with the finder.
     *
     * @var array
     */
    protected $extensions = ['php', 'phtml', 'html'];

    /**
     * Create a new file view loader instance.
     *
     * @param  \Brainwave\Filesystem\Filesystem $files
     * @param  array                            $paths
     * @param  array                            $extensions
     *
     * @return void
     */
    public function __construct(Filesystem $files, array $paths, array $extensions = null)
    {
        $this->files = $files;
        $this->paths = $paths;

        if (isset($extensions)) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Get the fully qualified location of the view.
     *
     * @param  string $name
     *
     * @return string
     */
    public function find($name)
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if ($this->hasHintInformation($name = trim($name))) {
            return $this->views[$name] = $this->findNamedPathView($name);
        }

        return $this->views[$name] = $this->findInPaths($name, $this->paths);
    }

    /**
     * Get the path to a template with a named path.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function findNamedPathView($name)
    {
        list($namespace, $view) = $this->getNamespaceSegments($name);

        return $this->findInPaths($view, $this->hints[$namespace]);
    }

    /**
     * Get the segments of a template with a named path.
     *
     * @param  string $name
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getNamespaceSegments($name)
    {
        $segments = explode(static::HINT_PATH_DELIMITER, $name);

        if (count($segments) != 2) {
            throw new \InvalidArgumentException("View [$name] has an invalid name.");
        }

        if (!isset($this->hints[$segments[0]])) {
            throw new \InvalidArgumentException("No hint path defined for [{$segments[0]}].");
        }

        return $segments;
    }

    /**
     * Find the given view in the list of paths.
     *
     * @param  string $name
     * @param  array  $paths
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function findInPaths($name, $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($this->files->exists($viewPath = $path.'/'.$file)) {
                    return $viewPath;
                }
            }
        }

        throw new \InvalidArgumentException("View [$name] not found.");
    }

    /**
     * Get an array of possible view files.
     *
     * @param  string $name
     *
     * @return array
     */
    protected function getPossibleViewFiles($name)
    {
        return array_map(function ($extension) use ($name) {
            return str_replace('.', '/', $name).'.'.$extension;

        }, $this->extensions);
    }

    /**
     * Add a location to the finder.
     *
     * @param  string $location
     *
     * @return void
     */
    public function addLocation($location)
    {
        $this->paths[] = $location;
    }

    /**
     * Add a namespace hint to the finder.
     *
     * @param  string       $namespace
     * @param  string|array $hints
     *
     * @return void
     */
    public function addNamespace($namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($this->hints[$namespace], $hints);
        }

        $this->hints[$namespace] = $hints;
    }

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param  string       $namespace
     * @param  string|array $hints
     *
     * @return void
     */
    public function prependNamespace($namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($hints, $this->hints[$namespace]);
        }

        $this->hints[$namespace] = $hints;
    }

    /**
     * Register an extension with the view finder.
     *
     * @param  string $extension
     *
     * @return void
     */
    public function addExtension($extension)
    {
        if (($index = array_search($extension, $this->extensions)) !== false) {
            unset($this->extensions[$index]);
        }

        array_unshift($this->extensions, $extension);
    }

    /**
     * Returns whether or not the view specify a hint information.
     *
     * @param  string $name
     *
     * @return bool
     */
    public function hasHintInformation($name)
    {
        return strpos($name, static::HINT_PATH_DELIMITER) > 0;
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Brainwave\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get the active view paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Get the namespace to file path hints.
     *
     * @return array
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * Get registered extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
