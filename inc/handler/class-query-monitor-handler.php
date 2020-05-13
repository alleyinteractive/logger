<?php
/**
 * Query_Monitor_Handler class file
 *
 * @package AI_Logger
 */

namespace AI_Logger\Handler;

/**
 * Log to Query Monitor.
 */
class Query_Monitor_Handler implements Handler_Interface {
	/**
	 * Clear the stored log, not applicable.
	 */
	public function clear() { }

	/**
	 * Write the log to the Query Monitor on the page.
	 *
	 * @param string $level Log level {@see Psr\Log\LogLevel}.
	 * @param string $message Log message.
	 * @param array  $context Context to store.
	 */
	public function handle( string $level, string $message, array $context = [] ) {
		if ( ! did_action( 'plugins_loaded' ) ) {
			// After wpcom_vip_qm_require().
			\add_action(
				'plugins_loaded',
				function () use ( $level, $message, $context ) {
					$this->handle( $level, $message, $context ); // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UndefinedVariable
				},
				100
			);

			return;
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.NamingConventions.ValidHookName.UseUnderscores
		\do_action( "qm/{$level}", $message, $context );
	}
}
