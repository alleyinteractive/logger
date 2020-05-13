<?php
/**
 * Meta_Box class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Meta_Box;

/**
 * Logger Meta Box
 *
 * Expose stored logs to the front-end.
 */
abstract class Meta_Box {
	/**
	 * Meta key for the logs.
	 *
	 * @var string
	 */
	protected $meta_key;

	/**
	 * Meta box title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Constructor
	 *
	 * @param string $meta_key Meta key for logs.
	 * @param string $title Title for the meta box.
	 */
	public function __construct( string $meta_key, string $title ) {
		$this->meta_key = $meta_key;
		$this->title    = $title;

		$this->register_meta_box();
	}

	/**
	 * Meta type to retrieve the logs for.
	 *
	 * @return string
	 */
	abstract public function get_meta_type(): string;

	/**
	 * Method to retrieve the meta box for display.
	 */
	abstract public function register_meta_box();

	/**
	 * Render the meta box.
	 *
	 * @param mixed $object Object to render meta box for.
	 */
	public function render_meta_box( $object ) {
		if ( $object instanceof \WP_Post ) {
			$object_id = $object->ID;
		} elseif ( $object instanceof \WP_Term ) {
			$object_id = $object->term_id;
		} else {
			// Bail if unknown.
			return;
		}

		$logs = \get_metadata( $this->get_meta_type(), $object_id, $this->meta_key, false );

		if ( empty( $logs ) || ! is_array( $logs ) ) {
			return;
		}

		if ( 'term' === $this->get_meta_type() ) {
			printf(
				'<h4>%s</h4>',
				esc_html( $this->title )
			);
		}

		include AI_LOGGER_PATH . '/template-parts/meta-box.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
	}
}
