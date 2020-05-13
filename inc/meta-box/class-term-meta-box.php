<?php
/**
 * Term_Meta_Box class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Meta_Box;

/**
 * Term Log Meta Box
 */
class Term_Meta_Box extends Meta_Box {
	/**
	 * Taxonomies to enable it for.
	 *
	 * @var string[]
	 */
	protected $taxonomies = [];

	/**
	 * Constructor
	 *
	 * @param string $meta_key Meta key for logs.
	 * @param string $title Title for the meta box.
	 * @param array  $taxonomies Taxonomies to use.
	 */
	public function __construct( string $meta_key, string $title, array $taxonomies ) {
		$this->taxonomies = $taxonomies;
		parent::__construct( $meta_key, $title );
	}

	/**
	 * Meta type to retrieve the logs for.
	 *
	 * @return string
	 */
	public function get_meta_type(): string {
		return 'term';
	}

	/**
	 * Method to retrieve the meta box for display.
	 */
	public function register_meta_box() {
		foreach ( $this->taxonomies as $taxonomy ) {
			\add_action( "{$taxonomy}_edit_form", [ $this, 'render_meta_box' ], 99 );
		}
	}
}
