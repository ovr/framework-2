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

use \Brainwave\Support\Arr;
use \Brainwave\Crypt\CryptRand;
use \Brainwave\Crypt\CryptHash;
use \Brainwave\Crypt\Interfaces\CryptInterface;

/**
 * Crypt
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Crypt implements CryptInterface
{
    /**
     * Encryption key (should be correct length for selected cipher)
     * @var string
     */
    protected $key;

    /**
     * Encryption cipher
     * @var int
     * @see http://www.php.net/manual/mcrypt.ciphers.php
     */
    protected $algo = 'rijndael-256';

    /**
     * Encryption mode
     * @var int
     * @see http://www.php.net/manual/mcrypt.constants.php
     */
    protected $mode = 'ctr';

    /**
     * [$padding description]
     * @var boolean
     */
    protected $padding = false;

    /**
     * CryptRand
     * @var CryptRand
     */
    protected $cryptRand;

    /**
     * CryptHash
     * @var CryptHash
     */
    protected $cryptHash;

    /**
     * Password Hash Type Identification (Identify Hashes)
     */
    const HASH_TYPE = 'sha256';

    /**
     * Constructor
     * @param  string $key    Encryption key
     * @param  int    $cipher Encryption algorithm
     * @param  int    $mode   Encryption mode
     * @api
     */
    public function __construct($key, $cipher = MCRYPT_RIJNDAEL_256, $mode = 'ctr')
    {
        $this->checkRequirements();

        $this->key = $key;
        $this->algo = $cipher;
        $this->mode = $mode;

        //ini CryptRand class
        $this->cryptRand(new CryptRand());
        //ini CryptRand class
        $this->cryptHash(new CryptHash($this, $this->cryptRand));
    }

    /**
     * Encrypt data returning a JSON encoded array safe for storage in a database
     * or file. The array has the following structure before it is encoded:
     * [
     *   'cdata' => 'Encrypted data, Base 64 encoded',
     *   'iv'    => 'Base64 encoded IV',
     *   'algo'  => 'Algorythm used',
     *   'mode'  => 'Mode used',
     *   'mac'   => 'Message Authentication Code'
     * ]
     *
     * @param mixed $data
     *   Data to encrypt.
     *
     * @param string $key
     *   Key to encrypt data with.
     *
     * @return string
     *   Serialized array containing the encrypted data along with some meta data.
     */
    public function encrypt($data, $key = null)
    {
        // Check if $key is null
        if (is_null($key)) {
            $key = $this->key;
        }

        // Make sure both algorithm and mode are either block or non-block.
        $isBlockCipher = mcrypt_module_is_block_algorithm($this->_algo);
        $isBlockMode   = mcrypt_module_is_block_algorithm_mode($this->_mode);
        if ($isBlockCipher !== $isBlockMode) {
            throw new \RuntimeException('You can not mix block and non-block ciphers and modes');
        }

        $module = mcrypt_module_open($this->_algo, '', $this->_mode, '');

        // Validate key length
        $this->validateKeyLength($key, $module);

        // Create IV.
        $iv = $this->cryptRand->bytes(mcrypt_enc_get_iv_size($module));

        // Init mcrypt.
        mcrypt_generic_init($module, $key, $iv);

        // Prepeare the array with data.
        $serializedData = serialize($data);

        // Enable padding of data if block cipher moode.
        if (mcrypt_module_is_block_algorithm_mode($this->mode) === true) {
            $this->padding = true;
        }

        // Add padding if enabled.
        if ($this->padding === true) {
            $block = mcrypt_enc_get_block_size($module);
            $serializedData = $this->pad($block, $serializedData);
            $encrypted['padding'] = 'PKCS7';
        }

        // Algorithm used to encrypt.
        $encrypted['algo']  = $this->algo;
        // Algorithm mode.
        $encrypted['mode']  = $this->mode;
        // Initialization vector, just a bunch of randomness.
        $encrypted['iv']    = base64_encode($iv);
        // The encrypted data.
        $encrypted['cdata'] = base64_encode(mcrypt_generic($module, $serializedData));
        // The message authentication code. Used to make sure the
        // message is valid when decrypted.
        $encrypted['mac']   = base64_encode($this->pbkdf2($encrypted['cdata'], $key, 1000, 32));

        return json_encode($encrypted);
    }

    /**
     * Strip PKCS7 padding and decrypt
     * data encrypted by encrypt().
     *
     * @param string $data  JSON string containing the encrypted data and meta information in the
     *                      excact format as returned by encrypt().
     *
     * @return mixed        Decrypted data in it's original form.
     */
    public function decrypt($data, $key = null)
    {
        // Check if $key is null
        if (is_null($key)) {
            $key = $this->key;
        }

        // Decode the JSON string
        $data = json_decode($data, true);

        $dataStructure = [
          'algo'  => true,
          'mode'  => true,
          'iv'    => true,
          'cdata' => true,
          'mac'   => true,
        ];

        if ($data === null || Arr::arrayCheck($data, $dataStructure, false) !== true) {
            throw new \RuntimeException('Invalid data passed to decrypt()');
        }
        // Everything looks good so far. Let's continue.
        $module = mcrypt_module_open($data['algo'], '', $data['mode'], '');

        // Validate key
        $this->validateKeyLength($this->key, $module);

        $block = mcrypt_enc_get_block_size($module);

        // Check MAC.
        if (base64_decode($data['mac']) != $this->pbkdf2($data['cdata'], $key, 1000, 32)) {
            throw new \InvalidArgumentException('Message authentication code invalid');
        }

        // Init mcrypt.
        mcrypt_generic_init($module, $key, base64_decode($data['iv']));

        $decrypted = rtrim(mdecrypt_generic($module, base64_decode($this->stripPadding($block, $data['cdata']))));

        // Close up.
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        // Return decrypted data.
        return unserialize($decrypted);

    }

    /**
     * Validate encryption key based on valid key sizes for selected cipher and cipher mode
     * @param  string                    $key    Encryption key
     * @param  resource                  $module Encryption module
     * @return void
     * @throws \InvalidArgumentException         If key size is invalid for selected cipher
     */
    protected function validateKeyLength($key, $module)
    {
        $keySize = strlen($key);
        $keySizeMin = 1;
        $keySizeMax = mcrypt_enc_get_key_size($module);
        $validKeySizes = mcrypt_enc_get_supported_key_sizes($module);
        if ($validKeySizes) {
            if (!in_array($keySize, $validKeySizes)) {
                throw new \InvalidArgumentException(
                    'Encryption key length must be one of: ' . implode(', ', $validKeySizes)
                );
            }
        } else {
            if ($keySize < $keySizeMin || $keySize > $keySizeMax) {
                throw new \InvalidArgumentException(sprintf(
                    'Encryption key length must be between %s and %s, inclusive',
                    $keySizeMin,
                    $keySizeMax
                ));
            }
        }
    }

    /**
     * Check the mcrypt PHP extension is loaded
     * @throws \RuntimeException If the mcrypt PHP extension is missing
     */
    protected function checkRequirements()
    {
        if (extension_loaded('mcrypt') === false) {
            throw new \RuntimeException(sprintf(
                'The PHP mcrypt extension must be installed to use the %s encryption class.',
                __CLASS__
            ));
        }
    }

    /**
    * Implement PBKDF2 as described in RFC 2898.
    * @param string $password               Password to protect.
    * @param string $salt                   Salt.
    * @param integer $count                 Iteration count.
    * @param integer $dkLen                 Derived key length.
    * @param string $hash_algo              A hash algorithm.
    * @return string                        Derived key.
    */
    public function pbkdf2($password, $salt, $count, $dkLen, $hash_algo = 'sha256')
    {
        // Hash length.
        $hLen          = strlen(hash($hash_algo, null, true));
        // Length in blocks of derived key.
        $length        = ceil($dkLen / $hLen);
        // Derived key.
        $derived_key   = '';

        // Step 1. Check dkLen.
        if ($dkLen > (2^32-1) * $hLen) {
            throw new \InvalidArgumentException('Derived key too long');
        }

        for ($block = 1; $block<=$length; $block ++) {
            // Initial hash for this block.
            $ini_block = $hash_block = hash_hmac($hash_algo, $salt . pack('N', $block), $password, true);
            // Do block iterations.
            for ($i = 1; $i<$count; $i ++) {
                // XOR iteration.
                $ini_block ^= ($hash_block = hash_hmac($hash_algo, $hash_block, $password, true));
            }
            // Append iterated block.
            $derived_key .= $ini_block;
        }
        // Returned derived key.
        return substr($derived_key, 0, $dkLen);
    }

    /**
     * PKCS7-pad data.
     * Add bytes of data to fill up the last block.
     * PKCS7 padding adds bytes with the same value that the number of bytes that are added.
     * @see http://tools.ietf.org/html/rfc5652#section-6.3
     *
     * @param integer $block            Block size.
     * @param string $data              Data to pad.
     * @return string                   Padded data.
     */
    public function pad($block, $data)
    {
        $pad = $block - (strlen($data) % $block);
        $data .= str_repeat(chr($pad), $pad);

        return $data;
    }

    /**
     * Strip PKCS7-padding.
     *
     * @param integer $block        Block size.
     * @param string $data          Padded data.
     * @return string               Original data.
     */
    public function stripPadding($block, $data)
    {
        $pad = ord($data[(strlen($data)) - 1]);

        // Check that what we have at the end of the string really is padding, and if it is remove it.
        if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $data)) {
            return substr($data, 0, -$pad);
        }
        return $data;
    }

    /**
     * Returns a unique identifier.
     *
     * @return string     Returns a unique identifier.
     */
    public function genUid()
    {
        $hex = bin2hex($this->cryptRand->Bytes(32));
        $str = substr($hex, 0, 16);
        $str .= '-' . substr($hex, 16, 8);
        $str .= '-' . substr($hex, 24, 8);
        $str .= '-' . substr($hex, 32, 8);
        $str .= '-' . substr($hex, 40, 24);
        return $str;
    }

    /**
     * Return CryptHash
     * @return \Brainwave\Crypt\CryptHash
     */
    public function hash()
    {
        return $this->cryptHash;
    }

    /**
     * Return CryptRand
     * @return \Brainwave\Crypt\CryptRand
     */
    public function rand()
    {
        return $this->cryptRand;
    }

    /**
     * Returns CryptRand class
     * @param  CryptRand $cryptRand \Brainwave\Crypt\CryptRand
     * @return \Brainwave\Crypt\Crypt
     */
    public function cryptRand(CryptRand $cryptRand)
    {
        $this->cryptRand = $cryptRand;
        return $this;
    }

    /**
     * Returns CryptHash class
     * @param  CryptHash $cryptHash \Brainwave\Crypt\CryptHash
     * @return \Brainwave\Crypt\Crypt
     */
    public function cryptHash(CryptHash $cryptHash)
    {
        $this->cryptHash = $cryptHash;
        return $this;
    }
}
