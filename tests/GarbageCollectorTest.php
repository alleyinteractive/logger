<?php
namespace AI_Logger\Tests;

use AI_Logger\AI_Logger_Garbage_Collector;
use Mantle\Testkit\Test_Case;

/**
 * Test for the Garbage Collector.
 */
class GarbageCollectorTest extends Test_Case {
	public function test_it_schedules_the_cron_event() {
		$this->assertInCronQueue( AI_Logger_Garbage_Collector::CRON_HOOK );
	}

	public function test_it_runs_cleanup() {
		$this->expectApplied( 'ai_logger_garbage_collector_max_age' )->andReturnInteger();

		$old_log_id = static::factory()->post->for( 'ai_log' )->create( [
			'post_date' => date( 'Y-m-d H:i:s', strtotime( '-1 month' ) ),
		] );

		$recent_log_id = static::factory()->post->for( 'ai_log' )->create();

		AI_Logger_Garbage_Collector::run_cleanup( false );

		$this->assertInstanceOf( \WP_Post::class, get_post( $recent_log_id ) );
		$this->assertNull( get_post( $old_log_id ) );
	}
}
