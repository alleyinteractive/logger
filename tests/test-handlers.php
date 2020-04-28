<?php
namespace AI_Logger\Tests;

use AI_Logger\Handler\{
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
		$post_logger->log( 'info', 'Test message' );
		$post_logger->process_queue();

		$log = get_post_meta( $post_id, 'test_key', false );
		$this->assertInternalType( 'array', $log, 'Log should have log entires.' );

		list( $level, $message ) = array_shift( $log );
		$this->assertEquals( 'info', $level );
		$this->assertEquals( 'Test message', $message );
	}

	public function test_switch_site_writing() {
		$post_id = $this->factory->post->create();

		$this->assertEmpty( get_post_meta( $post_id, 'test_key', false ), 'Log should be empty.' );

		// Write the log.
		$post_logger = new Post_Meta_Handler( $post_id, 'test_key' );
		$post_logger->log( 'info', 'Test message' );

		$new_blog_id = $this->factory->blog->create();

		switch_to_blog( $new_blog_id );

		// Write to the logger again.
		$post_logger->log( 'error', 'Error from another site!' );
		// Even try and process the log here.

		$post_logger->process_queue();

		// Go back to the original site.
		restore_current_blog();

		// Now process the log.
		$post_logger->process_queue();

		$log = get_post_meta( $post_id, 'test_key', false );
		$this->assertInternalType( 'array', $log, 'Log should have log entires.' );

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
		$post_logger->log( 'info', 'Test message' );
		$post_logger->process_queue();

		$log = get_term_meta( $term_id, 'test_key', false );
		$this->assertInternalType( 'array', $log, 'Log should have log entires.' );

		list( $level, $message ) = array_shift( $log );
		$this->assertEquals( 'info', $level );
		$this->assertEquals( 'Test message', $message );
	}
}

