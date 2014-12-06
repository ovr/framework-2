<?php
namespace Brainwave\Contracts\Cookie;

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

/**
 * CookiesJar
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface QueueingFactory extends Factory
{
    /**
     * Queue a cookie to send with the next response.
     *
     * @param  mixed
     * @return void
     */
    public function queue();

    /**
     * Remove a cookie from the queue.
     *
     * @param  string  $name
     */
    public function unqueue($name);

    /**
     * Get the cookies which have been queued for the next request
     *
     * @return array
     */
    public function getQueuedCookies();
}
