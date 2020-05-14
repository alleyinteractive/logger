<?php
/**
 * Plugin Name: AI Logger
 * Plugin URI: https://github.com/alleyinteractive/logger
 * Description: A logger tool that stores errors and messages as a custom post type
 * Version: 2.0.0
 * Author: Alley Interactive, Jared Cobb
 * Author URI: https://alley.co/
 * Requires at least: 5.4
 * Tested up to: 5.4
 *
 * Text Domain: ai-logger
 * Domain Path: /languages/
 *
 * @package AI_Logger
 * @author jaredcobb
 */

namespace AI_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AI_LOGGER_PATH', __DIR__ );

// Check if Composer is installed.
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	\add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'AI Logger: Composer is not installed and the plugin cannot load.', 'ai-logger' ); ?></p>
			</div>
			<?php
		}
	);

	return;
}

// Include core dependencies.
require_once __DIR__ . '/vendor/autoload.php';

// If the Composer autoloader doesn't find the main file, fallback to plugin's.
if ( ! class_exists( 'AI_Logger\AI_Logger' ) ) {
	require_once __DIR__ . '/inc/autoload.php';

	try {
		\spl_autoload_register( generate_autoloader( __NAMESPACE__, __DIR__ . '/inc/' ) );
	} catch ( \Exception $exception ) {
		wp_die( esc_html__( 'Error generating autoloader.', 'ai-logger' ) );
	}
}

AI_Logger::instance();

// wp-cli command.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'ai-logger', __NAMESPACE__ . '\CLI' );
}
