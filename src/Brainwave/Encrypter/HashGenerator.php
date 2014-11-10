<?php
namespace Brainwave\Crypt;

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

use \Brainwave\Support\Helpers;
use \Brainwave\Encrypter\Encrypter;
use \RandomLib\Factory as RandomLib;

/**
 * HashGenerator
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class HashGenerator
{
    /**
     * All registered methods
     *
     * @var array
     */
    protected $registeredMethods = [
        'pbkdf2'    => '$pbkdf2$',
        'bcrypt'    => '$2y$',
        'bcrypt.bc' => '$2a$',
        'sha256'    => '$5$',
        'sha512'    => '$6$',
        'drupal'    => '$S$'
    ];

    /**
     * PBKDF2: Iteration count.
     *
     * @var int
     */
    public $pbkdf2C = 8192;

    /**
     * PBKDF2: Derived key length.
     *
     * @var int
     */
    public $pbkdf2DkLen = 128;

    /**
     * PBKDF2: Underlying hash method.
     *
     * @var string
     */
    public $pbkdf2Prf = 'sha256';

    /**
     * Bcrypt: Work factor.
     *
     * @var int
     */
    public $bcryptCost = 12;

    /**
     * SHA2: Number of rounds.
     *
     * @var int
     */
    public $sha2C = 6000;

    /**
     * Drupal: Hash length.
     *
     * @var int
     */
    public $drupalHashLen = 55;

    /**
     * Drupal: Iteration count (log 2).
     *
     * @var int
     */
    public $drupalCount = 15;

    /**
     * Encrypter
     *
     * @var \Brainwave\Encrypter\Encrypter
     */
    protected $crypt;

    /**
     * Rand generator
     *
     * @var RandGenerator
     */
    protected $randomLib;

    /**
     * Salt charsets.
     *
     * @var array
     */
    public $charsets = [
        'itoa64' => './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
    ];

    /**
     * HashGenerator
     *
     * @param Encrypter     $crypt
     * @param RandGenerator $randomLib
     */
    public function __construct(Encrypter $crypt, RandomLib $randomLib)
    {
        $this->crypt     = $crypt;
        $this->randomLib = $randomLib;
    }

    /**
     * Makes a salted hash from a string.
     *
     * @param  string $str    string to hash.
     * @param  string $method default method 'bcrypt'.
     *
     * @return string|boolen  returns hashed string, or false on error.
     */
    public function make($str, $method = 'bcrypt')
    {
        if (isset($this->registeredMethods[$method])) {
            throw new \Exception('Method {$method} dont exist.');
        }

        switch($method) {

            case 'pbkdf2':
                $hash = $this->makePbkdf2($str);
                break;

            case 'bcrypt':
            case 'bcrypt.bc':
                $hash = $this->makeBcrypt($str);
                break;

            case 'drupal':
                return $this->makeDrupal($str);
                break;

            case 'sha256':
            case 'sha512':
                $hash = $this->makeSha($str, $method);
                break;
        }

        if (strlen($hash) > 13) {
            return $hash;
        }

        return false;
    }

    /**
     * Create Pbkdf2 hash
     *
     * @param  string $str string to hash.
     *
     * @return string
     */
    private function makePbkdf2($str)
    {
        $salt = $this->randomLib->bytes(64);

        if (function_exists('hash_pbkdf2')) {
            $pbkdf2 = hash_pbkdf2($this->pbkdf2Prf, $str, $salt, $this->pbkdf2C, $this->pbkdf2DkLen);
        } else {
            $pbkdf2 = $this->crypt->pbkdf2(
                $str,
                $salt,
                $this->pbkdf2C,
                $this->pbkdf2DkLen,
                $this->pbkdf2Prf
            );
        }

        return sprintf(
            "{$this->registeredMethods['pbkdf2']}=%s&dk=%s&f=%s$%s$%s",
            $this->pbkdf2C,
            $this->pbkdf2DkLen,
            $this->pbkdf2Prf,
            base64_encode($pbkdf2),
            base64_encode($salt)
        );
    }

    /**
     * Create Bcrypt hash
     *
     * @param  string $str string to hash.
     *
     * @return string
     */
    private function makeBcrypt($str)
    {
        $saltRnd = $this->randomLib->str(22, $this->charsets['itoa64']);
        $salt = sprintf('%s%s$%s', $this->registeredMethods['bcrypt'], $this->bcryptCost, $saltRnd);

        return crypt($str, $salt);
    }

    /**
     * Create Drupal hash
     *
     * @param  string $str string to hash.
     *
     * @return string
     */
    private function makeDrupal($str)
    {
        $setting  = $this->registeredMethods['drupal'];
        $setting .= $this->charsets['itoa64'][$this->drupalCount];
        $setting .= $this->b64Encode($this->randomLib->bytes(6), 6);

        return substr($this->phpassHash($str, $setting), 0, $this->drupalHashLen);
    }

    /**
     * Create Sha hash
     *
     * @param  string $str    string to hash.
     * @param  string $method default method 'sha512'.
     *
     * @return string
     */
    private function makeSha($str, $method = 'sha512')
    {
        $saltRnd = $this->randomLib->str(16, $this->charsets['itoa64']);
        $salt    = sprintf(
            '%srounds=%s$%s',
            $this->registeredMethods[$method],
            $this->sha2C,
            $saltRnd
        );

        return crypt($str, $salt);
    }

    /**
     * Check a string against a hash.
     *
     * @param  string       $str  String to check.
     * @param  string       $hash The hash to check the string against.
     *
     * @return boolean|null       Returns true on match.
     */
    public function check($str, $hash)
    {
        $hashInfo = $this->getEncoding($hash);
        $method = $this->registeredMethods;

        switch($hashInfo['algo']) {
            case $method['pbkdf2']:
                $this->checkPbkdf2($str, $hash);
                break;

            case $method['drupal']:
                $this->checkDrupal($str, $hash);
                break;

            case $method['bcrypt']:
            case $method['bcrypt.bc']:
            case $method['sha256']:
            case $method['sha512']:
                $this->checkBcryptSha($str, $hash);
                break;

            default:
                $this->checkHashLen($str, $hash);
                break;
        }
    }

    /**
     * Pbkdf2 format
     *
     * @param  string $str  String to check.
     * @param  string $hash The hash to check the string against.
     *
     * @return boolean|null Returns true on match.
     */
    private function checkPbkdf2($str, $hash)
    {
        $param = [];
        list( , , $params, $hash, $salt) = explode('$', $hash);
        parse_str($params, $param);

        return Helpers::timingSafe(
            $this->crypt->pbkdf2(
                $str,
                base64_decode($salt),
                $param['c'],
                $param['dk'],
                $param['f']
            ),
            base64_decode($hash)
        );
    }

    /**
     * Drupal format
     *
     * @param  string $str  String to check.
     * @param  string $hash The hash to check the string against.
     *
     * @return boolean|null Returns true on match.
     */
    private function checkDrupal($str, $hash)
    {
        $test = strpos($this->phpassHash($str, $hash), $hash);

        if ($test === false || $test !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Bcrypt and sha format
     *
     * @param  string $str  String to check.
     * @param  string $hash The hash to check the string against.
     *
     * @return boolean|null Returns true on match.
     */
    private function checkBcryptSha($str, $hash)
    {
        return Helpers::timingSafe(crypt($str, $hash), $hash);
    }

    /**
     * Not any of the supported formats.
     * Try plain hash methods.
     *
     * @param  string $str  String to check.
     * @param  string $hash The hash to check the string against.
     *
     * @return boolean|null Returns true on match.
     */
    private function checkHashLen($str, $hash)
    {
        $hash = strlen($hash);

        switch($hash) {
            case 32:
                $mode = 'md5';
                break;

            case 40:
                $mode = 'sha1';
                break;

            case 64:
                $mode = 'sha256';
                break;

            case 128:
                $mode = 'sha512';
                break;

            default:
                return false;
        }

        return Helpers::timingSafe(hash($mode, $str), $hash);
    }

    /**
     * Returns settings used to generate a hash.
     *
     * @param  string $hash Hash to get settings for.
     *
     * @return array        Returns an array with settings used to make $hash.
     */
    public function getEncoding($hash)
    {
        preg_match('/^\$[a-z, 1-6]{1,6}\$/i', $hash, $matches);

        $registeredMethod = $this->registeredMethods;

        if (sizeof($matches) > 0) {
            list($method) = $matches;
        } else {
            $method = null;
        }

        switch($method) {
            case $regMethod['sha256']:
            case $regMethod['sha512']:
            case $regMethod['pbkdf2']:
                $param = [];
                list( , , $params) = explode('$', $hash);
                parse_str($params, $param);
                $info['options'] = $param;
                break;

            case $regMethod['bcrypt']:
            case $regMethod['bcrypt.bc']:
                list( , , $cost) = explode('$', $hash);
                $info['options'] = ['cost' => $cost];
                break;
        }

        $info['algo'] = $method;

        return $info;
    }

    /**
     * hashPassword
     *
     * @param  string $password password to hash
     * @param  string $setting  hash settings
     * @param  string $method   method to hash
     *
     * @return string
     */
    public function hashPassword($password, $setting, $method = 'sha512')
    {
        /* First 12 characters are the settings. */
        $setting = substr($setting, 0, 12);
        $salt    = substr($setting, 4, 8);
        $count   = 1 << strpos($this->charsets['itoa64'], $setting[3]);


        $hash = hash($method, $salt . $password, true);

        do {
            $hash = hash($method, $hash . $password, true);
        } while (--$count);

        $len = strlen($hash);
        $output = $setting . $this->b64Encode($hash, $len);
        $expected = 12 + ceil((8 * $len) / 6);

        return substr($output, 0, $expected);
    }

    /**
     * Check a password against a hash.
     *
     * @param  stirng $password
     * @param  string $passwordHash
     *
     * @return boolen
     */
    public function checkPassword($password, $passwordHash)
    {
        return $this->check($password, $passwordHash);
    }

    /**
     * b64Encode
     *
     * @param  string $input
     * @param  integer $count
     *
     * @return string
     */
    private function b64Encode($input, $count)
    {
        $itoa64 = $this->charsets['itoa64'];

        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3f];

            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }

            $output .= $itoa64[($value >> 6) & 0x3f];

            if ($i++ >= $count) {
                break;
            }

            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }

            $output .= $itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            $output .= $itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }
}
