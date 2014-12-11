<?php
namespace Brainwave\Filesystem\Parser;

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
 */

use Brainwave\Contracts\Filesystem\Parser as ParserContract;
use Brainwave\Filesystem\Filesystem;

/**
 * Ini
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Ini implements ParserContract
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
     * @param \Brainwave\Filesystem\Filesystem $files
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Loads a INI file and gets its' contents as an array
     *
     * @param string $filename
     * @param string $group
     *
     * @return array data data
     */
    public function load($filename, $group = null)
    {
        if ($this->files->exists($filename)) {
            $data = parse_ini_file($filename, true);

            $groupData = [];

            if ($group !== null) {
                foreach ($data as $key => $value) {
                    $groupData["{$group}::{$key}"] = $value;
                }

                return $groupData;
            }

            return $data;
        }

        throw new \Exception("INI file dont exists: ".$filename);
    }

    /**
     * Checking if file ist supported
     *
     * @param string $filename
     *
     * @return boolean
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.ini(\.dist)?$#', $filename);
    }

    /**
     * Format a file for saving.
     *
     * @param array $data data
     *
     * @return void
     */
    public function format(array $data)
    {
        $this->iniFormat((array) $data);
    }

    /**
     * Format a ini file.
     *
     * @param array $data
     * @param array $parent
     *
     * @return string data export
     */
    private function iniFormat(array $data, array $parent = [])
    {
        $out = '';

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                //subsection case
                //merge all the sections into one array...
                $sec = array_merge($parent, $k);
                //add section information to the output
                $out .= '['.join('.', $sec).']'.PHP_EOL;
                //recursively traverse deeper
                $out .= $this->iniFormat($v, $sec);
            } else {
                //plain key->value case
                $out .= "$k=$v".PHP_EOL;
            }
        }

        return $out;
    }
}
