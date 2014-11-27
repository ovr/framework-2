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

use \Brainwave\Filesystem\Filesystem;
use \Brainwave\Contracts\Filesystem\Parser as ParserContract;

/**
 * Xml
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Xml implements ParserContract
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
     * @param  \Brainwave\Filesystem\Filesystem $files
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Loads a XML file and gets its' contents as an array
     *
     * @param  string $filename
     * @param  string $group
     *
     * @return array data
     */
    public function load($filename, $group = null)
    {
        if ($this->files->exists($filename)) {
            $data = simplexml_load_file($filename);
            $data = unserialize(serialize(json_decode(json_encode((array) $data), 1)));

            $groupData = [];

            if ($group !== null) {
                foreach ($data as $key => $value) {
                    $groupData["{$group}::{$key}"] = $value;
                }

                return $groupData;
            }

            return $data;
        }
    }

    /**
     * Checking if file ist supported
     *
     * @param  string $filename
     *
     * @return boolean
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.xml(\.dist)?$#', $filename);
    }

    /**
     * Format a xml file for saving.
     *
     * @param  array $data data
     *
     * @return string|false data export
     */
    public function format(array $data)
    {
        // creating object of SimpleXMLElement
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><config></config>");

        // function call to convert array to xml
        $this->arrayToXml($data, $xml);

        return $xml->asXML();
    }

    /**
     * Defination to convert array to xml [NOT IMPLEMENTED]
     *
     * @param  array             $data data
     * @param  \SimpleXMLElement $xml
     *
     * @return string
     */
    private function arrayToXml($data, \SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $key = is_numeric($key) ? "item$key" : $key;
                $subnode = $xml->addChild("$key");
                array_to_xml($value, $subnode);
            } else {
                $key = is_numeric($key) ? "item$key" : $key;
                $xml->addChild("$key", "$value");
            }
        }
    }
}
