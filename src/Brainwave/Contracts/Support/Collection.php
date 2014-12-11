<?php
namespace Brainwave\Contracts\Support;

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
 */

use Brainwave\Contracts\Encrypter\Encrypter as EncrypterContract;

/**
 * Collection
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Collection extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @param string $default
     */
    public function get($key, $default = null);

    /**
     * @return void
     */
    public function replace(array $items);

    public function all();

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key);

    /**
     * @return void
     */
    public function clear();

    /**
     * @return void
     */
    public function encrypt(EncrypterContract $crypt);

    /**
     * @return void
     */
    public function decrypt(EncrypterContract $crypt);
}
