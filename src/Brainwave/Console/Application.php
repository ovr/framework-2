<?php
namespace Brainwave\Console;

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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Application
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Application extends Container
{
    /**
     * Version number for Cerebro
     */
    const VERSION = '2.0.0-DEV';

    /**
     * ServiceProviderInterface
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Determines if all service providers have been registered and booted
     *
     * @var boolean
     */
    protected $booted = false;

    /**
     * Registers the autoloader and necessary components.
     *
     * @param string      $name    Name for this application.
     * @param string|null $version Version number for this application.
     * @param string[]    $values  Options for this application and its services.
     */
    public function __construct($name, $version = null, array $values = [])
    {
        parent::__construct($values);

        $app = $this;

        $this['dispatcher_class'] = 'Symfony\\Component\\EventDispatcher\\EventDispatcher';
        $this['dispatcher'] = function () use ($app) {
            $dispatcher = new $app['dispatcher_class']();

            return $dispatcher;
        };

        $consoleConfig = array('console.name' => $name);

        if (null !== $version) {
            $consoleConfig['console.version'] = $version;
        }

        $this->register(new ConsoleServiceProvider(), $consoleConfig);

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param mixed[]                  $values   An array of values that customizes the provider
     *
     * @api
     *
     * @return Application
     */
    public function register(ServiceProviderInterface $provider, array $values = [])
    {
        $this->providers[] = $provider;

        parent::register($provider, $values);

        return $this;
    }

    /**
     * Boots all service providers.
     *
     * This method is automatically called by run(), but you can use it to boot all service providers when not handling
     * a command.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        foreach ($this->providers as $provider) {
            if ($provider instanceof Interfaces\CommandProviderInterface) {
                $provider->addCommands($this);
            }
        }
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string   $eventName The event to listen on
     * @param callable $callback  The listener
     * @param int      $priority  The higher this value, the earlier an event listener will be triggered in the
     *                            chain (defaults to 0)
     *
     * @return void
     */
    public function on($eventName, $callback, $priority = 0)
    {
        if ($this->booted) {
            $this['dispatcher']->addListener($eventName, $callback, $priority);

            return;
        }

        $this->extend('dispatcher', function ($dispatcher) use ($callback, $priority, $eventName) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher->addListener($eventName, $callback, $priority);

            return $dispatcher;
        });
    }

    /**
     * Adds a command object.
     *
     * If a command with the same name already exists, it will be overridden.
     *
     * @param Command $command A Command object
     *
     * @api
     *
     * @return void
     */
    public function add(Command $command)
    {
        $this['console']->add($command);
    }

    /**
     * @param string   $name
     * @param callable $callable
     *
     * @return Command
     */
    public function command($name, $callable)
    {
        $command = new Command($name);
        $command->setCode($callable);

        $this->add($command);

        return $command;
    }

    /**
     * Executes this application.
     *
     * @param bool $interactive runs in an interactive shell if true.
     *
     * @api
     *
     * @return void
     */
    public function run($interactive = false)
    {
        if (!$this->booted) {
            $this->boot();
        }

        $app = $this['console'];

        if ($interactive) {
            $app = new Console\Shell($app);
        }

        $app->run();
    }
}
