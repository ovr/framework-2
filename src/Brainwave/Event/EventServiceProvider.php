<?php
namespace Brainwave\Event;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Pimple\Container;
use \Brainwave\Event\EventManager;
use \Pimple\ServiceProviderInterface;

/**
 * EventServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class EventServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['events'] = function ($app) {
            $event = new EventManager();
            return $event;
        };
    }
}
