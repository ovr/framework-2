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
 * Php Driver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class PhpDriver implements DriverInterface
{
    /**
     * Loads a PHP file and gets its' contents as an array
     * @param  string $filename
     * @return array            config data
     */
    public function load($filename)
    {
        $config = require $filename;
        $config = (1 === $config) ? array() : $config;
        return $config ?: array();
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
     * Format a config file for saving.
     * @param  array  $data config data
     * @return string data export
     */
    public function format(array $data)
    {
        $data = var_export($data, true);

        $formatted = str_replace(
            array('  ', 'array ('),
            array("\t", 'array('),
            $data
        );

        $output = <<<CONF
<?php

return {$formatted};
CONF;

        return $output;
    }
}
