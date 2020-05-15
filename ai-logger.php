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

// Include core dependencies (which loads inc/bootstrap.php).
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Retrieve the core logger instance.
 *
 * @return Monolog\Logger
 */
function ai_logger(): Monolog\Logger {
	return \AI_Logger\AI_Logger::instance()->get_logger();
}
