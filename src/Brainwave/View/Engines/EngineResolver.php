<?php
namespace Brainwave\View\Engines;

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

/**
 * EngineResolver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class EngineResolver
{
    /**
     * The array of engine resolvers.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * The resolved engine instances.
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * Register a new engine resolver.
     * The engine string typically corresponds to a file extension.
     *
     * @param string   $engine
     * @param \Closure $resolver
     *
     * @return void
     */
    public function register($engine, \Closure $resolver)
    {
        $this->resolvers[$engine] = $resolver;
    }

    /**
     * Resolver an engine instance by name.
     *
     * @param string $engine
     *
     * @return \Brainwave\Contracts\View\Engines
     */
    public function resolve($engine)
    {
        if (!isset($this->resolved[$engine])) {
            $this->resolved[$engine] = call_user_func($this->resolvers[$engine]);
        }

        return $this->resolved[$engine];
    }
}
