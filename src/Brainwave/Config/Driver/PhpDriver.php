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
     * Loads a PHP file and gets its' contents as an array
     *
     * @param  string $filename
     * @param  string $group
     * @return array            config data
     */
    public function load($filename, $group = null)
    {
        $config = $this->files->getRequire($filename);

        $groupConfig = [];

        if ($group !== null) {
            foreach ($config as $key => $value) {
                $groupConfig["{$group}::{$key}"] = $value;
            }
            $config = $groupConfig;
        }

        return $config;
    }

    /**
     * Checking if file ist supported
     *
     * @param  string $filename
     * @return mixed
     */
    public function supports($filename)
    {
        return (bool) preg_match('#\.php(\.dist)?$#', $filename);
    }

    /**
     * Format a config file for saving.
     *
     * @param  array  $data config data
     * @return string data export
     */
    public function format(array $data)
    {
        $data = var_export($data, true);

        $formatted = str_replace(
            ['  ', '['],
            ["\t", '['],
            $data
        );

        $output = <<<CONF
<?php

return {$formatted};
CONF;

        return $output;
    }
}
