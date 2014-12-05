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

use Brainwave\Application\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console;
use Symfony\Component\Console\Command\Command;

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
    const VERSION = '0.9.4-dev';

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
     */
    public function __construct($name, $version = null)
    {
        parent::__construct($values);

        $this['settings']->set(
            'console::name', isset($name) ? $name : 'cerebro'
        );

        $this['settings']->set(
            'console::class', 'Brainwave\Console\ContainerAwareApplication'
        );

        $this['settings']->set(
            'console::version', ($version !== null) ? $version : Application::BRAINWAVE_VERSION;
        );

        $this->register(new ConsoleServiceProvider());
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
            if ($provider instanceof CommandProviderInterface) {
                $provider->addCommands($this);
            }
        }
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
