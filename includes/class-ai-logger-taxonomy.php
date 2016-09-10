<?php

/**
 * Abstract class for taxonomy classes
 */
abstract class AI_Logger_Taxonomy {

	/**
	 * Name of the taxonomy
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Object types for this taxonomy
	 *
	 * @var array
	 */
	public $object_types = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Create the taxonomy
		add_action( 'init', array( $this, 'create_taxonomy' ) );
	}

	/**
	 * Create the taxonomy.
	 */
	abstract public function create_taxonomy();

	/**
	 * Get the taxonomy object.
	 *
	 * @see get_taxonomy()
	 *
	 * @return object
	 */
	public function get_object() {
		return get_taxonomy( $this->name );
	}

}
