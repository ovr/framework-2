<?php
namespace Brainwave\Contracts\Hashing;

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

/**
 * HashGenerator
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface HashGenerator
{
    /**
     * Makes a salted hash from a string.
     *
     * @param  string $str    string to hash.
     * @param  string $method default method 'bcrypt'.
     *
     * @return string|boolen  returns hashed string, or false on error.
     */
    public function make($str, $method = 'bcrypt');

    /**
     * Check a string against a hash.
     *
     * @param  string       $str  String to check.
     * @param  string       $hash The hash to check the string against.
     *
     * @return boolean|null       Returns true on match.
     */
    public function check($str, $hash);

    /**
     * Returns settings used to generate a hash.
     *
     * @param  string $hash Hash to get settings for.
     *
     * @return array        Returns an array with settings used to make $hash.
     */
    public function getEncoding($hash);
}