<?php namespace Brainwave\Cache\Driver;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Cache\Interfaces\CacheInterface;

abstract class AbstractCache implements CacheInterface
{
	public function __construct(array $options = array())
	{

	}
}