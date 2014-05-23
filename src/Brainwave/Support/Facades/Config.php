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
class Config extends Facades
{
    protected static function getFacadeAccessor() { return self::$brainwave; }

    public static function setArray($name)
    {
        return self::$brainwave->config($name);
    }

    public static function set($name, $value = null)
    {
        return self::$brainwave->config($name, $value);
    }

    public static function get($name)
    {
        return self::$brainwave->config($name);
    }

    public static function loadFile($parser, $file)
    {
        return self::$brainwave->loadConfig($parser, $file);
    }
}
