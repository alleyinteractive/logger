<?php

/**
 * Taxonomy for Log Context
 */
class AI_Logger_Taxonomy_Context extends AI_Logger_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'ai_log_context';

	/**
	 * Build the taxonomy object.
	 */
	public function __construct() {
		$this->object_types = array( 'ai_log' );
		parent::__construct();
	}

	/**
	 * Create taxonomies.
	 */
	public function create_taxonomy() {
		register_taxonomy( $this->name, $this->object_types, array(
			'labels' => array(
				'name'                  	=> __( 'Context', 'ai-logger' ),
				'singular_name'         	=> __( 'Context', 'ai-logger' ),
				'search_items'          	=> __( 'Search Contexts', 'ai-logger' ),
				'popular_items'         	=> __( 'Popular Contexts', 'ai-logger' ),
				'all_items'             	=> __( 'All Contexts', 'ai-logger' ),
				'edit_item'             	=> __( 'Edit Context', 'ai-logger' ),
				'view_item'             	=> __( 'View Context', 'ai-logger' ),
				'update_item'           	=> __( 'Update Context', 'ai-logger' ),
				'add_new_item'          	=> __( 'Add New Context', 'ai-logger' ),
				'new_item_name'         	=> __( 'New Context', 'ai-logger' ),
				'add_or_remove_items'   	=> __( 'Add or remove Contexts', 'ai-logger' ),
				'choose_from_most_used' 	=> __( 'Choose from most used Contexts', 'ai-logger' ),
				'menu_name'             	=> __( 'Contexts', 'ai-logger' ),
				'not_found'             	=> __( 'No levels found', 'ai-logger' ),
			),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		) );
	}

}

$ai_logger_taxonomy_level = new AI_Logger_Taxonomy_Context();
