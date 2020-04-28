<?php
/**
 * AI_Logger class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

/**
 * Main class responsible for defining the logger functionality
 */
class AI_Logger {

	/**
	 * Class instance.
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
	 * A collection of log entries that should be inspected
	 * for inclusion in a DB insert at the end of the WP
	 * lifecycle.
	 *
	 * @var array
	 * @access protected
	 */
	protected $log_stack = array();

	/**
	 * Flag to write the logs on shutodown.
	 *
	 * @var bool
	 */
	public $write_on_shutdown = true;

	/**
	 * Get the instance of this singleton
	 *
	 * @access public
	 * @static
	 * @return AI_Logger
	 */
	public static function instance() {

		if ( ! isset( static::$instance ) ) {
			static::$instance = new AI_Logger();
		}

		return static::$instance;
	}

	/**
	 * Register various actions & filters, initialize the object
	 *
	 * @access public
	 * @return void
	 */
	protected function __construct() {
		/**
		 * Log levels according to RFC 5424.
		 *
		 * @link https://tools.ietf.org/html/rfc5424
		 */
		$this->allowed_levels = array(
			'emergency' => __( 'Emergency', 'ai-logger' ),
			'alert'     => __( 'Alert', 'ai-logger' ),
			'critical'  => __( 'Critical', 'ai-logger' ),
			'error'     => __( 'Error', 'ai-logger' ),
			'warning'   => __( 'Warning', 'ai-logger' ),
			'notice'    => __( 'Notice', 'ai-logger' ),
			'info'      => __( 'Info', 'ai-logger' ),
			'debug'     => __( 'Debug', 'ai-logger' ),
			'log'       => __( 'Log', 'ai-logger' ),
		);

		$this->throttle_limit = apply_filters( 'ai_logger_throttle_limit', MINUTE_IN_SECONDS * 15 );
	}

	/**
	 * Inserts a new log entry
	 *
	 * @param string $key A short and unique title for the log entry.
	 * @param string $message An info or error message.
	 * @param array  $args Arguments (optional).
	 * @access public
	 * @return void
	 */
	public function insert( $key, $message, $args = array() ) {

		$defaults = array(
			'level'               => 'error',
			'context'             => '',
			'include_stack_trace' => true,
		);

		// Parse incoming $args and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		/**
		 * Validate the level term is actually an allowed term for this taxonomy
		 * otherwise just force the log level to be 'error'.
		 */
		if ( ! array_key_exists( $args['level'], $this->allowed_levels ) ) {
			$args['level'] = 'error';
		}

		if ( true === $args['include_stack_trace'] ) {
			$e        = new \Exception();
			$message .= "\r\n\r\n" . esc_html( $e->getTraceAsString() );
		}

		/**
		 * Add the log entry to the top of the stack, using the transient key
		 * as the array key.
		 */
		$transient_key = 'ai_log_' . md5( $key . $args['context'] );
		if ( ! array_key_exists( $transient_key, $this->log_stack ) ) {
			$this->log_stack[ $transient_key ] = array(
				'key'     => $key,
				'message' => $message,
				'args'    => $args,
			);
		}

		if ( ! $this->write_on_shutdown ) {
			$this->record_logs();
		}
	}

	/**
	 * Callback for the 'shutdown' hook
	 *
	 * This function will parse the array of collected
	 * log entries and record them to the database
	 *
	 * @access public
	 * @return void
	 */
	public function record_logs() {
		// Loop through the array of possible log entries.
		foreach ( $this->log_stack as $transient_key => $log ) {
			// Determine if this insert should actually write to the DB.
			if ( $this->insert_permitted( $transient_key, $log ) ) {
				$post_args   = array(
					'post_title'     => $log['key'],
					'post_status'    => 'publish',
					'post_type'      => 'ai_log',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_content'   => $log['message'],
				);
				$new_post_id = wp_insert_post( $post_args );

				if ( $new_post_id ) {
					$this->assign_terms( $new_post_id, $this->allowed_levels[ $log['args']['level'] ], 'ai_log_level' );
					if ( ! empty( $log['args']['context'] ) ) {
						$this->assign_terms( $new_post_id, $log['args']['context'], 'ai_log_context' );
					}
				}

				// create a unique transient key based on the log key and context.
				if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
					set_transient( $transient_key, true, $this->throttle_limit );
				}
			}

			// Remove the log from the stack.
			unset( $this->log_stack[ $transient_key ] );
		}
	}

	/**
	 * Assign the terms associated with the new post, currently
	 * used to apply a Log Level (info, warning, error) and the
	 * custom context to a log
	 *
	 * @param int    $new_post_id Post ID.
	 * @param string $term Term name.
	 * @param string $taxonomy Taxonomy name.
	 * @access protected
	 * @return void
	 */
	protected function assign_terms( $new_post_id, $term, $taxonomy ) {
		$term_id       = false;
		$existing_term = get_term_by( 'name', $term, $taxonomy );

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
	 * @param string $transient_key Transient key to store to.
	 * @param array  $log Log arguments.
	 * @access protected
	 * @return bool
	 */
	protected function insert_permitted( $transient_key, $log ): bool {
		// If the site is in debug mode, always write to the log.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		} else {
			/**
			 * In production, do not write info messages to the log unless the
			 * filter has been overridden.
			 */
			if ( 'info' === $log['args']['level'] && ! apply_filters( 'ai_logger_allow_production_info_logs', false ) ) {
				return false;
			}

			/**
			 * The throttling transient has expired if get_transient returns false,
			 * and a new insert should be permitted.
			 */
			return false === get_transient( $transient_key );
		}
	}
}
