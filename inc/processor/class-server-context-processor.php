<?php
/**
 * Server Context Processor class file
 *
 * @package AI_Logger
 */

namespace AI_Logger\Processor;

use Monolog\Processor\ProcessorInterface;

/**
 * Include information about the server in the log record.
 */
class Server_Context_Processor implements ProcessorInterface {
	/**
	 * Adds server context to the log record.
	 *
	 * @param array $record The record to process.
	 * @return array The processed record
	 */
	public function __invoke( array $record ): array {
		$record['extra']['php_version'] = phpversion();
		$record['extra']['php_sapi']    = php_sapi_name();
		$record['extra']['php_os']      = PHP_OS;
		$record['extra']['php_uname']   = php_uname();
		$record['extra']['is_wp_cli']   = defined( 'WP_CLI' ) && WP_CLI;
		$record['extra']['is_cron']     = wp_doing_cron();
		$record['extra']['is_rest']     = defined( 'REST_REQUEST' ) && REST_REQUEST;
		$record['extra']['is_ajax']     = defined( 'DOING_AJAX' ) && DOING_AJAX;
		$record['extra']['is_xmlrpc']   = defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST;

		return $record;
	}
}
