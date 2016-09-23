<?php
/**
 * Plugin Name: AI Logger
 * Plugin URI: https://github.com/alleyinteractive/logger
 * Description: A logger tool that stores errors and messages as a custom post type
 * Version: 1.0.0
 * Author: Jared Cobb
 * Author URI: http://www.alleyinteractive.com
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

define( 'AI_LOGGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'AI_LOGGER_URL', plugins_url( '/', __FILE__ ) );

// require the main plugin class
require_once AI_LOGGER_PATH . 'includes/class-ai-logger-plugin.php';

/**
 * Begin execution of the main plugin class
 *
 * @access public
 * @return void
 */
function run_ai_logger_plugin() {
	$plugin = new AI_Logger_Plugin( __FILE__ );
	$plugin->run();
}
run_ai_logger_plugin();
