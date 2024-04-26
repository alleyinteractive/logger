<?php
/**
 * WordPress_User_Processor class file
 *
 * @package AI_Logger
 */

namespace AI_Logger\Processor;

use Monolog\Processor\ProcessorInterface;

/**
 * Include information about the current WordPress user in the log record.
 */
class WordPress_User_Processor implements ProcessorInterface {
	/**
	 * Adds user context to the log record.
	 *
	 * @param array $record The record to process
	 * @return array The processed record
	 */
	public function __invoke( array $record ): array {
		$user = wp_get_current_user();

		if ( $user ) {
			$record['extra']['user'] = [
				'ID'         => $user->ID,
				'user_login' => $user->user_login,
				'user_email' => $user->user_email,
			];
		}

		return $record;
	}
}
