<?php namespace Brainwave\Event\Interfaces;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface EventManagerInterface
 *
 * Describs the required interface for
 * an event-manager compatible module.
 */
interface EventManagerInterface
{
    /**
     * Hock an event
     *
     * @param  string   $event    Event/Hook name
     * @param  callable $callback EventManager
     * @param  integer  $priority 0 = high, 10 = low
     */
    public function hook($event, callable $callback, $priority = 10);

    /**
     * Alias for self::hook()
     *
     * @param             $event
     * @param callable     callable $callback
     * @param int         $priority
     */
    public function addEventListener($event, callable $callback, $priority = 10);

    /**
     * Trigger a hook
     * @param string $name
     * @param mixed $hookArg
     */
    public function applyHook($name, $hookArg = null);

    /**
     * Triger a chained hook
     * the first callback to return a non-null value will be returned
     *
     * @param string $@name the hook name
     * @param mixed $hookArg (Optional) Argument for hooked functions
     * @return mixed|void
     */
    public function applyChain($name, $hookArg = null);

    /**
     * Alias for self::applyChain()
     * @param string $event Event name
     * @param array  $args  Array of arguments to pass to callback
     * @return mixed|void
     */
    public function triggerChain($event, array $args = array());
}
