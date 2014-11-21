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

use \Pimple\Container;
use \Brainwave\Support\Str;
use \Brainwave\Support\Arr;
use \Brainwave\Collection\Collection;
use \Brainwave\Contracts\Application\Environment as EnvironmentContract;

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
        'REQUEST_TIME'         => ''
    ];

    /**
     * Constructor, will parse an array for environment information if present
     *
     * @param Container $app
     * @param array     $environment
     */
    public function __construct(Container $app, $environment = null)
    {
        $this->app = $app;

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
     * @param  array $environment
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
     * @return void
     */
    public function mock(array $settings = [])
    {
        $this->mocked['REQUEST_TIME'] = time();
        $settings = array_merge($this->mocked, $settings);

        $this->parse($settings);
    }

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     * @return string
     */
    public function environment()
    {
        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (str_is($pattern, $this['env'])) {
                    return true;
                }
            }

            return false;
        }

        return $this->app['env'];
    }

    /**
     * Detect the application's current environment.
     *
     * @param  array|string $envs
     *
     * @return string
     */
    public function detectEnvironment($envs)
    {
        $args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;

        return $this->app['env'] = $this->detect($envs, $args);
    }

    /**
     * Determine if we are running unit tests.
     *
     * @return string
     */
    public function runningUnitTests()
    {
        return $this->app['env'] = 'testing';
    }

    /**
     * Determine if we are running console.
     *
     * @return string
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Determine if the name matches the machine name.
     *
     * @param  string $name
     *
     * @return bool
     */
    public function isMachine($name)
    {
        return Str::is($name, gethostname());
    }

    /**
     * Detect the application's current environment.
     *
     * @param  array|string $environments
     * @param  array|null   $consoleArgs
     *
     * @return string
     */
    public function detect($environments, $consoleArgs = null)
    {
        if ($consoleArgs) {
            return $this->detectConsoleEnvironment($environments, $consoleArgs);
        }

        return $this->detectWebEnvironment($environments);
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
     * @param  array|string $environments
     *
     * @return string
     */
    protected function detectWebEnvironment($environments)
    {
        // If the given environment is just a Closure, we will defer the environment check
        // to the Closure the developer has provided, which allows them to totally swap
        // the webs environment detection logic with their own custom Closure's code.
        if ($environments instanceof \Closure) {
            return call_user_func($environments);
        }

        foreach ($environments as $environment => $hosts) {
            // To determine the current environment, we'll simply iterate through the possible
            // environments and look for the host that matches the host for this request we
            // are currently processing here, then return back these environment's names.
            foreach ((array) $hosts as $host) {
                if ($this->isMachine($host)) {
                    return $environment;
                }
            }
        }

        return 'production';
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @param  mixed $environments
     * @param  array $args
     *
     * @return string
     */
    protected function detectConsoleEnvironment($environments, array $args)
    {
        // First we will check if an environment argument was passed via console arguments
        // and if it was that automatically overrides as the environment. Otherwise, we
        // will check the environment as a "web" request like a typical HTTP request.
        if (!is_null($value = $this->getEnvironmentArgument($args))) {
            return reset(array_slice(explode('=', $value), 1));
        }

        return $this->detectWebEnvironment($environments);
    }

    /**
     * Get the environment argument from the console.
     *
     * @param  array $args
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
