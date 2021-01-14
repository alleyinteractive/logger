<?php
/**
 * AI_Logger_JS class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

/**
 * Class to implement JavaScript logging functionality.
 * Disabled by default, enable by passing true to the
 * 'ai_logger_enable_js_logging' filter.
 */
class AI_Logger_JS {

	/**
	 * Initialize JavaScript Logging functionality.
	 */
	public function __construct() {
		// Create AJAX actions.
		add_action( 'wp_ajax_ai_logger_insert', [ $this, 'log' ] );
		add_action( 'wp_ajax_nopriv_ai_logger_insert', [ $this, 'log' ] );

		// Set up assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Enqueue and localize assets.
	 */
	public function enqueue() {
		wp_enqueue_script( 'ai-logger-insert', AI_LOGGER_URL . '/static/js/logger-insert.js', [], '0.1', true );
		wp_localize_script(
			'ai-logger-insert',
			'aiLoggerConfig',
			[
				'nonce' => wp_create_nonce( 'ai-logger-insert' ),
				'url'   => admin_url( 'admin-ajax.php' ),
			]
		);
	}

	/**
	 * Create a log message based on posted data.
	 */
	public function log() {
		if ( ! check_ajax_referer( 'ai-logger-insert', 'ai_logger_nonce', false ) ) {
			wp_send_json_error( [ 'response' => __( 'Insecure request.', 'ai-logger' ) ] );
		} elseif ( empty( $_POST['message'] ) ) {
			wp_send_json_error( [ 'response' => __( 'Key and message parameters are required.', 'ai-logger' ) ] );
		}

		// Sanitize input.
		$message    = sanitize_text_field( wp_unslash( $_POST['message'] ) );
		$clean_args = [];

		// Clean up the arguments array.
		if ( isset( $_POST['args'] ) ) {
			$args = wp_unslash( $_POST['args'] );
			if ( is_string( $args ) ) {
				$args = json_decode( $args, true );
			}

			foreach ( $args as $arg => $value ) {
				$clean_args[ sanitize_text_field( $arg ) ] = sanitize_text_field( $value );
			}
		}

		// Setup some default arguments.
		$level           = $args['level'] ?? 'info';
		$args['context'] = $args['context'] ?? 'front-end';

		$logger = ai_logger();

		// Create the log entry.
		if ( ! method_exists( $logger, $level ) ) {
			wp_send_json_error(
				[
					'error' => __( 'Invalid log level.', 'ai-logger' ),
				],
				400
			);
		}

		$logger->$level( $message, $args );

		// Respond.
		wp_send_json_success(
			[
				'args'     => $clean_args,
				'message'  => $message,
				'response' => __( 'Log entry created.', 'ai-logger' ),
			]
		);
	}
}
