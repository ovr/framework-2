<?php
namespace Brainwave\Workbench;

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

/**
 * StaticalProxyResolver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class StaticalProxyResolver
{
    //TODO finish resolver
    public function __construct()
    {
        # code...
    }

    public function generateArtisanOutput($facade)
    {
        if ($this->isFacade($facade)) {
            $rootClass = get_class($facade::getFacadeRoot());
            $this->info("The registered facade '{$facade}' maps to {$rootClass}");
        } else {
            $this->error("Facade not found");
        }
    }

    public function getFacadeNameFromInput($facadeName)
    {
        if ($this->isUppercase($facadeName)) {
            return $facadeName;
        } else {
            return ucfirst(camel_case(strtolower($facadeName)));
        }
    }

    public function getArguments()
    {
        return array(
            array('facade', 'The name of the registered facade you want to resolve.'),
        );
    }

    public function isFacade($facade)
    {
        if (class_exists($facade)) {
            return array_key_exists('Brainwave\Support\Facades', class_parents($facade));
        } else {
            return false;
        }
    }

    private function isUppercase($string)
    {
        return (strtoupper($string) == $string);
    }
}
