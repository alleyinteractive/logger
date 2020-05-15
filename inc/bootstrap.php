<?php
/**
 * Logger Bootstrapper
 *
 * @package AI_Logger
 */

namespace AI_Logger;

require_once __DIR__ . '/autoload.php';

try {
	\spl_autoload_register( generate_autoloader( __NAMESPACE__, __DIR__ ) );
} catch ( \Exception $exception ) {
	wp_die( esc_html__( 'Error generating autoloader.', 'ai-logger' ) );
}

AI_Logger::instance();

// wp-cli command.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'ai-logger', __NAMESPACE__ . '\CLI' );
}
