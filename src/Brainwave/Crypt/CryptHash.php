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

use \Brainwave\Crypt\Crypt;
use \Brainwave\Support\Helpers;
use \Brainwave\Crypt\CryptRand;

/**
 * CryptHash
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class CryptHash
{
    const PBKDF2    = '$pbkdf2$';
    const BCRYPT    = '$2y$';
    const BCRYPT_BC = '$2a$';
    const SHA256    = '$5$';
    const SHA512    = '$6$';
    const DRUPAL    = '$S$';

    /**
    * Default hashing method.
    */
    public $method = self::BCRYPT;

    /**
    * PBKDF2: Iteration count.
    */
    public $pbkdf2_c = 8192;

    /**
    * PBKDF2: Derived key length.
    */
    public $pbkdf2_dkLen = 128;

    /**
    * PBKDF2: Underlying hash method.
    */
    public $pbkdf2_prf = 'sha256';

    /**
    * Bcrypt: Work factor.
    */
    public $bcrypt_cost = 12;

    /**
    * SHA2: Number of rounds.
    */
    public $sha2_c = 6000;

    /**
    * Drupal: Hash length.
    */
    public $drupal_hashLen = 55;

    /**
    * Drupal: Iteration count (log 2).
    */
    public $drupal_count = 15;

    /**
     * CryptHash
     * @var CryptHash
     */
    protected $crypt;

    /**
     * CryptRand
     * @var CryptRand
     */
    protected $cryptRand;

    /**
    * Salt charsets.
    */
    public $charsets = [
    'itoa64' => './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
    ];

    /**
     * [__construct description]
     * @param Crypt     $crypt     [description]
     * @param CryptRand $cryptRand [description]
     */
    public function __construct(Crypt $crypt, CryptRand $cryptRand)
    {
        //ini CryptRand class
        $this->crypt($crypt);
        //ini CryptRand class
        $this->cryptRand($cryptRand);
    }

    /**
    * Creates a salted hash from a string.
    *
    *   @param string $str		String to hash.
    *
    *   @return string			Returns hashed string, or false on error.
    */
    public function create($str)
    {
        switch($this->method) {
            case self::BCRYPT:
                $saltRnd = $this->cryptRand->str(22, $this->charsets['itoa64']);
                $salt = sprintf('%s%s$%s', self::BCRYPT, $this->bcrypt_cost, $saltRnd);
                $hash = crypt($str, $salt);
                break;

            case self::PBKDF2:
                $salt = $this->cryptRand->bytes(64);
                $hash = $this->crypt->pbkdf2($str, $salt, $this->pbkdf2_c, $this->pbkdf2_dkLen, $this->pbkdf2_prf);

                $hash = sprintf(
                    '$pbkdf2$c=%s&dk=%s&f=%s$%s$%s',
                    $this->pbkdf2_c,
                    $this->pbkdf2_dkLen,
                    $this->pbkdf2_prf,
                    base64_encode($hash),
                    base64_encode($salt)
                );
                break;

            case self::DRUPAL:
                $setting  = '$S$';
                $setting .= $this->charsets['itoa64'][$this->drupal_count];
                $setting .= $this->b64Encode($this->cryptRand->bytes(6), 6);

                return substr($this->phpassHash($str, $setting), 0, $this->drupal_hashLen);
            break;

            case self::SHA256:
            case self::SHA512:
                $saltRnd = $this->cryptRand->str(16, $this->charsets['itoa64']);
                $salt = sprintf('%srounds=%s$%s', $this->method, $this->sha2_c, $saltRnd);
                $hash = crypt($str, $salt);
                break;
        }

        if (strlen($hash) > 13) {
            return $hash;
        }
        return false;
    }

    /**
    * Check a string against a hash.
    *
    * @param string $str		String to check.
    *
    * @param string $hash		The hash to check the string against.
    *
    * @return boolean|null				Returns true on match.
    */
    public function check($str, $hash)
    {
        $hashInfo = $this->getInfo($hash);

        switch($hashInfo['algo']) {
            case self::PBKDF2:
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
                break;

            case self::DRUPAL:
                $test = strpos($this->phpassHash($str, $hash), $hash);
                if ($test === false || $test !== 0) {
                    return false;
                }
                return true;
                break;

            case self::BCRYPT:
            case self::BCRYPT_BC:
            case self::SHA256:
            case self::SHA512:
                return Helpers::timingSafe(crypt($str, $hash), $hash);
                break;

            default:
                /* Not any of the supported formats. Try plain hash methods. */
                $hashLen = strlen($hash);
                switch($hashLen) {
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
                break;
        }
    }

    /**
    * Returns settings used to generate a hash.
    *
    * @param string $hash	Hash to get settings for.
    *
    * @return array			Returns an array with settings used to create $hash.
    */
    public function getInfo($hash)
    {
        $regex_pattern = '/^\$[a-z, 1-6]{1,6}\$/i';
        preg_match($regex_pattern, $hash, $matches);

        if (sizeof($matches) > 0) {
            list($method) = $matches;
        } else {
            $method = null;
        }

        switch($method) {
            case self::SHA256:
            case self::SHA512:
            case self::PBKDF2:
                $param = [];
                list( , , $params) = explode('$', $hash);
                parse_str($params, $param);
                $info['options'] = $param;
                break;

            case self::BCRYPT:
                list( , , $cost) = explode('$', $hash);
                $info['options'] = [
                    'cost' => $cost,
                ];
                break;
        }
        $info['algo'] = $method;
        return $info;
    }

    /**
     * [phpassHash description]
     * @param  string $password [description]
     * @param  string $setting  [description]
     * @param  string $method   [description]
     * @return [type]           [description]
     */
    private function phpassHash($password, $setting, $method = 'sha512')
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
     * [b64Encode description]
     * @param  string $input [description]
     * @param  integer $count [description]
     * @return [type]        [description]
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

    /**
     * Returns Crypt class
     * @param  CryptHash $crypt \Brainwave\Crypt\Crypt
     * @return \Brainwave\Crypt\CryptHash
     */
    public function crypt(Crypt $crypt)
    {
        $this->crypt = $crypt;
        return $this;
    }

    /**
     * Returns CryptRand class
     * @param  CryptRand $cryptRand \Brainwave\Crypt\CryptRand
     * @return \Brainwave\Crypt\CryptHash
     */
    public function cryptRand(CryptRand $cryptRand)
    {
        $this->cryptRand = $cryptRand;
        return $this;
    }
}
