<?php
/**
 * Service_Provider class file.
 *
 * @package Mantle
 */

namespace Mantle\Support;

use Mantle\Console\Application as Console_Application;
use Mantle\Console\Command;
use Mantle\Container\Container;
use Mantle\Contracts\Application;
use Mantle\Support\Traits\Hookable;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};

use function Mantle\Support\Helpers\collect;

/**
 * Application Service Provider
 */
abstract class Service_Provider implements LoggerAwareInterface {
	use Hookable;
	use LoggerAwareTrait;

	/**
	 * The paths that should be published.
	 *
	 * @var array<class-string<Service_Provider>, string[]>
	 */
	public static array $publishes = [];

	/**
	 * The paths that should be published by group.
	 *
	 * @var array<string, string[]>
	 */
	public static array $publish_tags = [];

	/**
	 * The application instance.
	 */
	protected Application $app;

	/**
	 * Commands to register.
	 * Register commands through `Service_Provider::add_command()`.
	 *
	 * @var \Mantle\Console\Command[]
	 */
	protected array $commands;

	/**
	 * Create a new service provider instance.
	 *
	 * @param Application $app Application Instance.
	 */
	public function __construct( Application $app ) {
		$this->app = $app;
	}

	/**
	 * Register the service provider.
	 *
	 * @phpstan-ignore missingType.return
	 */
	public function register() {}

	/**
	 * Boot the service provider.
	 *
	 * @phpstan-ignore missingType.return
	 */
	public function boot() {}

	/**
	 * Bootstrap services.
	 */
	public function boot_provider(): void {
		if ( isset( $this->app['log'] ) ) {
			$this->setLogger( $this->app['log']->driver() );
		}

		$this->register_hooks();
		$this->boot();
	}

	/**
	 * Register a console command.
	 *
	 * @param Command[]|string[]|Command|string $command Command instance or class name to register.
	 */
	public function add_command( array|string|Command|\Symfony\Component\Console\Command\Command $command ): Service_Provider {
		Console_Application::starting(
			fn ( Console_Application $console ) => $console->resolve_commands( $command )
		);

		return $this;
	}

	/**
	 * Setup an after resolving listener, or fire immediately if already resolved.
	 *
	 * @param  string   $name Abstract name.
	 * @param  callable $callback Callback.
	 */
	protected function call_after_resolving( string $name, callable $callback ): void {
		$this->app->after_resolving( $name, $callback );

		if ( $this->app->resolved( $name ) ) {
			$callback( $this->app->make( $name ), $this->app );
		}
	}

	/**
	 * Register paths to be published by the publish command.
	 *
	 * @param string[]                  $paths Paths to publish.
	 * @param string|array<string>|null $tags Tags to publish.
	 */
	public function publishes( array $paths, $tags = null ): void {
		$class = static::class;

		if ( ! array_key_exists( $class, static::$publishes ) ) {
			static::$publishes[ $class ] = [];
		}

		static::$publishes[ $class ] = array_merge( static::$publishes[ $class ], $paths );

		foreach ( (array) $tags as $tag ) {
			if ( ! array_key_exists( $tag, static::$publish_tags ) ) {
				static::$publish_tags[ $tag ] = [];
			}

			static::$publish_tags[ $tag ] = array_merge(
				static::$publish_tags[ $tag ],
				$paths,
			);
		}
	}

	/**
	 * Get the service providers available for publishing.
	 *
	 * @return array<class-string<Service_Provider>>
	 */
	public static function publishable_providers(): array {
		return array_keys( static::$publishes );
	}

	/**
	 * Get the groups available for publishing.
	 *
	 * @return string[]
	 */
	public static function publishable_tags(): array {
		return array_keys( static::$publish_tags );
	}

	/**
	 * Load routes from the given path.
	 *
	 * @param string $path Path to routes file.
	 */
	public function load_routes_from( string $path ): void {
		require $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}

	/**
	 * Load views from the given path.
	 *
	 * @param string $path Path to views directory.
	 * @param string $alias Alias to register views under.
	 */
	public function load_views_from( string $path, string $alias ): void {
		$this->call_after_resolving(
			'view.loader',
			fn ( \Mantle\Http\View\View_Finder $finder ) => $finder->add_path( $path, $alias ),
		);
	}

	/**
	 * Get the paths to publish.
	 *
	 * Passing both a provider and a tag will return all paths that are
	 * published by that provider and tag.
	 *
	 * @param  class-string<Service_Provider>|array<class-string<Service_Provider>>|null $providers The service provider class name.
	 * @param  string|array<string>|null                                                 $tags      The tag name.
	 * @return array<string, string> The paths to publish. Index is the source path, value is the destination path.
	 */
	public static function paths_to_publish( array|string|null $providers = null, array|string|null $tags = null ): array {
		if ( ! $providers && ! $tags ) {
			return [];
		}

		$provider_paths = collect();
		$tag_paths      = collect();

		if ( $providers ) {
			foreach ( (array) $providers as $item ) {
				$provider_paths = $provider_paths->merge( static::$publishes[ $item ] ?? [] );
			}
		}

		if ( $tags ) {
			foreach ( (array) $tags as $item ) {
				$tag_paths = $tag_paths->merge( static::$publish_tags[ $item ] ?? [] );
			}
		}

		return match ( true ) {
			! empty( $providers ) && ! empty( $tags ) => $provider_paths->intersect_by_keys( $tag_paths )->all(),
			! empty( $providers ) => $provider_paths->all(),
			! empty( $tags ) => $tag_paths->all(),
			default => [],
		};
	}
}
