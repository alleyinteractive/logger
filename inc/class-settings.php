<?php
/**
 * Settings class file
 *
 * @package AI_Logger
 */

namespace AI_Logger;

/**
 * Admin Settings for the plugin.
 */
class Settings {
	/**
	 * Class instance.
	 *
	 * @var static
	 */
	protected static $instance;

	/**
	 * Option storage.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Get the instance of this singleton
	 *
	 * @return static
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_action( 'admin_init', [ $this, 'on_admin_init' ] );
		add_action( 'admin_menu', [ $this, 'on_admin_menu' ] );
	}

	/**
	 * Register Admin Settings
	 */
	public function on_admin_init() {
		register_setting( 'ai-logger', 'ai-logger' );

		// Log filters settings.
		add_settings_section( 'logger_filter', __( 'Logger Filter Settings', 'ai-logger' ), '__return_null', 'ai-logger' );

		add_settings_field(
			'filter_error_message',
			__( 'Error Message', 'ai-logger' ),
			[ $this, 'render_field' ],
			'ai-logger',
			'logger_filter',
			[
				'description' => __( 'Filters out error messages matching a given string. Patterns regular expressions and separated by new lines.', 'ai-logger' ),
				'field'       => 'filter_error_message',
				'type'        => 'textarea',
			]
		);
	}

	/**
	 * Register the Admin Settings Page
	 */
	public function on_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ai_log',
			__( 'Log Settings', 'ai-logger' ),
			__( 'Settings', 'ai-logger' ),
			$this->get_settings_page_capability(),
			'ai-logger',
			[ $this, 'render_admin_page' ],
		);
	}

	/**
	 * Retrieve the settings page capability.
	 *
	 * @return string
	 */
	public function get_settings_page_capability() {
		/**
		 * Admin Page Capability
		 *
		 * @param string $capability Capability for menu.
		 */
		return apply_filters( 'ai_logger_admin_capability', 'manage_options' );
	}

	/**
	 * Render the admin menu page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( $this->get_settings_page_capability() ) ) {
			wp_die( esc_html__( 'You do not have permissions to access this page.', 'ai-logger' ) );
		}

		include __DIR__ . '/../template-parts/admin/settings.php';
	}

	/**
	 * Retrieve an option.
	 *
	 * @param string $slug Slug of the option.
	 * @return mixed
	 */
	public function get( string $slug ) {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( 'ai-logger' );
		}

		return $this->options[ $slug ] ?? null;
	}

	/**
	 * Update the settings.
	 *
	 * @param array $options Options to update.
	 */
	public function set( array $options ) {
		$this->options = $options;

		update_option( 'ai-logger', $this->options );
	}

	/**
	 * Render the settings field.
	 *
	 * @param array $args Settings field arguments.
	 */
	public function render_field( $args ) {
		if ( empty( $args['field'] ) ) {
			return;
		}

		if ( empty( $args['type'] ) ) {
			$args['type'] = 'text';
		}

		$value = $this->get( $args['field'] );

		switch ( $args['type'] ) {
			case 'textarea':
				$this->render_textarea( $args, $value );
				break;

			case 'checkboxes':
				$this->render_checkboxes( $args, $value );
				break;

			default:
				$this->render_text_field( $args, $value );
				break;
		}

		if ( ! empty( $args['description'] ) ) {
			printf(
				'<span class="field-description" style="display: block;padding-top:4px;font-size:12px;">%s</span>',
				esc_html( $args['description'] ),
			);
		}
	}

	/**
	 * Render a settings text field.
	 *
	 * @param array  $args {
	 *     An array of arguments for the text field.
	 *
	 *     @type string $field  The field name.
	 *     @type string $type   The field type. Default 'text'.
	 *     @type string $size   The field size. Default 80.
	 * }
	 * @param string $value The current field value.
	 */
	public function render_text_field( $args, $value ) {
		$args = wp_parse_args(
			$args,
			[
				'type' => 'text',
				'size' => 80,
			],
		);

		if ( 'checkbox' === $args['type'] ) {
			$checked = '1' === $value;
			$value   = '1';
		}

		printf(
			'<input type="%s" name="%s[%s]" value="%s" size="%s" %s />',
			esc_attr( $args['type'] ),
			esc_attr( 'ai-logger' ),
			esc_attr( $args['field'] ),
			esc_attr( $value ),
			esc_attr( $args['size'] ),
			'checkbox' === $args['type'] && ! empty( $checked ) ? 'checked' : '',
		);
	}

	/**
	 * Render a settings textarea.
	 *
	 * @param array  $args {
	 *     An array of arguments for the textarea.
	 *
	 *     @type  string $field The field name.
	 *     @type  int    $rows  Rows in the textarea. Default 2.
	 *     @type  int    $cols  Columns in the textarea. Default 80.
	 * }
	 * @param string $value The current field value.
	 */
	public function render_textarea( $args, $value ) {
		$args = wp_parse_args(
			$args,
			[
				'rows' => 5,
				'cols' => 80,
			]
		);

		printf(
			'<textarea name="%s[%s]" rows="%d" cols="%d">%s</textarea>',
			esc_attr( 'ai-logger' ),
			esc_attr( $args['field'] ),
			esc_attr( $args['rows'] ),
			esc_attr( $args['cols'] ),
			esc_textarea( $value )
		);
	}

	/**
	 * Render settings checkboxes.
	 *
	 * @param  array $args {
	 *     An array of arguments for the checkboxes.
	 *
	 *     @type string $field The field name.
	 *     @type array  $boxes An associative array of the value and label
	 *                         of each checkbox.
	 * }
	 * @param  array $values Indexed array of current field values.
	 */
	public function render_checkboxes( $args, $values ) {
		foreach ( $args['boxes'] as $box_value => $box_label ) {
			printf(
				'
					<label for="%1$s_%2$s_%3$s">
						<input id="%1$s_%2$s_%3$s" type="checkbox" name="%1$s[%2$s][]" value="%3$s" %4$s>
						%5$s
					</label><br>',
				esc_attr( 'ai-logger' ),
				esc_attr( $args['field'] ),
				esc_attr( $box_value ),
				is_array( $values ) ? checked( in_array( $box_value, $values, true ), true, false ) : '',
				esc_html( $box_label )
			);
		}
	}
}
