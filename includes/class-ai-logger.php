<?php
/**
 * Main class responsible for defining the logger functionality
 */
class AI_Logger {

	/**
	 * instance
	 *
	 * @var AI_Logger
	 * @access protected
	 * @static
	 */
	protected static $instance;

	/**
	 * A predefined list of log levels that are permitted.
	 * These are stored as terms in the Level taxonomy.
	 *
	 * @var array
	 * @access protected
	 */
	protected $allowed_levels = array();

	/**
	 * The time limit that this logger should wait before
	 * attempting to insert another UNIQUE log entry in seconds
	 *
	 * @var int
	 * @access protected
	 */
	protected $throttle_limit;

	/**
	 * Get the instance of this singleton
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function instance() {

		if ( ! isset( static::$instance ) ) {
			static::$instance = new AI_Logger();
			static::$instance->setup();
		}
		return static::$instance;

	}

	/**
	 * Register various actions & filters, initialize the object
	 *
	 * @access public
	 * @return void
	 */
	public function setup() {

		$this->allowed_levels = array(
			'info' => __( 'Info', 'ai-logger' ),
			'warning' => __( 'Warning', 'ai-logger' ),
			'error' => __( 'Error', 'ai-logger' ),
		);

		$this->throttle_limit = apply_filters( 'ai_logger_throttle_limit', MINUTE_IN_SECONDS * 15 );

	}

	public function insert( $key, $message, $args = array() ) {

		$defaults = array(
			'level' => 'error',
			'context' => '',
			'include_stack_trace' => true,
		);

		// parse incoming $args and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );

		// validate the level term is actually an allowed term for this taxonomy
		// otherwise just force the log level to be 'error'
		if ( ! array_key_exists( $args['level'], $this->allowed_levels ) ) {
			$args['level'] = 'error';
		}

		if ( true === $args['include_stack_trace'] ) {
			$e = new \Exception;
			$message .= "\r\n\r\n" . esc_html( $e->getTraceAsString() );
		}

		// determine if this insert should actually write to the DB
		if ( $this->insert_permitted( $key, $args ) ) {
			$post_args = array(
				'post_title' => $key,
				'post_status' => 'publish',
				'post_type' => 'ai_log',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_content' => $message,
			);
			$new_post_id = wp_insert_post( $post_args );

			if ( $new_post_id ) {
				$this->assign_terms( $new_post_id, $this->allowed_levels[ $args['level'] ], 'ai_log_level' );
				if ( ! empty( $args['context'] ) ) {
					$this->assign_terms( $new_post_id, $args['context'], 'ai_log_context' );
				}
			}

			// create a unique transient key based on the log key and context
			$context = ! empty( $args['context'] ) ? $args['context'] : '';
			$transient_key = 'ai_log_' . md5( $key . $context );
			set_transient( $transient_key, true, $this->throttle_limit );
		}
	}

	/**
	 * Assign the terms associated with the new post, currently
	 * used to apply a Log Level (info, warning, error) and the
	 * custom context to a log
	 *
	 * @param int $new_post_id
	 * @param string $term
	 * @param string $taxonomy
	 * @access protected
	 * @return void
	 */
	protected function assign_terms( $new_post_id, $term, $taxonomy ) {

		$term_id = false;

		if ( function_exists( 'wpcom_vip_get_term_by' ) ) {
			$existing_term = wpcom_vip_get_term_by( 'name', $term, $taxonomy );
		} else {
			$existing_term = get_term_by( 'name', $term, $taxonomy );
		}

		if ( ! $existing_term ) {

			$existing_term = wp_insert_term( $term, $taxonomy );

			if ( ! empty( $existing_term ) && ! is_wp_error( $existing_term ) ) {
				$term_id = $existing_term['term_id'];
			}

		} else {

			$term_id = $existing_term->term_id;

		}

		if ( $term_id ) {

			wp_set_object_terms( $new_post_id, $term_id, $taxonomy );

		}

	}

	/**
	 * Determines if this message should actually be inserted
	 * into the database. Will filter based on whether WP_DEBUG
	 * is defined as true (for info levels) and will throttle
	 * the overall inserts happening to the DB
	 *
	 * @param string $key
	 * @param array $args
	 * @access protected
	 * @return bool
	 */
	protected function insert_permitted( $key, $args ) {

		// if the site is in debug mode, always write to the log
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		} else {
			// in production, do not write info messages to the log
			// unless the filter has been overriden
			if ( 'info' === $args['level'] && ! apply_filters( 'ai_logger_allow_production_info_logs', false ) ) {
				return false;
			}

			// create a unique transient key based on the log key and context
			$context = ! empty( $args['context'] ) ? $args['context'] : '';
			$transient_key = 'ai_log_' . md5( $key . $context );

			// the throttling transient has expired if get_transient
			// returns false, and a new insert should be permitted
			return false === get_transient( $transient_key );
		}

	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * singleton via the `new` operator from outside of this class.
	 */
	protected function __construct() {
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * singleton instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Private unserialize method to prevent unserializing of the
	 * singleton instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}
}
