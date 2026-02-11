<?php
/**
 * Helpers for interacting with meta data.
 *
 * @package Mantle
 */

namespace Mantle\Support\Helpers;

use InvalidArgumentException;

/**
 * Register meta for posts or terms with sensible defaults and sanitization.
 *
 * @throws \InvalidArgumentException For unmet requirements.
 *
 * @see \register_post_meta
 * @see \register_term_meta
 *
 * @param 'post'|'term'        $object_type  The type of meta to register, which must be one of 'post' or 'term'.
 * @param string|string[]      $object_slugs The post type or taxonomy slugs to register with.
 * @param string               $meta_key     The meta key to register.
 * @param array<string, mixed> $args         Optional. Additional arguments for register_post_meta or register_term_meta. Defaults to an empty array.
 * @return bool True if the meta key was successfully registered in the global array, false if not.
 */
function register_meta_helper(
	string $object_type,
	string|array $object_slugs,
	string $meta_key,
	array $args = []
): bool {
	if ( ! in_array( $object_type, [ 'post', 'term' ], true ) ) {
		throw new \InvalidArgumentException(
			esc_html__(
				'Object type must be one of "post", "term".',
				'mantle'
			)
		);
	}

	/**
	 * Merge provided arguments with defaults and filter register_meta() args.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_meta/
	 *
	 * @param array           $args {
	 *     Array of args to be passed to register_meta().
	 *
	 *     @type string     $object_subtype    A subtype; e.g. if the object type is "post", the post type. If left empty,
	 *                                         the meta key will be registered on the entire object type. Default empty.
	 *     @type string     $type              The type of data associated with this meta key. Valid values are
	 *                                         'string', 'boolean', 'integer', 'number', 'array', and 'object'.
	 *     @type string     $description       A description of the data attached to this meta key.
	 *     @type bool       $single            Whether the meta key has one value per object, or an array of values per object.
	 *     @type mixed      $default           The default value returned from get_metadata() if no value has been set yet.
	 *                                         When using a non-single meta key, the default value is for the first entry. In other words,
	 *                                         when calling get_metadata() with $single set to false, the default value given here will be wrapped in an array.
	 *     @type callable   $sanitize_callback A function or method to call when sanitizing $meta_key data.
	 *     @type callable   $auth_callback     Optional. A function or method to call when performing edit_post_meta,
	 *                                         add_post_meta, and delete_post_meta capability checks.
	 *     @type bool|array $show_in_rest      Whether data associated with this meta key can be considered public and should be
	 *                                         accessible via the REST API. A custom post type must also declare support
	 *                                         for custom fields for registered meta to be accessible via REST. When registering
	 *                                         complex meta values this argument may optionally be an array with 'schema'
	 *                                         or 'prepare_callback' keys instead of a boolean.
	 * }
	 * @param string          $object_type  The type of meta to register, which must be one of 'post' or 'term'.
	 * @param string|string[] $object_slugs The post type or taxonomy slugs to register with.
	 * @param string          $meta_key     The meta key to register.
	 */
	$args = apply_filters(
		'mantle_register_meta_helper_args', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		wp_parse_args(
			$args,
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		),
		$object_type,
		$object_slugs,
		$meta_key
	);

	// Allow setting meta for all of an object type.
	if (
		(
			is_array( $object_slugs ) &&
			1 === count( $object_slugs ) &&
			'all' === $object_slugs[0]
		) ||
		(
			is_string( $object_slugs ) &&
			'all' === $object_slugs
		)
	) {
		return register_meta( $object_type, $meta_key, $args );
	}

	// Fix potential errors since we're allowing `$object_slugs` to be a string or array.
	if ( is_string( $object_slugs ) ) {
		$object_slugs = [ $object_slugs ];
	}

	switch ( $object_type ) {
		case 'post':
			foreach ( $object_slugs as $object_slug ) {
				if ( ! register_post_meta( $object_slug, $meta_key, $args ) ) {
					return false;
				}
			}

			break;
		case 'term':
			foreach ( $object_slugs as $object_slug ) {
				if ( ! register_term_meta( $object_slug, $meta_key, $args ) ) {
					return false;
				}
			}

			break;
		default:
			return false;
	}

	return true;
}

/**
 * Reads the meta definitions a configuration file.
 *
 * @throws \InvalidArgumentException For unmet requirements.
 *
 * @param string        $file The name of the full file path to read definitions from.
 * @param 'post'|'term' $meta_context The type of meta to register, which must be one of 'post' or 'term'.
 * @param bool          $throw Optional. Whether to throw an exception if the file cannot be found. Default true.
 */
function register_meta_from_file( string $file, string $meta_context, bool $throw = true ): void {
	if ( ! in_array( $meta_context, [ 'post', 'term' ], true ) ) {
		throw new InvalidArgumentException( 'Meta context must be one of "post", "term".' );
	}

	if ( ! file_exists( $file ) || ! in_array( validate_file( $file ), [ 0, 2 ], true ) ) {
		throw_if( $throw, new InvalidArgumentException(
			"Meta definition file [{$file}] does not exist."
		) );

		return;
	}

	$definitions = wp_json_file_decode( $file, [ 'associative' => true ] );

	if ( ! is_array( $definitions ) ) {
		throw new InvalidArgumentException(
			"Meta definition file [{$file}] does not contain valid JSON."
		);
	}

	foreach ( $definitions as $meta_key => $definition ) {
		if ( '$schema' === $meta_key ) {
			continue;
		}

		if ( ! is_array( $definition ) ) {
			_doing_it_wrong( __FUNCTION__, 'Meta definition items must be an array.', '1.0.0' );

			continue;
		}

		// Extract post types or terms.
		$definition_key = 'post' === $meta_context ? 'post_types' : 'terms';
		$object_types   = $definition[ $definition_key ] ?? [];

		// Unset since $definition is passed as register_meta args.
		unset( $definition[ $definition_key ] );

		// Relocate schema, if specified at the top level.
		if ( ! empty( $definition['schema'] ) ) {
			if ( ! isset( $definition['show_in_rest'] ) || ! is_array( $definition['show_in_rest'] ) ) {
				$definition['show_in_rest'] = [];
			}

			$definition['show_in_rest']['schema'] = $definition['schema'];

			// Unset since $definition is passed as register_meta args.
			unset( $definition['schema'] );
		}

		register_meta_helper(
			$meta_context,
			$object_types,
			$meta_key,
			$definition,
		);
	}
}
