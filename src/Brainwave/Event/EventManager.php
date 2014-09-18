<?php
namespace Brainwave\Event;

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

use \Brainwave\Workbench\Workbench;
use \Brainwave\Event\Interfaces\EventManagerInterface;

/**
 * EventManager
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class EventManager implements EventManagerInterface
{
    /**
     * Workbench
     * @var \Brainwave\Workbench\Workbench
     */
    protected $app;

    /**
     * Application hooks
     * @var array
     */
    protected $hooks = array(
        'before' => [[]],
        'before.router' => [[]],
        'before.dispatch' => [[]],
        'after.dispatch' => [[]],
        'after.router' => [[]],
        'after' => [[]]
    );

    /**
     * @param var Workbench $app
     */
    public function __construct(Workbench $app)
    {
        $this->app = $app;
    }

    /**
     * Assign hook
     * @param  string $name     The hook name
     * @param  mixed  $callable A callable object
     * @param  int    $priority The hook priority; 0 = high, 10 = low
     * @api
     */
    public function hook($event, callable $callable, $priority = 10)
    {
        if (!isset($this->hooks[$event])) {
            $this->hooks[$event] = [[]];
        }
        if (is_callable($callable)) {
            $this->hooks[$event][(int) $priority][] = $callable;
        }
    }

    /**
     * Alias for self::hook()
     *
     * @param             $event
     * @param callable     callable $callback
     * @param int         $priority
     */
    public function addEventListener($event, callable $callback, $priority = 10)
    {
        $this->hook($event, $callback, $priority);
    }

    /**
     * Invoke hook
     * @param  string $name    The hook name
     * @param  mixed  $hookArg (Optional) Argument for hooked functions
     * @api
     */
    public function applyHook($name, $hookArg = null)
    {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = [[]];
        }
        if (!empty($this->hooks[$name])) {
            // Sort by priority, low to high, if there's more than one priority
            if (count($this->hooks[$name]) > 1) {
                ksort($this->hooks[$name]);
            }
            foreach ($this->hooks[$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        call_user_func($callable, $hookArg);
                    }
                }
            }
        }
    }

    /**
     * Triger a chained hook
     * the first callback to return a non-null value will be returned
     *
     * @param string $@name the hook name
     * @param mixed $hookArg (Optional) Argument for hooked functions
     * @return mixed|void
     */
    public function applyChain($name, $hookArg = null)
    {
        $hooks = $this->app->getHooks();

        if (!isset($hooks[$name])) {
            $hooks[$name] = [[]];
        }
        if (!empty($hooks[$name])) {
            // Sort by priority, low to hight, if there's more than one priority
            if (count($hooks[$name]) > 1) {
                ksort($hooks[$name]);
            }
            foreach ($hooks[$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($variable as $key => $value) {
                        $v = call_user_func($callable, $hookArg);
                        if ($v !== null) {
                            return $v;
                        }
                    }
                }
            }
        }
    }

    public function trigger($name, $hookArg = null)
    {
        $this->applyHook($name, $hookArg);
    }

    /**
     * Alias for self::applyChain()
     * @param string $event Event name
     * @param array  $args  Array of arguments to pass to callback
     * @return mixed|void
     */
    public function triggerChain($event, array $args = [])
    {
        $this->applyChain($event, $args);
    }

    /**
     * Get hook listeners
     *
     * Return an array of registered hooks. If `$name` is a valid
     * hook name, only the listeners attached to that hook are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are hook names and whose values are arrays of listeners.
     *
     * @param  string     $name A hook name (Optional)
     * @return array|null
     * @api
     */
    public function getHooks($name = null)
    {
        if (!is_null($name)) {
            return isset($this->hooks[(string) $name]) ? $this->hooks[(string) $name] : null;
        } else {
            return $this->hooks;
        }
    }

    /**
     * Clear hook listeners
     *
     * Clear all listeners for all hooks. If `$name` is
     * a valid hook name, only the listeners attached
     * to that hook will be cleared.
     *
     * @param  string $name A hook name (Optional)
     * @api
     */
    public function clearHooks($name = null)
    {
        if (!is_null($name) && isset($this->hooks[(string) $name])) {
            $this->hooks[(string) $name] = [[]];
        } else {
            foreach ($this->hooks as $key => $value) {
                $this->hooks[$key] = [[]];
            }
        }
    }
}
