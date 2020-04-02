<?php
/**
 * AI_Logger_Plugin class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger;

/**
 * Main class responsible for defining plugin settings, hooks, and filters
 */
class Plugin {

	/**
	 * A unique identifier for various slugs used throughout the plugin
	 *
	 * @var string
	 * @access public
	 */
	public $token;

	/**
	 * The plugin_url which is overridable via the 'ai_logger_plugin_dir_url' filter
	 *
	 * @var string
	 * @access public
	 */
	public $plugin_url;

	/**
	 * The plugin_path which is overridable via the 'ai_logger_plugin_dir_path' filter
	 *
	 * @var string
	 * @access public
	 */
	public $plugin_path;

	/**
	 * Current version of the plugin (used to cache bust resources)
	 *
	 * @var string
	 * @access public
	 */
	public $version;

	/**
	 * Initialize the plugin settings
	 *
	 * @param string $plugin_root Plugin root path.
	 * @access public
	 * @return void
	 */
	public function __construct( $plugin_root ) {
		$this->token       = 'ai-logger';
		$this->plugin_url  = apply_filters( 'ai_logger_plugin_dir_url', plugin_dir_url( $plugin_root ) );
		$this->plugin_path = apply_filters( 'ai_logger_plugin_dir_path', plugin_dir_path( $plugin_root ) );
		$this->version     = '1.0.0';
	}

	/**
	 * Register the actions and filters
	 *
	 * @access public
	 * @return void
	 */
	public function run() {
		new Data_Structures();

		add_action( 'ai_logger_insert', [ AI_Logger::instance(), 'insert' ], 10, 3 );
		add_action( 'shutdown', [ AI_Logger::instance(), 'record_logs' ] );
		add_action( 'init', [ AI_Logger_Garbage_Collector::class, 'add_hooks' ] );
	}
}
