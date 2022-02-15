<?php
/**
 * Logger Test Bootstrap
 */

use function Mantle\Testing\tests_add_filter;

define( 'MULTISITE', true );

Mantle\Testing\install(
	function() {
		tests_add_filter(
			'muplugins_loaded',
			function() {
				require __DIR__ . '/../ai-logger.php';
			}
		);
	}
);
