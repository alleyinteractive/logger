<?php
/**
 * Environment helpers.
 *
 * @package Mantle
 */

namespace Mantle\Support\Helpers;

/**
 * Check if we are on a hosted environment
 */
function is_hosted_env(): bool {
	return app()->is_environment( 'production' );
}

/**
 * Check if the current environment is a local developer environment.
 */
function is_local_env(): bool {
	return app()->is_environment( 'local' );
}

/**
 * Determine if the current request is from WP-CLI.
 */
function is_wp_cli(): bool {
	return defined( 'WP_CLI' ) && WP_CLI;
}

/**
 * Determine if we are unit testing with Mantle.
 */
function is_unit_testing(): bool {
	return defined( 'MANTLE_IS_TESTING' ) && MANTLE_IS_TESTING;
}
