<?php
/**
 * Autoloader file
 *
 * @package WordPress_Autoloader
 */

namespace Alley_Interactive\Autoloader;

/**
 * WordPress Autoload Generator
 */
class Autoloader {
	/**
	 * Namespace to autoload.
	 *
	 * @var string
	 */
	protected string $namespace;

	/**
	 * Root path of the namespace to load from.
	 *
	 * @var string
	 */
	protected string $root_path;

	/**
	 * Constructor.
	 *
	 * @param string $namespace Namespace to register.
	 * @param string $root_path Root path of the namespace.
	 */
	public function __construct( string $namespace, string $root_path ) {
		$this->namespace = $namespace;

		// Ensure consistent root.
		$this->root_path = rtrim( $root_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Register the autoloader.
	 */
	public function register() {
		spl_autoload_register( $this );
	}

	/**
	 * Unregister the autoloader.
	 */
	public function unregister() {
		spl_autoload_unregister( $this );
	}

	/**
	 * Invoke method of the class.
	 *
	 * @param string $classname Class being autoloaded.
	 */
	public function __invoke( string $classname ) {
		// Ignore if the base namespace doesn't match.
		if ( 0 !== \strpos( $classname, $this->namespace ) ) {
			return;
		}

		// Remove the namespace.
		$classname = \preg_replace( '#^' . \preg_quote( $this->namespace, '#' ) . '#', '', $classname );
		$classname = \ltrim( $classname, '\\' );

		// Convert to segments.
		$classname = \strtolower( $classname );
		$classname = \str_replace( [ '\\', '_' ], [ '/', '-' ], $classname );
		$classes   = \explode( '/', $classname );

		// Retrieve the class name (last item).
		$class = \array_pop( $classes );

		// Build the base path.
		$base_path = \implode( '/', $classes );

		// Support multiple locations since the class could be a class, trait or interface.
		$paths = [
			'%1$s/class-%2$s.php',
			'%1$s/trait-%2$s.php',
			'%1$s/interface-%2$s.php',
			'%1$s/enum-%2$s.php',
		];

		/*
		* Attempt to find the file by looping through the various paths.
		*
		* Autoloading a class will also cause a trait or interface with the
		* same fully qualified name to be autoloaded, as it's impossible to
		* tell which was requested.
		*/
		foreach ( $paths as $path ) {
			$path = $this->root_path . \sprintf( $path, $base_path, $class );

			if ( \file_exists( $path ) ) {
				// Path is defined by this file and validated.
				require_once $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
				return;
			}
		}
	}

	/**
	 * Generate an autoloader for the WordPress file naming conventions.
	 *
	 * @param string $namespace Namespace to autoload.
	 * @param string $root_path Path in which to look for files.
	 * @return static Function for spl_autoload_register().
	 */
	public static function generate( string $namespace, string $root_path ): callable {
		return new static( $namespace, $root_path );
	}
}
