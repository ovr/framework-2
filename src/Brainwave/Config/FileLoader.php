<?php
namespace Brainwave\Config;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Filesystem\Filesystem;
use \Brainwave\Config\Driver\PhpDriver;
use \Brainwave\Config\Driver\IniDriver;
use \Brainwave\Config\Driver\XmlDriver;
use \Brainwave\Config\Driver\JsonDriver;
use \Brainwave\Config\Driver\YamlDriver;
use \Brainwave\Config\Driver\TomlDriver;
use \Brainwave\Config\Interfaces\LoaderInterface;

/**
 * File Loader
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class FileLoader implements LoaderInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Brainwave\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The default configuration path.
     *
     * @var string
     */
    protected $defaultPath;

    /**
     * All of the named path hints.
     *
     * @var array
     */
    protected $hints = array();

    /**
     * A cache of whether namespaces and groups exists.
     *
     * @var array
     */
    protected $exists = [];

    /**
     * Create a new file configuration loader.
     *
     * @param  \Brainwave\Filesystem\Filesystem  $files
     * @param  string  $defaultPath
     * @return void
     */
    public function __construct(Filesystem $files, $defaultPath)
    {
        $this->files = $files;
        $this->defaultPath = $defaultPath;
    }

    /**
     * Load the given configuration group.
     *
     * @param  string  $file
     * @param  string  $group
     * @param  string  $namespace
     * @param  string  $environment
     * @return array
     */
    public function load($file, $group = null, $environment = null, $namespace = null)
    {
        $path = $this->getPath($namespace);

        // Determine if the given file exists.
        $this->exists($file, $group, $environment, $namespace);

        // Get checked config file
        $configFile = $this->exists[preg_replace('[/]', '', $namespace.$group.$file)];

        // Set the right driver for config
        $driver = $this->driver($this->files->extension($file), $configFile);

        // return config array
        $items = $driver->load($configFile, $group);

        // Finally we're ready to check for the environment specific configuration
        // file which will be merged on top of the main arrays so that they get
        // precedence over them if we are currently in an environments setup.
        $env = "/{$environment}/{$file}";

        // Get checked env config file
        $envConfigFile = $this->exists[preg_replace('[/]', '', $namespace.$environment.$group.$file)];

        if ($this->files->exists($envConfigFile)) {
            // Set the right driver for environment config
            $envDriver = $this->driver($this->files->extension($file), $path.$env);

            // Return config array
            $envItems = $envDriver->load($envConfigFile, $group);

            // Merege env config and config
            $items = $this->configMerge($items, $envItems);
        }

        return $items;
    }

    /**
     * Determine if the given file exists.
     *
     * @param  string  $file
     * @param  string  $namespace
     * @param  string  $environment
     * @param  string  $group
     * @return bool
     */
    public function exists($file, $group = null, $environment = null, $namespace = null)
    {
        $envKey = $namespace.$environment.$group.$file;
        $envKey = preg_replace('[/]', '', $envKey);

        $key = $namespace.$group.$file;
        $key = preg_replace('[/]', '', $key);

        // We'll first check to see if we have determined if this namespace and
        // group combination have been checked before. If they have, we will
        // just return the cached result so we don't have to hit the disk.

        if (isset($this->exists[$envKey]) || isset($this->exists[$key])) {
            return $this->exists;
        }

        $path = $this->getPath($namespace);

        // To check if a group exists, we will simply get the path based on the
        // namespace, and then check to see if this files exists within that
        // namespace. False is returned if no path exists for a namespace.
        if (is_null($path.$file)) {
            return $this->exists[$key] = false;
        }

        if (is_null($path.$environment.$file)) {
            $this->exists[$envKey] = false;
        }

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the config file.
        $file = "{$path}/{$file}";

        $envFile = "{$path}/{$environment}/{$file}";

        if ($this->files->exists($envFile)) {
            $this->exists[$envKey] = $envFile;
        }

        $this->exists[$key] = $file;

        return $this;
    }

    /**
     * Apply any cascades to an array of package options.
     *
     * @param  string  $package
     * @param  string  $group
     * @param  string  $env
     * @param  array   $items
     * @return array
     */
    public function cascadePackage(
        $file,
        $package = null,
        $group = null,
        $env = null,
        $items = null,
        $namespace = 'packages'
    ) {
        // First we will look for a configuration file in the packages configuration
        // folder. If it exists, we will load it and merge it with these original
        // options so that we will easily 'cascade' a package's configurations.
        if ($this->exists($file, "{$namespace}/{$packages}/{$env}", null, $group)) {

            $items = $this->configMerge(
                $items,
                $this->files->get(
                    $this->exists[preg_replace('[/]', '', $namespace.$packages.$env.$group.$file)]
                )
            );
        }

        // Once we have merged the regular package configuration we need to look for
        // an environment specific configuration file. If one exists, we will get
        // the contents and merge them on top of this array of options we have.
        $path = $this->getPackagePath($env, $package, $group, $file, $namespace);

        if ($this->exists($path)) {
            $items = $this->configMerge($items, $this->files->get($path));
        }

        return $items;
    }

    /**
     * Get the package path for an environment and group.
     *
     * @param  string  $env
     * @param  string  $package
     * @param  string  $group
     * @return string
     */
    protected function getPackagePath($env, $package, $group, $file, $namespace = null)
    {
        $file = "packages/{$package}/{$env}/{$group}/{$file}";
        $file = preg_replace('[//]', '/', $file);

        return $this->getPath($namespace).$file;
    }

    /**
     * Get the configuration path for a namespace.
     *
     * @param  string  $namespace
     * @return string
     */
    protected function getPath($namespace)
    {
        if ($namespace === null) {
            return $this->defaultPath;
        } elseif (isset($this->hints[$namespace])) {
            return $this->hints[$namespace];
        }
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;
    }

    /**
     * Returns all registered namespaces with the config
     * loader.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->hints;
    }

    /**
     * Sensibly merge configuration arrays.
     *
     * @param  dynamic array
     * @return array
     */
    protected function configMerge()
    {
        $result = [];

        foreach (func_get_args() as $arg) {
            foreach ($arg as $key => $value) {
                if (is_numeric($key)) {
                    $result[] = $value;
                } elseif (array_key_exists($key, $result) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = $this->configMerge($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Get a file's contents by requiring it.
     *
     * @param  string  $path
     * @return mixed
     */
    protected function getRequire($path)
    {
        return $this->files->getRequire($path);
    }

    /**
     * Get the Filesystem instance.
     *
     * @return \Brainwave\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get the right driver for config file
     *
     * @param  string $ext  file extension
     * @param  string $path file path
     * @return array
     */
    protected function driver($ext, $path)
    {
        switch ($ext) {
            case 'php':
                $driver = new PhpDriver($this->getFilesystem());
                break;

            case 'json':
                $driver = new JsonDriver($this->getFilesystem());
                break;

            case 'ini':
                $driver = new IniDriver($this->getFilesystem());
                break;

            case 'xml':
                $driver = new XmlDriver($this->getFilesystem());
                break;

            case 'yaml':
                $driver = new YamlDriver($this->getFilesystem());
                break;

            case 'toml':
                $driver = new TomlDriver($this->getFilesystem());
                break;
            default:
                throw new \RuntimeException(
                    sprintf("Unable to find the right driver for '%s'", $ext)
                );
                break;
        }

        if ($driver->supports($path)) {
            return $driver;
        }
    }
}
