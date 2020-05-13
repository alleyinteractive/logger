<?php
namespace AI_Logger\Tests;

use AI_Logger\AI_Logger;
use AI_Logger\Handler\{
	Post_Handler,
	Post_Meta_Handler,
	Term_Meta_Handler
};

/**
 * Test log handlers.
 */
class Test_Class_Handler extends \WP_UnitTestCase {
	public function test_post_handler() {
		$post_id = $this->factory->post->create();

		$this->assertEmpty( get_post_meta( $post_id, 'test_key', false ), 'Log should be empty.' );

		// Write the log.
		$post_logger = new Post_Meta_Handler( $post_id, 'test_key' );
		$post_logger->handle( 'info', 'Test message' );
		$post_logger->process_queue();

		$log = get_post_meta( $post_id, 'test_key', false );
		$this->assertInternalType( 'array', $log, 'Log should have log entries.' );

		list( $level, $message ) = array_shift( $log );
		$this->assertEquals( 'info', $level );
		$this->assertEquals( 'Test message', $message );
	}

	public function test_switch_site_writing() {
		$post_id = $this->factory->post->create();

		$this->assertEmpty( get_post_meta( $post_id, 'test_key', false ), 'Log should be empty.' );

		// Write the log.
		$post_logger = new Post_Meta_Handler( $post_id, 'test_key' );
		$post_logger->handle( 'info', 'Test message' );

		$new_blog_id = $this->factory->blog->create();

		switch_to_blog( $new_blog_id );

		// Write to the logger again.
		$post_logger->handle( 'error', 'Error from another site!' );
		// Even try and process the log here.

		$post_logger->process_queue();

		// Go back to the original site.
		restore_current_blog();

		// Now process the log.
		$post_logger->process_queue();

		$log = get_post_meta( $post_id, 'test_key', false );
		$this->assertInternalType( 'array', $log, 'Log should have log entries.' );

		list( $level, $message ) = array_shift( $log );
		$this->assertEquals( 'info', $level );
		$this->assertEquals( 'Test message', $message );

		// Check that the next log was recorded as well.
		list( $level, $message ) = array_shift( $log );
		$this->assertEquals( 'error', $level );
		$this->assertEquals( 'Error from another site!', $message );
	}

	public function test_term_handler() {
		$term_id = $this->factory->term->create();

		$this->assertEmpty( get_term_meta( $term_id, 'test_key', false ), 'Log should be empty.' );

		// Write the log.
		$post_logger = new Term_Meta_Handler( $term_id, 'test_key' );
		$post_logger->handle( 'info', 'Test message' );
		$post_logger->process_queue();

		$log = get_term_meta( $term_id, 'test_key', false );
		$this->assertInternalType( 'array', $log, 'Log should have log entries.' );

		list( $level, $message ) = array_shift( $log );
		$this->assertEquals( 'info', $level );
		$this->assertEquals( 'Test message', $message );
	}

	/**
	 * Test the legacy method to write logs via 'ai_logger_insert'.
	 */
	public function test_legacy_write_logs() {
		// Ensure all logs are written instantly.
		add_filter( 'ai_logger_should_write_on_shutdown', '__return_false', 99 );

		$log_key = 'Log key ' . wp_rand( 1, 1000 );

		// Write to the log.
		\do_action( 'ai_logger_insert', $log_key, 'Log message', [ 'context' => 'log-context' ] );

		// Check if the log exists.
		$logs = get_posts(
			[
				'post_type' => 'ai_log',
			]
		);

		$this->assertNotEmpty( $logs );

		$log = array_shift( $logs );
		$this->assertEquals( $log_key, $log->post_title, 'Log post title should match the "' . $log_key . '"' );

		// Verify the context.
		$this->assertEquals( $this->get_log_context( $log->ID ), 'log-context' );
	}

	public function test_post_logger() {
		// Ensure all logs are written instantly.
		add_filter( 'ai_logger_should_write_on_shutdown', '__return_false', 99 );

		$log_message = 'Direct Log key ' . wp_rand( 1, 1000 );

		AI_Logger::emergency( $log_message, [ 'context' => 'the-context' ] );

		// Check if the log exists.
		$logs = get_posts(
			[
				'post_type' => 'ai_log',
			]
		);

		$this->assertNotEmpty( $logs );

		$log = array_shift( $logs );
		$this->assertEquals( $log_message, $log->post_title, 'Log post title should match the "' . $log_message . '"' );

		// Verify the context.
		$this->assertEquals( $this->get_log_context( $log->ID ), 'the-context' );
	}

	/**
	 * Quick log context getter.
	 *
	 * @param int $log_id Log post ID.
	 * @return string|null
	 */
	protected function get_log_context( int $log_id ): string {
		$terms = \get_the_terms( $log_id, Post_Handler::TAXONOMY_LOG_CONTEXT );
		return ! empty( $terms ) ? $terms[0]->slug : null;
	}
}

