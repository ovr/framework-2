<?php
namespace Brainwave\Config;

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
     * @var array
     */
    protected $files = [];

    /**
     * The default configuration path.
     * @var string
     */
    protected $defaultPath;

    /**
     * A cache of whether namespaces and groups exists.
     *
     * @var array
     */
    protected $exists = [];

    /**
     * Create a new file configuration loader.
     * @param string $path
     * @return void
     */
    public function addDefaultPath($path)
    {
        $this->defaultPath = $path;
    }

    /**
     * Load the given configuration group.
     * @param  string  $file
     * @param  string  $namespace
     * @param  string  $environment
     * @param  string  $group
     * @return array
     */
    public function load($file, $namespace = null, $environment = null, $group = null)
    {
        // File extension to get the right driver
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        // Determine if the given file exists.
        $this->exists($file, $namespace, $environment, $group);

        // Get checked config file
        $configFile = $this->exists[preg_replace('[/]', '', $environment.$namespace.$group.$file)];

        // Set the right driver for config
        $driver = $this->driver($ext, $configFile);

        // return config array
        return $driver->load($configFile);
    }

    /**
     * Determine if the given file exists.
     * @param  string  $file
     * @param  string  $namespace
     * @param  string  $environment
     * @param  string  $group
     * @return bool
     */
    public function exists($file, $namespace = null, $environment = null, $group = null)
    {
        $key = $environment.$namespace.$group.$file;
        $key = preg_replace('[/]', '', $key);

        // We'll first check to see if we have determined if this namespace and
        // group combination have been checked before. If they have, we will
        // just return the cached result so we don't have to hit the disk.
        if (isset($this->exists[$key])) {
            return $this->exists[$key];
        }

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the config file.

        $filePath = "{$environment}/{$namespace}/{$group}/{$file}";

        $file = $this->defaultPath.preg_replace('[//]', '/', $filePath);

        if (file_exists($file)) {
            return $this->exists[$key] = $file;
        } else {
            throw new \InvalidArgumentException(
                sprintf("The file '%s' does not exist.", $file)
            );
        }
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
        $namespace = "config/packages"
    ) {
        // First we will look for a configuration file in the packages configuration
        // folder. If it exists, we will load it and merge it with these original
        // options so that we will easily 'cascade' a package's configurations.
        if ($this->exists($file, $namespace.'/'.$packages.'/'.$env, null, $group)) {
            $requireFile = file_get_contents(
                $this->exists[preg_replace('[/]', '', $namespace.$packages.$env.$group.$file)]
            );
            $items = array_merge($items, $requireFile);
        }

        // Once we have merged the regular package configuration we need to look for
        // an environment specific configuration file. If one exists, we will get
        // the contents and merge them on top of this array of options we have.
        $path = $this->getPackagePath($env, $package, $group);

        if ($this->exists($path)) {
            $requireFile = file_get_contents($path);
            $items = array_merge($items, $requireFile);
        }

        return $items;
    }

    /**
     * Get the package path for an environment and group.
     * @param  string  $env
     * @param  string  $package
     * @param  string  $group
     * @return string
     */
    protected function getPackagePath($env, $package, $group, $file)
    {
        $file = "packages/{$package}/{$env}/{$group}/{$file}";
        $file = preg_replace('[//]', '/', $file);

        return $this->defaultPath.$file;
    }

    /**
     * Get the right driver for config file
     * @param  string $ext  file extension
     * @param  string $path file path
     * @return array
     */
    protected function driver($ext, $path)
    {
        switch ($ext) {
            case 'php':
                $driver = new PhpDriver();
                $driver->load($path);
                break;

            case 'json':
                $driver = new JsonDriver();
                $driver->load($path);
                break;

            case 'ini':
                $driver = new IniDriver();
                $driver->load($path);
                break;

            case 'xml':
                $driver = new XmlDriver();
                $driver->load($path);
                break;

            case 'yaml':
                $driver = new YamlDriver();
                $driver->load($path);
                break;

            case 'toml':
                $driver = new TomlDriver();
                $driver->load($path);
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
