<?php
namespace Brainwave\Application\Environment;

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

use Brainwave\Contracts\Application\Environment as EnvironmentContract;
use Brainwave\Support\Arr;
use Brainwave\Support\Collection;
use Brainwave\Support\Str;
use Pimple\Container;

/**
 * EnvironmentDetector
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class EnvironmentDetector extends Collection implements EnvironmentContract
{
    /**
     * The application instance.
     *
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * Mock data for an Environment
     *
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
        'REQUEST_TIME'         => '',
    ];

    /**
     * Constructor, will parse an array for environment information if present
     *
     * @param Container $container
     * @param array     $environment
     */
    public function __construct(Container $container, $environment = null)
    {
        $this->container = $container;

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
     * @param array $environment
     *
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
     * @param array $settings
     *
     * @return void
     */
    public function mock(array $settings = [])
    {
        $this->mocked['REQUEST_TIME'] = time();

        $this->parse(array_merge($this->mocked, $settings));
    }

    /**
     * Detect the application's current environment.
     *
     * @param \Closure   $callback
     * @param array|null $consoleArgs
     *
     * @return string
     */
    public function detect(\Closure $callback, $consoleArgs = null)
    {
        if ($consoleArgs) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Returns true when the runtime used is HHVM or
     * the runtime used is PHP + Xdebug.
     *
     * @return boolean
     */
    public function canCollectCodeCoverage()
    {
        return $this->isHHVM() || $this->hasXdebug();
    }

    /**
     * Returns the running php/HHVM version
     *
     * @return string
     */
    public function getVersion()
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        } else {
            return PHP_VERSION;
        }
    }

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return boolean
     */
    public function hasXdebug()
    {
        return $this->isPHP() && extension_loaded('xdebug');
    }

    /**
     * Returns true when the runtime used is HHVM.
     *
     * @return boolean
     */
    public function isHHVM()
    {
        return defined('HHVM_VERSION');
    }

    /**
     * Returns true when the runtime used is PHP.
     *
     * @return boolean
     */
    public function isPHP()
    {
        return !$this->isHHVM();
    }

    /**
     * Set the application environment for a web request.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    protected function detectWebEnvironment(\Closure $callback)
    {
        return call_user_func($callback);
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @param \Closure $callback
     * @param array    $args
     *
     * @return string
     */
    protected function detectConsoleEnvironment(\Closure $callback, array $args)
    {
        // First we will check if an environment argument was passed via console arguments
        // and if it was that automatically overrides as the environment. Otherwise, we
        // will check the environment as a "web" request like a typical HTTP request.
        if (!is_null($value = $this->getEnvironmentArgument($args))) {
            $arr = array_slice(explode('=', $value), 1);

            return reset($arr);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Get the environment argument from the console.
     *
     * @param array $args
     *
     * @return string|null
     */
    protected function getEnvironmentArgument(array $args)
    {
        return Arr::arrayFirst($args, function ($k, $v) {
            return Str::startsWith($v, '--env');
        });
    }
}
