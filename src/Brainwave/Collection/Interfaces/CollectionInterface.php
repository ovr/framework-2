<?php
namespace Brainwave\Collection\Interfaces;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Crypt\Interfaces\CryptInterface;

/**
 * CollectionInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface CollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @param string $key
     *
     * @return void
     */
    public function set($key, $value);

    /**
     * @param string $key
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
    public function encrypt(CryptInterface $crypt);

    /**
     * @return void
     */
    public function decrypt(CryptInterface $crypt);
}
