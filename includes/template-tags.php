<?php
/**
 * Optional helper functions that make interacting with the plugin class easier
 * These are intended to be wrapper functions, so they should not be required
 */

if ( ! function_exists( 'ai_logger_insert' ) ) {

	/**
	 * Inserts a new log entry
	 *
	 * @param string $key A short and unique title for the log entry
	 * @param string $message An info or error message
	 * @param array $args Optional
	 * @access public
	 * @return void
	 */
	function ai_logger_insert( $key, $message, $args = array() ) {
		AI_Logger::instance()->insert( $key, $message, $args );
	}

}
