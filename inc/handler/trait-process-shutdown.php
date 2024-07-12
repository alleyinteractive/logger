<?php
/**
 * Process_Shutdown trait
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

/**
 * Trait to check if the log should be written on shutdown.
 */
trait Process_Shutdown {
	/**
	 * Check if the log should be written on shutdown.
	 *
	 * @return bool
	 */
	protected function should_write_on_shutdown(): bool {
		return (bool) \apply_filters( 'ai_logger_should_write_on_shutdown', wp_using_themes() );
	}
}
