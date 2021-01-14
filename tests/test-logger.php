<?php
namespace AI_Logger\Tests;

use AI_Logger\AI_Logger;
use Mantle\Framework\Testing\Framework_Test_Case;
use Monolog\Handler\NullHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
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

	public function test_logger_pass_through() {
		$logger = AI_Logger::instance();
		$handler = new TestHandler();
		$logger->get_logger()->setHandlers( [ $handler ] );

		$logger->alert( 'A alert message.' );
		$this->assertTrue( $handler->hasAlert( [ 'message' => 'A alert message.' ] ) );

		$logger->critical( 'A critical message.' );
		$this->assertTrue( $handler->hasCritical( [ 'message' => 'A critical message.' ] ) );

		$logger->error( 'A error message.' );
		$this->assertTrue( $handler->hasError( [ 'message' => 'A error message.' ] ) );

		$logger->warning( 'A warning message.' );
		$this->assertTrue( $handler->hasWarning( [ 'message' => 'A warning message.' ] ) );

		$logger->notice( 'A notice message.' );
		$this->assertTrue( $handler->hasNotice( [ 'message' => 'A notice message.' ] ) );

		$logger->info( 'A info message.' );
		$this->assertTrue( $handler->hasInfo( [ 'message' => 'A info message.' ] ) );

		$logger->debug( 'A debug message.' );
		$this->assertTrue( $handler->hasDebug( [ 'message' => 'A debug message.' ] ) );

		$logger->log( Logger::DEBUG, 'A log message.' );
		$this->assertTrue( $handler->hasDebug( [ 'message' => 'A log message.' ] ) );
	}

	public function test_ai_logger_handlers_filter() {
		$_SERVER['__filter_invoked'] = false;

		add_filter(
			'ai_logger_handlers',
			function() {
				$_SERVER['__filter_invoked'] = true;

				return [ new NullHandler() ];
			}
		);

		$instance = Non_Static_Logger::instance();

		$this->assertTrue( $_SERVER['__filter_invoked'] );
		$this->assertInstanceOf( NullHandler::class, $instance->get_logger()->getHandlers()[0] );
	}
}


class Non_Static_Logger extends AI_Logger {
	public static function instance() {
		return new static();
	}
}
