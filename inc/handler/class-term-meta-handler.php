<?php
/**
 * Term_Meta_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

/**
 * Term Meta Handler
 *
 * Provides storage of logs to the term's meta.
 */
class Term_Meta_Handler extends Meta_Handler {
	/**
	 * Get the meta type for the logger.
	 *
	 * @return string
	 */
	public function get_meta_type(): string {
		return 'term';
	}
}
