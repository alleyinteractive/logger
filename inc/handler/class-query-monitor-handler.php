<?php
/**
 * Query_Monitor_Handler class file
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * Log to Query Monitor.
 */
class Query_Monitor_Handler extends AbstractProcessingHandler {
	/**
	 * Write the log to the Query Monitor on the page.
	 *
	 * @link https://github.com/php-fig/log/blob/master/Psr/Log/AbstractLogger.php
	 *
	 * @param array $record Log Record.
	 */
	protected function write( array $record ): void {
		if ( ! did_action( 'plugins_loaded' ) ) {
			// After wpcom_vip_qm_require().
			\add_action(
				'plugins_loaded',
				function () use ( $record ) {
					$this->write( $record ); // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UndefinedVariable
				},
				100
			);

			return;
		}

		[
			'context' => $context,
			'level'   => $level,
			'message' => $message,
		] = $record;

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.ValidHookName.UseUnderscores
		\do_action( "qm/{$level}", $message, $context );
	}
}
