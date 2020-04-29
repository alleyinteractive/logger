<?php
namespace AI_Logger\Tests;

use AI_Logger\Handler\Handler_Interface;
use AI_Logger\Handler\Post_Meta_Handler;
use AI_Logger\Invalid_Handlers_Exception;
use AI_Logger\Logger;

/**
 * Test log handlers.
 */
class Test_Logger extends \WP_UnitTestCase {
	public function test_logger_without_handlers() {
		$this->expectException( Invalid_Handlers_Exception::class );

		$logger = new Logger( 'Test Logger' );
		$logger->info( 'Test message' );
	}

	/**
	 * Test that a logger passes a log down to the handler.
	 */
	public function test_logger_to_handler() {
		$mock_handler = $this
			->getMockBuilder( Handler_Interface::class )
			->setMethods( [ 'handle', 'clear' ] )
			->getMock();

		$mock_handler
			->expects( $this->once() )
			->method( 'handle' )
			->will( $this->returnValue( null ) );

		$mock_handler_2 = $this
			->getMockBuilder( Handler_Interface::class )
			->setMethods( [ 'handle', 'clear' ] )
			->getMock();

		$mock_handler_2
			->expects( $this->once() )
			->method( 'handle' )
			->will( $this->returnValue( null ) );

		$logger = new Logger( 'Test Mock Handler', [ $mock_handler, $mock_handler_2 ] );
		$logger->error( 'Test message', [ 1, 2, 3 ] );
	}
}

