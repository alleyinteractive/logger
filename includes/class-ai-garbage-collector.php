<?php
/**
 * AI_Garbage_Collector class file.
 *
 * @package ai
 */

/**
 * AI Logger Garbage Collector
 *
 * Removes logs after a certain number of days.
 */
class AI_Garbage_Collector {
	/**
	 * Cron hook for garbage cleanup.
	 *
	 * @var string
	 */
	public const CRON_HOOK = 'ai_logger_cleanup';

	/**
	 * Register hooks.
	 */
	public static function add_hooks() {
		if (
			false === \has_action( static::CRON_HOOK )
			&& true === apply_filters( 'ai_garbage_collector_enabled', false )
		) {
			\add_action( static::CRON_HOOK, [ static::class, 'run_cleanup' ] );

			// Schedule the next run if it isn't already.
			if ( false === \wp_next_scheduled( static::CRON_HOOK ) ) {
				\wp_schedule_single_event( time() + ( HOUR_IN_SECONDS * 12 ), static::CRON_HOOK );
			}
		}
	}

	/**
	 * Run the garbage collector.
	 */
	public static function run_cleanup() {

	}
}

AI_Garbage_Collector::add_hooks();
