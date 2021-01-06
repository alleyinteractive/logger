<?php
namespace AI_Logger\Tests;

use AI_Logger\AI_Logger;
use Mantle\Framework\Testing\Framework_Test_Case;
use Psr\Log\LoggerInterface;

class Test_Logger extends Framework_Test_Case {
	public function test_psr_instance() {
		$this->assertInstanceOf( LoggerInterface::class, AI_Logger::instance() );
	}

	public function test_to_post() {
		$post_id = static::factory()->post->create();

		$this->assertEmpty( get_post_meta( $post_id, 'log_key', true ) );

		ai_logger()->to_post( 'log_key', $post_id )->info( 'Log Message' );

		$this->assertNotEmpty( get_post_meta( $post_id, 'log_key', true ) );
	}

	public function test_to_term() {
		$term_id = static::factory()->post->create();

		$this->assertEmpty( get_term_meta( $term_id, 'log_key', true ) );

		ai_logger()->to_term( 'log_key', $term_id )->info( 'Log Message' );

		$this->assertNotEmpty( get_term_meta( $term_id, 'log_key', true ) );
	}
}
