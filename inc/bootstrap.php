<?php
/**
 * Logger Bootstrapper
 *
 * @package AI_Logger
 */

namespace AI_Logger;

// Instantiate the logger.
AI_Logger::instance();
Settings::instance();

add_action(
	'after_setup_theme',
	function () {
		/**
		 * Flag if Javascript logging is enabled.
		 *
		 * @param bool $enabled Flag if Javascript logging is enabled.
		 */
		if ( apply_filters( 'ai_logger_enable_js_logging', false ) ) {
			new AI_Logger_JS();
		}
	},
	20
);

// wp-cli command.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'ai-logger', __NAMESPACE__ . '\CLI' );
}
