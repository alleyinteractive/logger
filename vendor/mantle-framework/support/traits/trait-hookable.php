<?php
/**
 * Hookable trait file
 *
 * @package Mantle
 */

namespace Mantle\Support\Traits;

use Mantle\Support\Attributes\Action;
use Mantle\Support\Attributes\Filter;
use Mantle\Support\Attributes\Hookable\Allow_Legacy_Duplicate_Registration;
use Mantle\Support\Collection;
use Mantle\Support\Service_Provider;
use Mantle\Support\Str;
use ReflectionAttribute;
use ReflectionClass;

use function Mantle\Support\Helpers\collect;

/**
 * Register all hooks on a class.
 *
 * Collects all of the `Action`/`Filter` attribute methods as well as the
 * `on_{hook}` and `on_{hook}_at_{priority}` methods and registers them with the
 * respective WordPress hooks.
 *
 * Attributes are the preferred way to register hooks but the method naming
 * convention to define the hook name is still supported for backwards
 * compatibility.
 *
 * @phpstan-type HookItem array{type: string, hook: string, method: string, priority: int}
 */
trait Hookable {
	/**
	 * Flag to determine if the hooks have been registered.
	 */
	protected bool $hooks_registered = false;

	/**
	 * Reflection class instance.
	 */
	private ReflectionClass $reflection;

	/**
	 * Constructor (can be overridden by the trait user).
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Boot all actions and attribute methods on the service provider.
	 *
	 * Collects all of the `on_{hook}`, `on_{hook}_at_{priority}`,
	 * `action__{hook}`, and `filter__{hook}` methods as well as the attribute
	 * based `#[Action]` and `#[Filter]` methods and registers them with the
	 * respective WordPress hooks.
	 */
	protected function register_hooks(): void {
		if ( $this->hooks_registered ) {
			return;
		}

		$this->reflection = new ReflectionClass( static::class );

		$this->collect_action_methods()
			->merge( $this->collect_attribute_hooks() )
			->unique()
			->each(
				function ( array $item ): void {
					if ( $this->use_event_dispatcher() ) {
						if ( 'action' === $item['type'] ) {
							\Mantle\Support\Helpers\add_action( $item['hook'], [ $this, $item['method'] ], $item['priority'] );
						} else {
							\Mantle\Support\Helpers\add_filter( $item['hook'], [ $this, $item['method'] ], $item['priority'] );
						}

						return;
					}

					// Use the default WordPress action/filter methods.
					if ( 'action' === $item['type'] ) {
						\add_action( $item['hook'], [ $this, $item['method'] ], $item['priority'], 999 );
					} else {
						\add_filter( $item['hook'], [ $this, $item['method'] ], $item['priority'], 999 );
					}
				},
			);

		$this->hooks_registered = true;
	}

	/**
	 * Collect all action methods from the service provider.
	 *
	 * @phpstan-return Collection<int, HookItem>
	 */
	private function collect_action_methods(): Collection {
		// Determine if the legacy attribute is on the class to allow for duplicate
		// hook registration when a action method has an attribute.
		$has_legacy_attribute = collect(
			( $this->reflection )->getAttributes( Allow_Legacy_Duplicate_Registration::class ),
		)->is_not_empty();

		return collect( get_class_methods( static::class ) )
			->filter(
				static fn ( string $method ) => Str::starts_with( $method, [ 'on_', 'action__', 'filter__' ] )
			)
			->map(
				function ( string $method ) use ( $has_legacy_attribute ) {
					// Check if the method has any Action or Filter attributes. If it does
					// and the legacy attribute is not present on the class, ignore the
					// method name.
					if (
						! $has_legacy_attribute
						&& collect( $this->reflection->getMethod( $method )->getAttributes( Filter::class, ReflectionAttribute::IS_INSTANCEOF ) )->is_not_empty()
					) {
						return null;
					}

					$type = match ( true ) {
						Str::starts_with( $method, 'filter__' ) => 'filter',
						default => 'action',
					};

					$hook = match ( true ) {
						Str::starts_with( $method, 'on_' ) => Str::after( $method, 'on_' ),
						default => Str::after( $method, $type . '__' ),
					};

					$priority = 10;

					if ( Str::contains( $hook, '_at_' ) ) {
						// Strip the priority from the hook name.
						$priority = (int) Str::after_last( $hook, '_at_' );
						$hook     = Str::before_last( $hook, '_at_' );
					}

					return [
						'type'     => $type,
						'hook'     => $hook,
						'method'   => $method,
						'priority' => $priority,
					];
				}
			)
			->filter()
			->values();
	}

	/**
	 * Collect all attribute actions on the service provider.
	 *
	 * Allow methods with the `#[Action]` attribute to automatically register
	 * WordPress hooks.
	 *
	 * @phpstan-return Collection<int, HookItem>
	 */
	private function collect_attribute_hooks(): Collection {
		$items = new Collection();

		foreach ( $this->reflection->getMethods() as $method ) {
			foreach ( $method->getAttributes( Action::class ) as $attribute ) {
				$instance = $attribute->newInstance();

				$items->push(
					[
						'type'     => 'action',
						'hook'     => $instance->hook_name,
						'method'   => $method->getName(),
						'priority' => $instance->priority,
					]
				);
			}

			foreach ( $method->getAttributes( Filter::class ) as $attribute ) {
				$instance = $attribute->newInstance();

				$items->push(
					[
						'type'     => 'filter',
						'hook'     => $instance->hook_name,
						'method'   => $method->getName(),
						'priority' => $instance->priority,
					]
				);
			}
		}

		return $items;
	}

	/**
	 * Determine if the service provider should use the event dispatcher or the
	 * core WordPress hooks.
	 *
	 * By default, it is only enabled if the class is an instance of the
	 * `Service_Provider` class. For external uses of this trait, the event
	 * dispatcher won't be used.
	 */
	public function use_event_dispatcher(): bool {
		return class_exists( Service_Provider::class ) && $this instanceof Service_Provider;
	}
}
