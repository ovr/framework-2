<?php
namespace Brainwave\Cookie;

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
    /** @var string filename */
    protected $filename;

    /**
     * Create a new FileCookieJar object
     *
     * @param string $cookieFile File to store the cookie data
     *
     * @throws RuntimeException if the file cannot be found or created
     */
    public function __construct($cookieFile)
    {
        $this->filename = $cookieFile;
        $this->load();
    }

    /**
     * Saves the file when shutting down
     */
    public function __destruct()
    {
        $this->persist();
    }

    /**
     * Save the contents of the data array to the file
     *
     * @throws RuntimeException if the file cannot be found or created
     */
    protected function persist()
    {
        if (false === file_put_contents($this->filename, $this->serialize())) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Unable to open file ' . $this->filename);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Load the contents of the json formatted file into the data array and discard any unsaved state
     */
    protected function load()
    {
        $json = file_get_contents($this->filename);
        if (false === $json) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Unable to open file ' . $this->filename);
            // @codeCoverageIgnoreEnd
        }

        $this->unserialize($json);
        $this->cookies = $this->cookies ?: [];
    }
}
