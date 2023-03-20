<?php
/**
 * Logger Test Bootstrap
 */

define( 'MULTISITE', true );

Mantle\Testing\manager()
	->loaded( fn () => require_once __DIR__ . '/../logger.php' )
	->install();
