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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-ai-logger-plugin.php';

/**
 * Begin execution of the main plugin class.
 *
 * @access public
 * @return void
 */
function ai_logger_plugin_init() {
	$plugin = new AI_Logger_Plugin( __FILE__ );
	$plugin->run();
}
ai_logger_plugin_init();
