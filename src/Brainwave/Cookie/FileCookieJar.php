<?php
namespace Brainwave\Cookie;

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

use \Brainwave\Cookie\CookieJar;

/**
 * FileCookieJar
 *
 * Persists non-session cookies using a JSON formatted file
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class FileCookieJar extends CookieJar
{
    /**
     * File path
     *
     * @var string
     */
    protected $filename;

    /**
     * Create a new FileCookieJar object
     *
     * @param  string $cookieFile File to store the cookie data
     *
     * @throws RuntimeException if the file cannot be found or created
     */
    public function __construct($cookieFile)
    {
        $this->filename = $cookieFile;

        if (file_exists($cookieFile)) {
            $this->load($cookieFile);
        }
    }

    /**
     * Saves the file when shutting down
     */
    public function __destruct()
    {
        $this->save($this->filename);
    }

    /**
     * Save the contents of the data array to the file
     *
     * @param string $filename File to save
     * @throws RuntimeException if the file cannot be found or created
     */
    protected function save($filename)
    {
        $json = [];
        foreach ($this as $cookie) {
            if ($cookie->getExpires() && !$cookie->getDiscard()) {
                $json[] = $cookie->toArray();
            }
        }

        if (false === file_put_contents($filename, json_encode($json))) {
            throw new \RuntimeException('Unable to open file ' . $filename);
        }
    }

     /**
      * Load cookies from a JSON formatted file.
      *
      * Old cookies are kept unless overwritten by newly loaded ones.
      *
      *
      * @throws \RuntimeException if the file cannot be loaded.
      */
    protected function load()
    {
        $json = file_get_contents($this->filename);

        if (false === $json) {
            throw new \RuntimeException('Unable to open file ' . $this->filename);
        }

        $this->unserialize($json);
        $this->defaults = $this->defaults ?: [];
    }
}
