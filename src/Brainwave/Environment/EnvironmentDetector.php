<?php namespace Brainwave\Environment;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * 
 */
class EnvironmentDetector
{

    /**
     * Detect the application's current environment.
     *
     * @param  array|string  $environments
     * @return string
     */
    public function detect($environments)
    {
        return $this->detectWebEnvironment($environments);
    }

    /**
     * Set the application environment for a web request.
     *
     * @param  array|string  $environments
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
     * Determine if the name matches the machine name.
     *
     * @param  string  $name
     * @return bool
     */
    public function isMachine($name)
    {
        return str_is($name, gethostname());
    }
}
