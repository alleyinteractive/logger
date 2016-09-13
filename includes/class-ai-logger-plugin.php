<?php
/**
 * Main class responsible for defining plugin settings, hooks, and filters
 */
class AI_Logger_Plugin {

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
	 * @access public
	 * @return void
	 */
	public function __construct( $plugin_root ) {
		$this->token = 'ai-logger';
		$this->plugin_url = apply_filters( 'ai_logger_plugin_dir_url', plugin_dir_url( $plugin_root ) );
		$this->plugin_path = apply_filters( 'ai_logger_plugin_dir_path', plugin_dir_path( $plugin_root ) );
		$this->version = '1.0.0';
	}

	/**
	 * Register the actions and filters
	 *
	 * @access public
	 * @return void
	 */
	public function run() {
		// required includes
		require_once( $this->plugin_path . 'includes/class-ai-logger-post-type.php' );
		require_once( $this->plugin_path . 'includes/class-ai-logger-post-type-log.php' );
		require_once( $this->plugin_path . 'includes/class-ai-logger-taxonomy.php' );
		require_once( $this->plugin_path . 'includes/class-ai-logger-taxonomy-context.php' );
		require_once( $this->plugin_path . 'includes/class-ai-logger-taxonomy-level.php' );
		require_once( $this->plugin_path . 'includes/class-ai-logger.php' );
		require_once( $this->plugin_path . 'includes/template-tags.php' );

		// hooks and filters
		add_action( 'shutdown', array( AI_Logger::instance(), 'record_logs' ) );
	}
}
