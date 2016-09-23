<?php
/**
 * Class to implement JavaScript logging functionality.
 * Disabled by default. to enable, filter ai_logger_enable_js_logging.
 * e.g. Place `add_filter( 'ai_logger_enable_js_logging', '__return_true' );` in your theme.
 */
class AI_Logger_JS {

	/**
	 * Initialize JavaScript Logging functionality.
	 */
	public function __construct() {
		// Create AJAX actions.
		add_action( 'wp_ajax_ai_logger_insert', array( $this, 'log' ) );
		add_action( 'wp_ajax_nopriv_ai_logger_insert', array( $this, 'log' ) );

		// Set up assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue and localize assets.
	 */
	public function enqueue() {
		wp_enqueue_script( 'ai-logger-insert', AI_LOGGER_URL . '/static/js/logger-insert.js', array( 'jquery' ), '0.1', true );
		wp_localize_script( 'ai-logger-insert', 'aiLogger', array(
			'url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'ai-logger-insert' ),
		) );
	}

	/**
	 * Create a log message based on posted data.
	 */
	public function log() {
		if ( ! check_ajax_referer( 'ai-logger-insert', 'ai_logger_nonce', false ) ) {
			wp_send_json_error( array( 'response' => __( 'Insecure request.', 'ai-logger' ) ) );
		} elseif ( empty( $_POST['key'] ) || empty( $_POST['message'] ) ) {
			wp_send_json_error( array( 'response' => __( 'Key and message parameters are required.', 'ai-logger' ) ) );
		}

		// Sanitize input.
		$key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
		$message = sanitize_text_field( wp_unslash( $_POST['message'] ) );
		$clean_args = array();

		// Clean up the arguments array.
		if ( isset( $_POST['args'] ) && is_array( $_POST['args'] ) ) {
			$args = wp_unslash( $_POST['args'] );
			foreach ( $args as $arg => $value ) {
				$clean_args[ sanitize_text_field( $arg ) ] = sanitize_text_field( $value );
			}
		}

		// Create log message.
		do_action( 'ai_logger_insert', $key, $message, $clean_args );

		// Respond.
		wp_send_json_success( array(
			'response' => __( 'Log entry created.', 'ai-logger' ),
			'key' => $key,
			'message' => $message,
			'args' => $clean_args,
		) );
	}
}

add_action( 'after_setup_theme', function() {
	if ( apply_filters( 'ai_logger_enable_js_logging', false ) ) {
		new AI_Logger_JS();
	}
}, 20 );
