<?php
namespace Brainwave\Environment;

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

use \Brainwave\Collection\Collection;
use \Brainwave\Environment\Interfaces\EnvironmentInterface;

/**
 * Environment
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Environment extends Collection implements EnvironmentInterface
{
    /**
     * Mock data for an Environment
     * @var array
     */
    public $mocked = [
        'SERVER_PROTOCOL'      => 'HTTP/1.1',
        'REQUEST_METHOD'       => 'GET',
        'SCRIPT_NAME'          => '',
        'REQUEST_URI'          => '',
        'QUERY_STRING'         => '',
        'CONTEXT_PREFIX'       => '',
        'SERVER_NAME'          => 'localhost',
        'SERVER_PORT'          => 80,
        'HTTP_HOST'            => 'localhost',
        'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
        'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
        'HTTP_USER_AGENT'      => 'Brainwave',
        'REMOTE_ADDR'          => '127.0.0.1',
        'REQUEST_TIME'         => ''
    ];

    /**
     * Constructor, will parse an array for environment information if present
     * @param array $environment
     */
    public function __construct($environment = null)
    {
        if (!is_null($environment)) {
            $this->parse($environment);
        }
    }

    /**
     * Parse environment array
     *
     * This method will parse an environment array and add the data to
     * this collection
     *
     * @param  array  $environment
     * @return void
     */
    public function parse(array $environment)
    {
        foreach ($environment as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Mock environment
     *
     * This method will parse a mock environment array and add the data to
     * this collection
     *
     * @param  array  $environment
     * @return void
     */
    public function mock(array $settings = [])
    {
        $this->mocked['REQUEST_TIME'] = time();
        $settings = array_merge($this->mocked, $settings);

        $this->parse($settings);
    }
}
