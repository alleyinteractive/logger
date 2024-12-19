<?php
namespace AI_Logger\Tests\Handler;

use AI_Logger\Backtrace\Frame;
use AI_Logger\Handler\Post_Handler;
use Mantle\Testkit\Test_Case;
use Monolog\Logger;
use WP_Post;

class PostHandlerTest extends Test_Case {
	protected Logger $logger;

	protected function setUp(): void {
		parent::setUp();

		$this->logger = new Logger( 'test', [
			new Post_Handler(),
		] );

		// Prevent logging on shutdown by default.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
		add_filter( 'ai_logger_should_write_on_shutdown', '__return_false' );
	}

	public function test_generates_a_backtrace(): void {
		$this->logger->info( 'a message to log' );

		$log = $this->get_last_log();

		$this->assertNotNull( $log );

		$record = get_post_meta( $log->ID, '_logger_record', true );

		$this->assertNotEmpty( $record['extra']['backtrace'] );
		$this->assertContainsOnlyInstancesOf( Frame::class, $record['extra']['backtrace'] );
	}

	protected function get_last_log(): ?WP_Post {
		$logs = get_posts( [ 'post_type' => 'ai_log', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'DESC' ] );

		return array_shift( $logs );
	}
}
