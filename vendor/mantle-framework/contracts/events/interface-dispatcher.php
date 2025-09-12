<?php
/**
 * Dispatcher interface file.
 *
 * @package Mantle
 */

namespace Mantle\Contracts\Events;

/**
 * Event Dispatcher Contract
 */
interface Dispatcher {
	/**
	 * Register an event listener with the dispatcher.
	 *
	 * @param  string|string[] $events
	 * @param  string|callable $listener
	 */
	public function listen( string|array $events, string|callable $listener ): void;

	/**
	 * Determine if a given event has listeners.
	 *
	 * @param  string $event_name
	 */
	public function has_listeners( string $event_name ): bool;

	/**
	 * Register an event subscriber with the dispatcher.
	 *
	 * @param  object|string $subscriber
	 */
	public function subscribe( object|string $subscriber ): void;

	/**
	 * Dispatch an event and call the listeners.
	 *
	 * @param  string|object $event Event name.
	 * @param  mixed         ...$payload Event payload.
	 */
	public function dispatch( string|object $event, mixed ...$payload ): mixed;

	/**
	 * Remove a set of listeners from the dispatcher.
	 *
	 * @param string               $event Event to remove.
	 * @param callable|string|null $listener Listener to remove.
	 * @param int                  $priority Priority of the listener.
	 */
	public function forget( string $event, callable|string|null $listener = null, int $priority = 10 ): void;
}
