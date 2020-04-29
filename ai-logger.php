<?php
/**
 * Plugin Name: AI Logger
 * Plugin URI: https://github.com/alleyinteractive/logger
 * Description: A logger tool that stores errors and messages as a custom post type
 * Version: 1.0.0
 * Author: Alley Interactive, Jared Cobb
 * Author URI: https://alley.co/
 * Requires at least: 4.6.0
 * Tested up to: 4.6.1
 *
 * Text Domain: ai-logger
 * Domain Path: /languages/
 *
 * @package AI_Logger
 * @category Core
 * @author jaredcobb
 */

namespace AI_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
