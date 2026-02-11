<?php
/**
 * Nullable Core Objects functions
 *
 * @package Mantle
 */

namespace Mantle\Support\Helpers;

/**
 * Nullable wrapper for `get_post()`.
 *
 * @param int|\WP_Post|null $post   Post ID or post object.
 * @param string            $output Provided for compatibility with the
 *                                  `get_post()` signature.
 * @param string            $filter Type of filter to apply.
 *
 * @phpstan-param 'OBJECT'|'ARRAY_A'|'ARRAY_N' $output
 * @phpstan-param 'raw'|'edit'|'db'|'display' $filter
 * @phpstan-return ($output is \OBJECT ? null|\WP_Post : ($output is \ARRAY_A ? null|array<string, mixed> : ($output is \ARRAY_N ? null|array<int, mixed> : null)))
 */
function get_post_object( mixed $post = null, string $output = \OBJECT, string $filter = 'raw' ): mixed {
	$post = \get_post( $post, $output, $filter );

	return false === $post || $post instanceof \WP_Error ? null : $post; // @phpstan-ignore-line
}

/**
 * Nullable wrapper for `get_term()`.
 *
 * @param int|\WP_Term|object $term     Term ID, database object, or term
 *                                      object.
 * @param string              $taxonomy Taxonomy name that $term is part of.
 * @param string              $output   Provided for compatibility with the
 *                                      `get_term()` signature.
 * @param string              $filter   Type of filter to apply.
 *
 * @phpstan-param 'OBJECT'|'ARRAY_A'|'ARRAY_N' $output
 * @phpstan-param 'raw'|'edit'|'db'|'display' $filter
 * @phpstan-return ($output is \OBJECT ? null|\WP_Term : ($output is \ARRAY_A ? null|array<string, mixed> : ($output is \ARRAY_N ? null|array<int, mixed> : null)))
 */
function get_term_object( mixed $term, string $taxonomy = '', string $output = \OBJECT, string $filter = 'raw' ): mixed {
	$term = \get_term( $term, $taxonomy, $output, $filter );

	// @phpstan-ignore identical.alwaysFalse
	return $term instanceof \WP_Error || false === $term ? null : $term;
}

/**
 * Nullable wrapper for `get_term_by()`.
 *
 * @param string     $field    Either 'slug', 'name', 'id', or
 *                             'term_taxonomy_id'.
 * @param string|int $value    Search for this term value.
 * @param string     $taxonomy Taxonomy name. Optional, if $field is
 *                             'term_taxonomy_id'.
 * @param string     $output   Provided for compatibility with the
 *                             `get_term_by()` signature.
 * @param string     $filter   Type of filter to apply.
 *
 * @phpstan-param 'OBJECT'|'ARRAY_A'|'ARRAY_N' $output
 * @phpstan-param 'raw'|'edit'|'db'|'display' $filter
 * @phpstan-return ($output is \OBJECT ? null|\WP_Term : ($output is \ARRAY_A ? null|array<string, mixed> : ($output is \ARRAY_N ? null|array<int, mixed> : null)))
 */
function get_term_object_by( string $field, mixed $value, string $taxonomy = '', string $output = \OBJECT, string $filter = 'raw' ): mixed {
	$term = \get_term_by( $field, $value, $taxonomy, $output, $filter );

	// @phpstan-ignore instanceof.alwaysFalse
	return $term instanceof \WP_Error || false === $term ? null : $term;
}

/**
 * Nullable wrapper for `get_comment()`.
 *
 * @param \WP_Comment|string|int $comment Comment to retrieve.
 */
function get_comment_object( mixed $comment ): ?\WP_Comment {
	$object = \get_comment( $comment );

	return ( $object instanceof \WP_Comment ) ? $object : null;
}

/**
 * Nullable wrapper for `get_userdata()`.
 *
 * @param \WP_User|int $user User ID/object.
 */
function get_user_object( mixed $user ): ?\WP_User {
	if ( $user instanceof \WP_User ) {
		return $user;
	}

	$object = \get_userdata( (int) $user );

	return ( $object instanceof \WP_User ) ? $object : null;
}

/**
 * Nullable wrapper for `get_user_by()`.
 *
 * @param string     $field Either 'id', 'ID', 'slug', 'email', or 'login'.
 * @param int|string $value Search for this user value.
 */
function get_user_object_by( string $field, mixed $value ): ?\WP_User {
	$object = \get_user_by( $field, $value );

	return $object instanceof \WP_User ? $object : null;
}

/**
 * Nullable wrapper for `get_site()`.
 *
 * @param \WP_Site|int|null $site Site to retrieve.
 */
function get_site_object( mixed $site = null ): ?\WP_Site {
	$object = \get_site( $site );

	return $object instanceof \WP_Site ? $object : null;
}
