<?php namespace Brainwave\Support;

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
class FacadesResolver
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
            array('facade', InputArgument::REQUIRED, 'The name of the registered facade you want to resolve.'),
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
