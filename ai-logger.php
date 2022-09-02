<?php
/**
 * Plugin Name: AI Logger
 * Plugin URI: https://github.com/alleyinteractive/logger
 * Description: A Monolog-based logging tool for WordPress. Supports storing log message in a custom post type or in individual posts and terms.
 * Version: 2.2.0
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

use Monolog\Logger;

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

/**
 * Create a Query Monitor Logger instance.
 *
 * @param string $level The log level to use.
 * @return \AI_Logger\AI_Logger
 */
function ai_logger_to_qm( string $level = Logger::DEBUG ): \AI_Logger\AI_Logger {
	return ai_logger()->with_handlers(
		[
			new \AI_Logger\Handler\Query_Monitor_Handler( $level ),
		]
	);
}

/**
 * Create a post logger instance.
 *
 * @param int    $post_id Post ID.
 * @param string $meta_key Meta key to log to.
 * @param string $level The log level to use.
 * @return \AI_Logger\AI_Logger
 */
function ai_logger_to_post( int $post_id, string $meta_key = 'log', string $level = Logger::DEBUG ): \AI_Logger\AI_Logger {
	return ai_logger()->to_post( $meta_key, $post_id, $level );
}

/**
 * Create a term logger instance.
 *
 * @param int    $term_id Term ID.
 * @param string $meta_key Meta key to log to.
 * @param string $level The log level to use.
 * @return \AI_Logger\AI_Logger
 */
function ai_logger_to_term( int $term_id, string $meta_key = 'log', string $level = Logger::DEBUG ): \AI_Logger\AI_Logger {
	return ai_logger()->to_term( $meta_key, $term_id, $level );
}
