<?php
namespace Brainwave\Console\Command;

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

use Brainwave\Console\ContainerAwareApplication;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Command
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
abstract class Command extends BaseCommand
{
    /**
     * Returns the application container.
     *
     * @return \Pimple\Container
     *
     * @throws \LogicException
     */
    public function getContainer()
    {
        $app = $this->getApplication();

        if ($app instanceof ContainerAwareApplication) {
            return $app->getContainer();
        }

        throw new \LogicException(
            '{$app} must be an instance of Brainwave\Console\ContainerAwareApplication, '
           .'other instances are not supported.'
        );
    }

    /**
     * Returns a service contained in the application container or null if none
     * is found with that name.
     *
     * This is a convenience method used to retrieve an element from the
     * Application container without having to assign the results of the
     * getContainer() method in every call.
     *
     * @param string $name Name of the service
     *
     * @see self::getContainer()
     *
     * @api
     *
     * @return \stdClass|null
     *
     * @throws \LogicException
     */
    public function getService($name)
    {
        $app = $this->getApplication();

        if ($app instanceof ContainerAwareApplication) {
            return $app->getService($name);
        }

        throw new \LogicException(
            '{$app} must be an instance of Brainwave\Console\ContainerAwareApplication, '
           .'other instances are not supported.'
        );
    }
}
