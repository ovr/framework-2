<?php namespace Brainwave\Routing\Interfaces;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Workbench\Workbench;

/**
 * 
 */
interface ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Workbench $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Workbench $app);
}
