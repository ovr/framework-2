<?php namespace Brainwave\Support\Facades;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Brainwave\Support\Facades;

/**
 * 
 */
class Resource extends Facades
{
    protected static function getFacadeAccessor() { return self::$brainwave; }

    public static function set($name)
    {
        return self::$app->getResources($name);
    }
}
