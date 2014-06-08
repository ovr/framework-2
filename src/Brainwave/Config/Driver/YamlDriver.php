<?php
namespace Brainwave\Config\Driver;

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

use \Brainwave\Config\Driver\Interfaces\DriverInterface;

/**
 * Yaml Driver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class YamlDriver implements DriverInterface
{
    /**
     * Loads a YAML file and gets its' contents as an array
     * @param  string $filename
     * @return array            config data
     */
    public function load($filename)
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }
        $config = Yaml::parse($filename);
        return $config ?: array();
    }

    /**
     * Checking if file ist supported
     * @param  string $filename
     * @return mixed
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.ya?ml(\.dist)?$#', $filename);
    }
}
