<?php
namespace Brainwave\Config\Driver;

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

use \Brainwave\Config\Driver\Interfaces\DriverInterface;

/**
 * Xml Driver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class XmlDriver implements DriverInterface
{
    /**
     * Loads a xML file and gets its' contents as an array
     * @param  string $filename
     * @return array            config data
     */
    public function load($filename)
    {
        $config = require $filename;
        $config = (1 === $config) ? [] : $config;
        return $config ?: [];
    }

    /**
     * Checking if file ist supported
     * @param  string $filename
     * @return mixed
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.php(\.dist)?$#', $filename);
    }

    /**
     * Format a config file for saving. [NOT IMPLEMENTED]
     * @param  array     $data config data
     * @return string data export
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
     * Defination to convert array to xml
     * @param  array $data  config data
     * @param  void $xml    \SimpleXMLElement
     * @return string       data
     */
    protected function arrayToXml($data, &$xml)
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
