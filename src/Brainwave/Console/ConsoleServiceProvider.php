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

use Brainwave\Application\Application as BrainwaveApplication;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
        $version = (BrainwaveApplication::BRAINWAVE_VERSION !== null) ?
                    BrainwaveApplication::BRAINWAVE_VERSION :
                    '0.9.4-dev';

        $container['settings']->get(
            'console',
            [
                'console.name'    => 'Cerebro Application',
                'console.class'   => 'Brainwave\Console\ContainerAwareApplication',
                'console.version' => $version,
            ]
        );

        $container['console'] = function () use ($container) {
            $class    = $container['settings']['console.class'];
            $instance = new $class(
                $container['settings']['console.name'],
                $container['settings']['console.version']
            );

            if ($instance instanceof ContainerAwareApplication) {
                $instance->setContainer($container);
            }

            return $instance;
        };
    }
}
