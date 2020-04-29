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
		$term = \get_term( $this->object_id );
		if ( $term instanceof \WP_Term ) {
			\add_action( "{$term->taxonomy}_edit_form", [ $this, 'render_meta_box' ], 99 );
		}
	}
}
