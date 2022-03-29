<?php
/**
 * Plugin Name: AI Logger
 * Plugin URI: https://github.com/alleyinteractive/logger
 * Description: A Monolog-based logging tool for WordPress. Supports storing log message in a custom post type or in individual posts and terms.
 * Version: 2.1.3
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
define( 'AI_LOGGER_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );

// Check if Composer is installed.
if ( ! file_exists( __DIR__ . '/vendor/wordpress-autoload.php' ) ) {
	\add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'AI Logger: Composer is not installed and the plugin cannot load. Try using the `main-built` branch!', 'ai-logger' ); ?></p>
			</div>
			<?php
		}
	);

	return;
}

// Include core dependencies.
require_once __DIR__ . '/vendor/wordpress-autoload.php';
require_once __DIR__ . '/inc/bootstrap.php';

/**
 * Retrieve the core logger instance.
 *
 * @return \AI_Logger\AI_Logger
 */
function ai_logger(): \AI_Logger\AI_Logger {
	return \AI_Logger\AI_Logger::instance();
}
