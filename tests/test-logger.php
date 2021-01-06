<?php
namespace AI_Logger\Tests;

use AI_Logger\AI_Logger;
use Mantle\Framework\Testing\Framework_Test_Case;
use Psr\Log\LoggerInterface;

class Test_Logger extends Framework_Test_Case {
	public function test_psr_instance() {
		$this->assertInstanceOf( LoggerInterface::class, AI_Logger::instance() );
	}
}
