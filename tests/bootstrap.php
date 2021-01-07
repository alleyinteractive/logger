<?php
/**
 * Logger Test Bootstrap
 */

use function Mantle\Framework\Testing\tests_add_filter;

define( 'MULTISITE', true );

Mantle\Framework\Testing\install(
	function() {
		tests_add_filter(
			'muplugins_loaded',
			function() {
				require __DIR__ . '/../ai-logger.php';
			}
		);
	}
);
