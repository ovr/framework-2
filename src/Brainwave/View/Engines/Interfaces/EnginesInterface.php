<?php namespace Brainwave\View\Engines\Interfaces;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface EnginesInterface
{
   /**
    * Get the evaluated contents of the view.
    *
    * @param  array   $data
    * @return string
    */
    public function get(array $data = array());

   /**
    * Set path
    *
    * @param string $path
    * @return $this \Brainwave\View\Engines
    */
    public function set($path);
}
