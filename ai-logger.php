<?php
/**
 * Plugin Name: AI Logger
 * Plugin URI: https://github.com/alleyinteractive/logger
 * Description: A logger tool that stores errors and messages as a custom post type
 * Version: 1.0.0
 * Author: Alley Interactive, Jared Cobb
 * Author URI: https://alley.co/
 * Requires at least: 4.6.0
 * Tested up to: 4.6.1
 *
 * Text Domain: ai-logger
 * Domain Path: /languages/
 *
 * @package AI_Logger
 * @category Core
 * @author jaredcobb
 */

namespace AI_Logger;

use function AI_Logger\generate_autoloader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the one required file.
require_once __DIR__ . '/inc/autoload.php';

try {
	\spl_autoload_register( generate_autoloader( __NAMESPACE__, __DIR__ . '/inc/' ) );
} catch ( \Exception $exception ) {
	wp_die( esc_html__( 'Error generating autoloader.', 'ai-logger' ) );
}

add_action(
	'plugins_loaded',
	function () {
		// Begin execution of the main plugin class.
		( new Plugin( __FILE__ ) )->run();
	},
	10,
	0
);
