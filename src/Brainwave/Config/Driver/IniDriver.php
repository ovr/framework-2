<?php
namespace Brainwave\Config\Driver;

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
use \Brainwave\Config\Driver\Interfaces\DriverInterface;

/**
 * Ini Driver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class IniDriver implements DriverInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Brainwave\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file filesystem loader.
     *
     * @param  \Brainwave\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Loads a INI file and gets its' contents as an array
     *
     * @param  string $filename
     * @param  string $group
     * @return array            config data
     */
    public function load($filename, $group = null)
    {
        if ($this->files->exists($filename)) {
            $config = parse_ini_file($filename, true);
        } else {
            throw new \Exception("INI file dont exists: ".$filename);
        }

        $groupConfig = [];

        if ($group !== null) {
            foreach ($config as $key => $value) {
                $groupConfig["{$group}::{$key}"] = $value;
            }
        }

        return ($group === null) ? $config : $groupConfig;
    }

    /**
     * Checking if file ist supported
     *
     * @param  string $filename
     * @return boolean
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.ini(\.dist)?$#', $filename);
    }

    /**
     * Format a config file for saving.
     *
     * @param  array     $data config data
     */
    public function format(array $data)
    {
        $this->iniFormat((array) $data);
    }

    /**
     * Format a ini config file.
     *
     * @param  array  $data   config data
     * @param  array  $parent data
     * @return string data export
     */
    protected function iniFormat(array $data, array $parent = [])
    {
        $out = '';

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                //subsection case
                //merge all the sections into one array...
                $sec = array_merge($parent, $k);
                //add section information to the output
                $out .= '[' . join('.', $sec) . ']' . PHP_EOL;
                //recursively traverse deeper
                $out .= $this->iniFormat($v, $sec);
            } else {
                //plain key->value case
                $out .= "$k=$v" . PHP_EOL;
            }
        }

        return $out;
    }
}
