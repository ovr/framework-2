<?php
namespace Brainwave\Config;

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

use Symfony\Component\Yaml\Parser;
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
    protected $files = array();

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
    protected $exists = array();

    /**
     * Create a new file configuration loader.
     * @param  string  $defaultPath
     * @return void
     */
    public function __construct($defaultPath)
    {
        $this->defaultPath = $defaultPath;
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
        // File extension to get the right loader
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        //Determine if the given file exists.
        $this->exists($file, $namespace, $environment, $group);

        if ($ext == 'php') {
            try {
                foreach ($this->exists as $key => $file) {
                    return new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator(file_get_contents($file)),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                }
            } catch (\Exception $e) {
                printf("Unable to parse the PHP string: %s", $e->getMessage());
            }
        } elseif ($ext == 'json') {
            try {
                foreach ($this->exists as $key => $file) {
                    return new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator(
                            json_decode(file_get_contents($file), true)
                        ),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                }
            } catch (\Exception $e) {
                printf("Unable to parse the JSON string: %s", $e->getMessage());
            }
        } elseif ($ext == 'ini') {
            try {
                foreach ($this->exists as $key => $file) {
                    return new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator(
                            parse_ini_string(file_get_contents($file), true)
                        ),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                }
            } catch (\Exception $e) {
                printf("Unable to parse the INI string: %s", $e->getMessage());
            }
        } elseif ($ext == 'xml') {
            try {
                foreach ($this->exists as $key => $file) {
                    return new \RecursiveIteratorIterator(
                        new \SimpleXmlIterator(file_get_contents($file))
                    );
                }
            } catch (\Exception $e) {
                printf("Unable to parse the XML string: %s", $e->getMessage());
            }
        } elseif ($ext == 'yaml') {
            if (!class_exists('Symfony\Component\Yaml\Parser')) {
                throw new \Exception("Install it via Composer (symfony/yaml on Packagist)");
            }

            try {
                foreach ($this->exists as $key => $file) {
                    return $yaml->parse(file_get_contents(file_get_contents($file)));
                }
            } catch (ParseException $e) {
                printf("Unable to parse the YAML string: %s", $e->getMessage());
            }
        }
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

        // We'll first check to see if we have determined if this namespace and
        // group combination have been checked before. If they have, we will
        // just return the cached result so we don't have to hit the disk.
        if (isset($this->exists[$key])) {
            return $this->exists[$key];
        }

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the config file.

        $file = $this->defaultPath."{$environment}/{$namespace}/{$group}/{$file}";

        if (file_exists($file)) {
            return $this->exists[$key] = $file;
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
    public function cascadePackage($file, $package = null, $group = null, $env = null, $items = null)
    {
        // First we will look for a configuration file in the packages configuration
        // folder. If it exists, we will load it and merge it with these original
        // options so that we will easily 'cascade' a package's configurations.
        $file = "packages/{$package}/{$group}/{$file}";

        if ($this->exists($path = $this->defaultPath.'/'.$file))
        {
            $requireFile = file_get_contents($path);
            $items = array_merge($items, $requireFile);
        }

        // Once we have merged the regular package configuration we need to look for
        // an environment specific configuration file. If one exists, we will get
        // the contents and merge them on top of this array of options we have.
        $path = $this->getPackagePath($env, $package, $group);

        if ($this->exists($path))
        {
            $requireFile = file_get_contents($path);
            $items = array_merge($items, $requireFile);
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
    protected function getPackagePath($env, $package, $group, $file)
    {
        $file = "packages/{$package}/{$env}/{$group}/{$file}";

        return $this->defaultPath.'/'.$file;
    }
}