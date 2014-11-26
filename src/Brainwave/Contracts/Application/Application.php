<?php
namespace Brainwave\Contracts\Application;

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

use \Pimple\ServiceProviderInterface;

/**
 * Application Interface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Application
{
    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance();

    /**
     * Registers a service provider.
     *
     * @param \Pimple\ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                            $values   An array of values that customizes the provider
     *
     * @return \Pimple\ServiceProviderInterface
     */
    public function register(ServiceProviderInterface $provider, array $values = []);

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot();

    /**
     * Register a new boot listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booting($callback);

    /**
     * Register a new "booted" listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booted($callback);
}
