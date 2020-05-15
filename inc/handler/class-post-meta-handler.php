<?php
/**
 * Post_Meta_Handler class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

/**
 * Post Meta Handler
 *
 * Provides storage of logs to the post's meta.
 */
class Post_Meta_Handler extends Meta_Handler {
	/**
	 * Meta key for a general post log.
	 *
	 * @return string
	 */
	public const POST_LOG_KEY = '_post_log';

	/**
	 * Get the meta type for the logger.
	 *
	 * @return string
	 */
	public function get_meta_type(): string {
		return 'post';
	}
}
