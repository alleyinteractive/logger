<?php
/**
 * Plugin Name: Alley Logger
 * Plugin URI: https://github.com/alleyinteractive/logger
 * Description: A Monolog-based logging tool for WordPress. Supports storing log message in a custom post type or in individual posts and terms.
 * Version: 2.3.0
 * Author: Alley Interactive
 * Author URI: https://alley.com/
 * Requires at least: 5.9
 * Tested up to: 5.9
 *
 * Text Domain: ai-logger
 * Domain Path: /languages/
 *
 * @package AI_Logger
 */

use Monolog\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AI_LOGGER_PATH', __DIR__ );
define( 'AI_LOGGER_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );

// Check if Composer is installed.
if ( ! file_exists( __DIR__ . '/vendor/wordpress-autoload.php' ) ) {
	// Don't bail if Monolog is already loaded (logger could be installed as a
	// Composer dependency).
	if ( ! class_exists( Logger::class ) ) {
		\add_action(
			'admin_notices',
			function() {
				?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'AI Logger: Composer is not installed and the plugin cannot load. Try using the `develop-built` branch or a `*-built` tag.', 'ai-logger' ); ?></p>
				</div>
				<?php
			}
		);

		return;
	}
} else {
	// Include Composer dependencies.
	require_once __DIR__ . '/vendor/wordpress-autoload.php';
}

require_once __DIR__ . '/inc/bootstrap.php';

/**
 * Retrieve the core logger instance.
 *
 * @param array|string|null $context Default context to apply to the logger, optional.
 * @return \AI_Logger\AI_Logger
 */
function ai_logger( array|string|null $context = null ): \AI_Logger\AI_Logger {
	if ( $context ) {
		return \AI_Logger\AI_Logger::instance()->with_context( $context );
	}

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

/**
 * Create a new post meta box to display logs stored in post meta.
 *
 * @param string $meta_key Meta key for the logs.
 * @param string $title    Title for the meta box.
 * @return \AI_Logger\Meta_Box\Post_Meta_Box
 */
function ai_logger_post_meta_box( string $meta_key, string $title ): \AI_Logger\Meta_Box\Post_Meta_Box {
	return new \AI_Logger\Meta_Box\Post_Meta_Box( $meta_key, $title );
}

/**
 * Create a new term meta box to display logs stored in term meta.
 *
 * @param string $meta_key   Meta key for the logs.
 * @param string $title      Title for the meta box.
 * @param array  $taxonomies Taxonomies to display the meta box on.
 * @return \AI_Logger\Meta_Box\Term_Meta_Box
 */
function ai_logger_term_meta_box( string $meta_key, string $title, array $taxonomies ): \AI_Logger\Meta_Box\Term_Meta_Box {
	return new \AI_Logger\Meta_Box\Term_Meta_Box( $meta_key, $title, $taxonomies );
}
