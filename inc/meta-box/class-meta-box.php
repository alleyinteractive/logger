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
	 * Object ID.
	 *
	 * @var int
	 */
	protected $object_id;

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
	 * @param int    $object_id Object ID.
	 * @param string $meta_key Meta key for logs.
	 * @param string $title Title for the meta box.
	 */
	public function __construct( int $object_id, string $meta_key, string $title ) {
		$this->object_id = $object_id;
		$this->meta_key  = $meta_key;
		$this->title     = $title;
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
	 */
	public function render_meta_box() {
		$logs = \get_metadata( $this->get_meta_type(), $this->object_id, $this->meta_key, false );

		if ( empty( $logs ) || ! is_array( $logs ) ) {
			return;
		}

		include __DIR__ . '/template-parts/meta-box.php';
	}
}
