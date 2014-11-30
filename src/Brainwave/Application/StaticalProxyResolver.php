<?php
namespace Brainwave\Application;

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

use \Brainwave\Support\Str;

/**
 * StaticalProxyResolver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class StaticalProxyResolver
{
    /**
     * Resolve a facade quickly to its root class
     *
     * @param  string $facade
     *
     * @return string
     */
    public function resolve($facade)
    {
        if ($this->isFacade($this->getFacadeNameFromInput($facade))) {
            $rootClass = get_class($facade::getFacadeRoot());
            return "The registered facade '{$this->getFacadeNameFromInput($facade)}' maps to {$rootClass}";
        }

        return "Facade not found";
    }

    /**
     * Create a uppercase facade name if is not already
     *
     * @param  string $facadeName
     *
     * @return string
     */
    public function getFacadeNameFromInput($facadeName)
    {
        if ($this->isUppercase($facadeName)) {
            return $facadeName;
        }

        return ucfirst(Str::camel(strtolower($facadeName)));
    }

    /**
     * Checking if facade is a really facade of StaticalProxyManager
     *
     * @param  string $facade
     *
     * @return boolean
     */
    public function isFacade($facade)
    {
        if (class_exists($facade)) {
            return array_key_exists('Brainwave\Application\StaticalProxyManager', class_parents($facade));
        }

        return false;
    }

    /**
     * Checking if facade name is in uppercase
     *
     * @param  string $string
     *
     * @return boolean
     */
    private function isUppercase($string)
    {
        return (strtoupper($string) == $string);
    }
}
