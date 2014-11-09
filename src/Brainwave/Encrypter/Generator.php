<?php
namespace Brainwave\Crypt;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

/**
 * Generator
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Generator
{
    public $charset = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
    * Generate pseudorandom bytes.
    *
    * @param  integer $len
    *
    * @return string
    */
    public function bytes($len)
    {

        // Code inspired by this blogpost by Enrico Zimuel
        // http://www.zimuel.it/en/strong-cryptography-in-php/
        $strong = false;

        if (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($len, $strong);
            if ($strong === true) {
                return $rnd;
            }
        }

        // Either we dont have the OpenSSL library or the data returned was not
        // considered secure. Fall back on this less secure code.
        if (function_exists('mcrypt_create_iv')) {
            $rnd = mcrypt_create_iv($len, MCRYPT_DEV_URANDOM);
            return $rnd;
        }

        // Either we dont have the MCrypt library and OpenSSL library or the data returned was not
        // considered secure. Fall back on this less secure code.
        $rnd = '';

        for ($i=0; $i<$len; $i++) {
            $sha = hash('sha256', mt_rand());
            $char = mt_rand(0, 30);
            $rnd .= chr(hexdec($sha[$char].$sha[$char+1]));
        }

        return (binary) $rnd;
    }

    /**
    * Generate a random integer.
    *
    * @param  integer $min
    * @param  integer $max
    *
    * @return integer
    */
    public function int($min, $max)
    {
        $delta = $max-$min;
        $bytes = ceil($delta/256);
        $rnd = $this->bytes($bytes);
        $add = 0;

        for ($i = 0; $i < $bytes; $i++) {
            $add += ord($rnd[$i]);
        }

        $add = $add % ($delta + 1);

        return $min + $add;
    }

    /**
    * Generate a random string.
    *
    * @param  integer $len
    * @param  string  $charset
    *
    * @return string
    */
    public function str($len, $charset = null)
    {
        if (is_null($charset)) {
            $charset = $this->charset;
        }

        $str = '';

        for ($i = 0; $i < $len; $i++) {
            $pos = $this->int(0, strlen($charset)-1);
            $str .= $charset[$pos];
        }

        return $str;
    }

    /**
    * Return random hexadecimal data.
    *
    * @param  integer $len
    *
    * @return string
    */
    public function hex($len)
    {
        return bin2hex($this->bytes($len));
    }

    /**
    * Return one or more random random keys from an array.
    *
    * @param  array   $array Input array.
    * @param  integer $num   Number of keys to pick.
    *
    * @return mixed          String with the key, or array containing multiple keys.
    */
    public function arrayRand($array, $num = 1)
    {
        $keys = array_keys($array);
        $numKeys = sizeof($keys);

        if ($num == 1) {
            return $keys[$this->int(0, $numKeys-1)];
        }

        for ($i=0; $i < $num; $i++) {
            $picked[] = $keys[$this->int(0, $numKeys-1)];
        }

        return $picked;
    }

    /**
    * Return a random boolean.
    *
    * @return boolean
    */
    public function bool()
    {
        $byte = $this->bytes(1);

        if ((ord($byte) + 1) % 2 === 0) {
            return false;
        }

        return true;
    }
}
