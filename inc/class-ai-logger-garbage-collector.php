<?php
/**
 * AI_Logger_Garbage_Collector class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

/**
 * AI Logger Garbage Collector
 *
 * Removes logs after a certain number of days, disabled by default.
 */
class AI_Logger_Garbage_Collector {
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
		add_action( 'cron_schedules', [ static::class, 'add_cron_interval' ] );

		if (
			false === \has_action( static::CRON_HOOK )

			/**
			 * Enable/disable the garbage collector.
			 *
			 * @param bool $enabled Flag to enable the garbage collector (defaults to true).
			 */
			&& true === apply_filters( 'ai_logger_garbage_collector_enabled', true )
		) {
			\add_action( static::CRON_HOOK, [ static::class, 'run_cleanup' ] );

			// Schedule the next run if it isn't already.
			if ( false === \wp_next_scheduled( static::CRON_HOOK ) ) {
				\wp_schedule_event( time(), 'every_three_hours', static::CRON_HOOK );
			}
		}
	}

	/**
	 * Add a custom cron interval.
	 *
	 * @param array $schedules Existing cron schedules.
	 * @return array
	 */
	public static function add_cron_interval( $schedules ): array {
		if ( ! is_array( $schedules ) ) {
			$schedules = [];
		}

		$schedules['every_three_hours'] = [
			'interval' => HOUR_IN_SECONDS * 3,
			'display'  => esc_html__( 'Every 3 Hours', 'ai-logger' ),
		];

		return $schedules;
	}

	/**
	 * Run the garbage collector.
	 *
	 * @param bool $throttle Thorttle the cleanup to a smaller subset of items.
	 */
	public static function run_cleanup( bool $throttle = true ) {
		/**
		 * Max age of the logs to keep before deleting.
		 *
		 * @param int $max_age Max age (in days).
		 */
		$max_age = absint( apply_filters( 'ai_logger_garbage_collector_max_age', 7 ) );

		while ( true ) {
			$logs_to_delete = get_posts( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
				[
					'fields'                 => 'ids',
					'ignore_sticky_posts'    => true,
					'post_type'              => 'ai_log',
					'posts_per_page'         => $throttle ? 100 : 1000,
					'suppress_filters'       => false,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'date_query'             => [
						[
							'before' => "-{$max_age} days",
							'column' => 'post_date',
						],
					],
				]
			);

			if ( empty( $logs_to_delete ) ) {
				break;
			}

			foreach ( $logs_to_delete as $log_id ) {
				wp_delete_post( $log_id, true );
			}

			// Stop at one page if throttled.
			if ( $throttle ) {
				break;
			}
		}
	}
}
