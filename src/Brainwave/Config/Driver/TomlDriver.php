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

use Yosymfony\Toml\Toml;
use \Brainwave\Config\Driver\Interfaces\DriverInterface;

/**
 * Toml Driver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class TomlDriver implements DriverInterface
{
    /**
     * Loads a TOML file and gets its' contents as an array
     * @param  string $filename
     * @return array            config data
     */
    public function load($filename)
    {
        if (!class_exists('Yosymfony\\Toml\\Toml;')) {
            throw new \RuntimeException('Unable to read toml as the Toml Parser is not installed.');
        }

        $config = Toml::Parse($filename);
        return $config ?: [];
    }

    /**
     * Checking if file ist supported
     * @param  string $filename
     * @return mixed
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.toml(\.dist)?$#', $filename);
    }

    /**
     * Format a config file for saving. [NOT IMPLEMENTED]
     * @param  array     $data config data
     * @return string data export
     */
    public function format(array $data)
    {
        throw new \Exception('Toml export is not available');
    }
}
