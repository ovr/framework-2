<?php
namespace Brainwave\Contracts\Encrypter;

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
 * Encrypter
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Encrypter
{
   /**
     * Encrypt data returning a JSON encoded array safe for storage in a database
     * or file. The array has the following structure before it is encoded:
     *
     * [
     *   'cdata' => 'Encrypted data, Base 64 encoded',
     *   'iv'    => 'Base64 encoded IV',
     *   'algo'  => 'Algorythm used',
     *   'mode'  => 'Mode used',
     *   'mac'   => 'Message Authentication Code'
     * ]
     *
     * @param  mixed $data Data to encrypt.
     * @param  string $key Key to encrypt data with.
     *
     * @return string Serialized array containing the encrypted data
     *                along with some meta data.
     */
    public function encrypt($data, $key = null);

    /**
     * Strip PKCS7 padding and decrypt
     * data encrypted by encrypt().
     *
     * @param  string $data  JSON string containing the encrypted data and meta information in the
     *                      excact format as returned by encrypt().
     *
     * @return mixed        Decrypted data in it's original form.
     */
    public function decrypt($data, $key = null);
}
