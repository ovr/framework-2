<?php namespace Brainwave\Support\Services;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Support\Services\Interfaces\ServiceProviderInterface;

/**
*
*/
class ServiceManager
{
    /**
     * All loaded provicers
     * @var array
     */
    protected $providers = array();

    /**
     * Boots all service providers.
     * @var boolean
     */
    protected $booted = false;

    protected $app;

    /**
     * [__construct description]
     * @param App $app [description]
     */
    public function __construct(Workbench $app)
    {
        $this->app = $app;
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return Application
     */
    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;

        $provider->register($this->app);

        foreach ($values as $key => $value) {
            $this->app[$key] = $value;
        }

        return $this;
    }

    /**
     * Boots all service providers.
     *
     * This method is automatically called by handle(), but you can use it
     * to boot all service providers when not handling a request.
     */
    public function boot()
    {
        if (!$this->booted) {
            foreach ($this->providers as $provider) {
                $provider->boot($this);
            }

            $this->booted = true;
        }
    }
}
