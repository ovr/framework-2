<?php namespace Brainwave\Event;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Event\EventManagerInterface;

/**
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
     * @param var Workbench $app
     */
    public function __construct(Workbench $app)
    {
        $this->app = $app;
    }

    /**
     * Hock an event
     *
     * @param  string   $event    Event/Hook name
     * @param  callable $callback EventManager
     * @param  integer  $priority 0 = high, 10 = low
     */
    public function hook($event, callable $callback, $priority = 10)
    {
        $this->app->hook($event, $callback, $priority);
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
     * Trigger a hook
     * @param string $name
     * @param mixed $hookArg
     */
    public function applyHook($name, $hookArg = null)
    {
        $this->applyHook($event, $hookArg);
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
            $hooks[$name] = array(array());
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
    public function triggerChain($event, array $args = array())
    {
        $this->applyChain($event, $args);
    }
}
