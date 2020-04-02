<?php

/**
 * Taxonomy for Log Type
 */
class AI_Logger_Taxonomy_Level extends AI_Logger_Taxonomy {

	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	public $name = 'ai_log_level';

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
		register_taxonomy(
			$this->name,
			$this->object_types,
			array(
				'labels'            => array(
					'name'                  => __( 'Level', 'ai-logger' ),
					'singular_name'         => __( 'Level', 'ai-logger' ),
					'search_items'          => __( 'Search Levels', 'ai-logger' ),
					'popular_items'         => __( 'Popular Levels', 'ai-logger' ),
					'all_items'             => __( 'All Levels', 'ai-logger' ),
					'edit_item'             => __( 'Edit Level', 'ai-logger' ),
					'view_item'             => __( 'View Level', 'ai-logger' ),
					'update_item'           => __( 'Update Level', 'ai-logger' ),
					'add_new_item'          => __( 'Add New Level', 'ai-logger' ),
					'new_item_name'         => __( 'New Level', 'ai-logger' ),
					'add_or_remove_items'   => __( 'Add or remove Levels', 'ai-logger' ),
					'choose_from_most_used' => __( 'Choose from most used Levels', 'ai-logger' ),
					'menu_name'             => __( 'Levels', 'ai-logger' ),
					'not_found'             => __( 'No levels found', 'ai-logger' ),
				),
				'public'            => false,
				'show_admin_column' => true,
				'show_in_nav_menus' => false,
				'show_tagcloud'     => false,
			) 
		);
	}

}

$ai_logger_taxonomy_level = new AI_Logger_Taxonomy_Level();
