<?php
/**
 * Autoloader.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

/**
 * Generate an autoloader for the WordPress file naming conventions.
 *
 * @param string $namespace Namespace to autoload.
 * @param string $root_path Path in which to look for files.
 * @return \Closure Function for spl_autoload_register().
 */
function generate_autoloader( string $namespace, string $root_path ): callable {
	// Ensure consistent root.
	$root_path = \rtrim( $root_path, \DIRECTORY_SEPARATOR ) . \DIRECTORY_SEPARATOR;

	return function ( $classname ) use ( $namespace, $root_path ) {
		// Ignore if the base namespace doesn't match.
		if ( 0 !== \strpos( $classname, $namespace ) ) {
			return;
		}

		// Remove the namespace.
		$classname = \preg_replace( '#^' . \preg_quote( $namespace, '#' ) . '#', '', $classname );
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
		];

		/*
		 * Attempt to find the file by looping through the various paths.
		 *
		 * Autoloading a class will also cause a trait or interface with the
		 * same fully qualified name to be autoloaded, as it's impossible to
		 * tell which was requested.
		 */
		foreach ( $paths as $path ) {
			$path = $root_path . \sprintf( $path, $base_path, $class );

			if ( \file_exists( $path ) && 0 === validate_file( $path ) ) {
				// Path is defined by this file and validated.
				require_once $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
				return;
			}
		}
	};
}
