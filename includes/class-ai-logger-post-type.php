<?php

/**
 * Abstract class for post type classes
 */
abstract class AI_Logger_Post_Type {

	/**
	 * Name of the post type
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Create the post type
		add_action( 'init', array( $this, 'create_post_type' ) );
	}

	/**
	 * Create the post type.
	 */
	abstract public function create_post_type();

	/**
	 * Get the post type object.
	 *
	 * @see get_post_type_object()
	 *
	 * @return object
	 */
	public function get_object() {
		return get_post_type_object( $this->name );
	}
}
