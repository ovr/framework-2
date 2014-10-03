<?php
namespace Brainwave\Cache\Tag;

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

use \Brainwave\Cache\TagSet;
use \Brainwave\Cache\TaggedCache;

/**
 * TaggableStore
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
abstract class TaggableStore
{

    /**
     * Begin executing a new tags operation.
     *
     * @param  string  $name
     * @return \Brainwave\Cache\TaggedCache
     */
    public function section($name)
    {
        return $this->tags($name);
    }

    /**
     * Begin executing a new tags operation.
     *
     * @param  string  $names
     * @return \Brainwave\Cache\TaggedCache
     */
    public function tags($names)
    {
        return new TaggedCache(
            $this,
            new TagSet($this, is_array($names) ? $names : func_get_args())
        );
    }
}
