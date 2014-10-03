<?php
namespace Brainwave\Cache;

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

use \Carbon\Carbon;
use \Brainwave\Cache\Psr\Cache\CacheItemInterface;
use \Brainwave\Cache\Exception\InvalidArgumentException;

/**
 * CacheItem
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class CacheItem implements CacheItemInterface
{
    const DEFAULT_EXPIRATION = 'now +1 year';

    /**
     * @var \Brainwave\Cache\Interfaces\DriverInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var bool
     */
    protected $isHit = false;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * The expiration date.
     * @var \DateTime
     */
    protected $expiration;

    /**
     * Constructor.
     *
     * @param string                    $key
     * @param mixed                     $value
     * @param boolean    $ttl
     * @param bool                      $hit
     */
    public function __construct(
        $key,
        $value = null,
        $ttl = null,
        $hit = false
    ) {
        if (strpbrk($key, '{}()/\@:')) {
            throw new InvalidArgumentException(
                'Item key contains an invalide character.' . $key
            );
        }

        $this->key    = $key;
        $this->value = $value;
        $this->hit = $hit;
        $this->setExpiration($ttl);
    }

    /**
     * Returns the key for the current cache item.
     *
     * @return string    The key string for this cache item.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Lets get items from the driver
     *
     * @return mixed
     */
    public function get()
    {
        return $this->isHit ? $this->value : null;
    }

    /**
     * @param null $value
     * @return CacheItem
     */
    public function set($value = null)
    {
        $this->isHit = true; // We just set it, so it exists
        $this->value = $value;

        return $this;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * @return bool
     */
    public function isHit()
    {
        return $this->isHit;
    }

    /**
     * Confirms if the cache item exists in the cache.
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->isHit;
    }

    /**
     * Sets the expiration for this cache item.
     *
     * @param [type]    $ttl
     * @return static   The called object.
     */
    public function setExpiration($ttl = null)
    {
        if ($ttl instanceof \DateTime) {
            $this->expiration = $ttl;
        } elseif (is_numeric($ttl)) {
            $this->expiration = new \DateTime('now +' . $ttl . ' seconds');
        } elseif (is_null($ttl)) {
             // stored permanently or for as long as the default value.
            $this->expiration = new \DateTime(self::DEFAULT_EXPIRATION);
        } else {
            throw new InvalidArgumentException(
                'Integer or \DateTime object expected.'
            );
        }

        return $this;
    }

    /**
     * Returns the expiration time of a not-yet-expired cache item.
     *
     * @return \DateTime    The timestamp at which this cache item will expire.
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Returns the time to live in second.
     *
     * @return integer
     */
    public function getTtlInSecond()
    {
        return $this->expiration->format('U') - time();
    }
}
