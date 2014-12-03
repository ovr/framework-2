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
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use Brainwave\Contracts\Filesystem\Parser as ParserContract;
use Brainwave\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * YamlAdapter
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Yaml implements ParserContract
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
     * Loads a YAML file and gets its' contents as an array
     *
     * @param string $filename
     * @param string $group
     *
     * @return array|null
     */
    public function load($filename, $group = null)
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }

        if ($this->files->exists($filename)) {
            $data = YamlParser::parse($filename);

            $groupData = [];

            if ($group !== null && is_array($data)) {
                foreach ($data as $key => $value) {
                    $groupData["{$group}::{$key}"] = $value;
                }

                return $groupData;
            }

            return (array) $data;
        }

        return;
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
        return (bool) preg_match('#\.ya?ml(\.dist)?$#', $filename);
    }

    /**
     * Format a yaml file for saving.
     *
     * @param array $data data
     *
     * @return string data export
     */
    public function format(array $data)
    {
        return YamlParser::dump($data);
    }
}
