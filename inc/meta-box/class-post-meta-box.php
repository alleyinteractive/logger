<?php
/**
 * Post_Meta_Box class file.
 *
 * @package AI_Logger
 */

namespace AI_Logger\Meta_Box;

/**
 * Post Log Meta Box
 */
class Post_Meta_Box extends Meta_Box {
	/**
	 * Meta type to retrieve the logs for.
	 *
	 * @return string
	 */
	public function get_meta_type(): string {
		return 'post';
	}

	/**
	 * Method to retrieve the meta box for display.
	 */
	public function register_meta_box() {
		\add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
	}

	/**
	 * Register the log meta box.
	 *
	 * @param string   $post_type Post type.
	 * @param \WP_post $post Post object.
	 */
	public function register_meta_boxes( $post_type, $post ) {
		$meta = \get_post_meta( $this->object_id, $this->meta_key, false );

		if ( empty( $meta ) || ! is_array( $meta ) ) {
			return;
		}

		\add_meta_box(
			'ai-logger-' . $this->meta_key,
			$this->title,
			[ $this, 'render_meta_box' ],
			$post_type,
			'normal',
			'low'
		);
	}
}
