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

use Brainwave\Console\Command\CommandResolver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;

/**
 * ApplicationServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class ConsoleServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['console'] = function () use ($container) {
            $class    = $container['settings']['console::class'];
            $instance = new $class(
                $container['settings']['console::name'],
                $container['settings']['console::version']
            );

            if ($instance instanceof ContainerAwareApplication) {
                $instance->setContainer($container);
            }

            // Add auto-complete for Symfony Console application
            $instance->add(new CompletionCommand());

            return $instance;
        };

        $app['command.resolver'] = function ($app) {
            return new CommandResolver($app);
        };
    }
}
