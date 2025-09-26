<?php
/**
 * Post_Type_Arguments class file
 *
 * @package Mantle
 */

namespace Mantle\Support\Registration;

use Mantle\Contracts\Support\Arrayable;
use Mantle\Support\Str;
use Mantle\Support\Traits\Makeable;

use function Mantle\Support\Helpers\collect;

/**
 * Fluent interface for creating arguments to register a post type.
 */
class Post_Type_Arguments implements Arrayable {
	use Makeable;

	/**
	 * Arguments to register the post with.
	 *
	 * @var array<string, mixed>
	 */
	protected array $arguments = [];

	/**
	 * Get the instance as an array.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return $this->arguments;
	}

	/**
	 * Get all the arguments.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		return $this->arguments;
	}

	/**
	 * Set the label for the post type.
	 *
	 * @param string      $singular The singular label for the post type.
	 * @param string|null $plural The plural label for the post type. Optional.
	 */
	public function label( string $singular, ?string $plural = null ): static {
		if ( ! isset( $this->arguments['labels'] ) || ! is_array( $this->arguments['labels'] ) ) {
			$this->arguments['labels'] = [];
		}

		if ( ! $plural ) {
			$plural = Str::plural( $singular );
		}

		$this->arguments['labels'] = array_merge(
			$this->arguments['labels'],
			[
				'name'                     => $plural,
				'singular_name'            => $singular,
				/* translators: %s: Singular post type name. */
				'add_new'                  => sprintf( __( 'Add New %s', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'add_new_item'             => sprintf( __( 'Add New %s', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'edit_item'                => sprintf( __( 'Edit %s', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'new_item'                 => sprintf( __( 'New %s', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'view_item'                => sprintf( __( 'View %s', 'mantle' ), $singular ),
				/* translators: %s: Plural post type name. */
				'view_items'               => sprintf( __( 'View %s', 'mantle' ), $plural ),
				/* translators: %s: Plural post type name. */
				'search_items'             => sprintf( __( 'Search %s', 'mantle' ), $plural ),
				/* translators: %s: Plural post type name. */
				'not_found'                => sprintf( __( 'No %s found.', 'mantle' ), strtolower( $plural ) ),
				/* translators: %s: Plural post type name. */
				'not_found_in_trash'       => sprintf( __( 'No %s found in Trash.', 'mantle' ), strtolower( $plural ) ),
				'parent_item_colon'        => __( 'Parent Item:', 'mantle' ),
				/* translators: %s: Plural post type name. */
				'all_items'                => sprintf( __( 'All %s', 'mantle' ), $plural ),
				/* translators: %s: Singular post type name. */
				'archives'                 => sprintf( __( '%s Archives', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'attributes'               => sprintf( __( '%s Attributes', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'insert_into_item'         => sprintf( __( 'Insert into %s', 'mantle' ), strtolower( $singular ) ),
				/* translators: %s: Singular post type name. */
				'uploaded_to_this_item'    => sprintf( __( 'Uploaded to this %s', 'mantle' ), strtolower( $singular ) ),
				'featured_image'           => __( 'Featured Image', 'mantle' ),
				'set_featured_image'       => __( 'Set featured image', 'mantle' ),
				'remove_featured_image'    => __( 'Remove featured image', 'mantle' ),
				'use_featured_image'       => __( 'Use as featured image', 'mantle' ),
				'menu_name'                => $plural,
				/* translators: %s: Singular Post type name. */
				'filter_items_list'        => sprintf( __( 'Filter %s list', 'mantle' ), strtolower( $plural ) ),
				'filter_by_date'           => __( 'Filter by date', 'mantle' ),
				/* translators: %s: Plural post type name. */
				'items_list_navigation'    => sprintf( __( '%s list navigation', 'mantle' ), $plural ),
				/* translators: %s: Plural post type name. */
				'items_list'               => sprintf( __( '%s list', 'mantle' ), $plural ),
				/* translators: %s: Singular post type name. */
				'item_published'           => sprintf( __( '%s published.', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'item_published_privately' => sprintf( __( '%s published privately.', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'item_reverted_to_draft'   => sprintf( __( '%s reverted to draft.', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'item_trashed'             => sprintf( __( '%s trashed.', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'item_scheduled'           => sprintf( __( '%s scheduled.', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'item_updated'             => sprintf( __( '%s updated.', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'item_link'                => sprintf( __( '%s Link', 'mantle' ), $singular ),
				/* translators: %s: Singular post type name. */
				'item_link_description'    => sprintf( __( 'A link to a %s.', 'mantle' ), strtolower( $singular ) ),
			],
		);

		$this->arguments['labels']['name'] = $plural;

		$this->arguments['labels']['singular_name'] = $singular;

		return $this;
	}

	/**
	 * Set a specific label for the post type.
	 *
	 * @see get_post_type_labels()
	 *
	 * @param string|array<string, string> $key The key for the label or an array of labels.
	 * @param string                       $value The value for the label.
	 */
	public function labels( array|string $key, ?string $value = null ): static {
		if ( is_array( $key ) ) {
			$this->arguments['labels'] = array_merge( $this->arguments['labels'], $key );

			return $this;
		}

		if ( $value ) {
			$this->arguments['labels'][ $key ] = $value;
		} else {
			unset( $this->arguments['labels'][ $key ] );
		}

		return $this;
	}

	/**
	 * Set the description for the post type.
	 *
	 * @param string $description The description for the post type.
	 */
	public function description( string $description ): static {
		$this->arguments['description'] = $description;

		return $this;
	}

	/**
	 * Set the public argument for the post type.
	 *
	 * @param bool $public Whether the post type is public.
	 */
	public function public( bool $public = true ): static {
		$this->arguments['public'] = $public;

		return $this;
	}

	/**
	 * Set the hierarchical argument for the post type.
	 *
	 * @param bool $hierarchical Whether the post type is hierarchical.
	 */
	public function hierarchical( bool $hierarchical = true ): static {
		$this->arguments['hierarchical'] = $hierarchical;

		return $this;
	}

	/**
	 * Set the exclude_from_search argument for the post type.
	 *
	 * @param bool $exclude_from_search Whether to exclude from search.
	 */
	public function exclude_from_search( bool $exclude_from_search = true ): static {
		$this->arguments['exclude_from_search'] = $exclude_from_search;

		return $this;
	}

	/**
	 * Set the publicly_queryable argument for the post type.
	 *
	 * @param bool $publicly_queryable Whether the post type is publicly queryable.
	 */
	public function publicly_queryable( bool $publicly_queryable = true ): static {
		$this->arguments['publicly_queryable'] = $publicly_queryable;

		return $this;
	}

	/**
	 * Set the show_ui argument for the post type.
	 *
	 * @param bool $show_ui Whether to show the UI for the post type.
	 */
	public function show_ui( bool $show_ui = true ): static {
		$this->arguments['show_ui'] = $show_ui;

		return $this;
	}

	/**
	 * Set the show_in_menu argument for the post type.
	 *
	 * @param bool|string $show_in_menu Whether to show in menu.
	 */
	public function show_in_menu( bool|string $show_in_menu = true ): static {
		$this->arguments['show_in_menu'] = $show_in_menu;

		return $this;
	}

	/**
	 * Set the show_in_admin_bar argument for the post type.
	 *
	 * @param bool $show_in_admin_bar Whether to show in admin bar.
	 */
	public function show_in_admin_bar( bool $show_in_admin_bar = true ): static {
		$this->arguments['show_in_admin_bar'] = $show_in_admin_bar;

		return $this;
	}

	/**
	 * Set the menu position argument for the post type.
	 *
	 * @param int|null $menu_position
	 */
	public function menu_position( ?int $menu_position ): static {
		$this->arguments['menu_position'] = $menu_position;

		return $this;
	}

	/**
	 * Set the menu_icon argument for the post type.
	 *
	 * @param string $menu_icon The menu icon for the post type.
	 */
	public function menu_icon( string $menu_icon ): static {
		$this->arguments['menu_icon'] = $menu_icon;

		return $this;
	}

	/**
	 * Set the show_in_nav_menus argument for the post type.
	 *
	 * @param bool $show_in_nav_menus Whether to show in nav menus.
	 */
	public function show_in_nav_menus( bool $show_in_nav_menus = true ): static {
		$this->arguments['show_in_nav_menus'] = $show_in_nav_menus;

		return $this;
	}

	/**
	 * Set the show_in_rest argument for the post type.
	 *
	 * @param bool $show_in_rest Whether to show in REST.
	 */
	public function show_in_rest( bool $show_in_rest = true ): static {
		$this->arguments['show_in_rest'] = $show_in_rest;

		return $this;
	}

	/**
	 * Set the rest_base argument for the post type.
	 *
	 * @param string $rest_base The REST base for the post type.
	 */
	public function rest_base( string $rest_base ): static {
		$this->arguments['rest_base'] = $rest_base;

		return $this;
	}

	/**
	 * Set the rest_namespace argument for the post type.
	 *
	 * @param string $rest_namespace The REST namespace for the post type.
	 */
	public function rest_namespace( string $rest_namespace ): static {
		$this->arguments['rest_namespace'] = $rest_namespace;

		return $this;
	}

	/**
	 * Set the rest_controller_class argument for the post type.
	 *
	 * @param string $rest_controller_class The REST controller class for the post type.
	 */
	public function rest_controller_class( string $rest_controller_class ): static {
		$this->arguments['rest_controller_class'] = $rest_controller_class;

		return $this;
	}

	/**
	 * Set the capability_type argument for the post type.
	 *
	 * @param string|array{0: string, 1: string} $capability_type The capability type for the post type.
	 */
	public function capability_type( string|array $capability_type ): static {
		$this->arguments['capability_type'] = $capability_type;

		return $this;
	}

	/**
	 * Array of capabilities for this post type. $capability_type is used as a
	 * base to construct capabilities by default.
	 *
	 * @see get_post_type_capabilities()
	 *
	 * @param array<string, string> $capabilities Array of capabilities for this post type.
	 */
	public function capabilities( array $capabilities ): static {
		$this->arguments['capabilities'] = $capabilities;

		return $this;
	}

	/**
	 * Set the map_meta_cap argument for the post type.
	 *
	 * @param bool $map_meta_cap Whether to map meta capabilities.
	 */
	public function map_meta_cap( bool $map_meta_cap = true ): static {
		$this->arguments['map_meta_cap'] = $map_meta_cap;

		return $this;
	}

	/**
	 * Set the supports argument for the post type.
	 *
	 * @param array<string> $supports Array of features the post type supports.
	 */
	public function supports( array $supports ): static {
		$this->arguments['supports'] = $supports;

		return $this;
	}

	/**
	 * Set a default set of supports for the post type.
	 *
	 * @param array<string> $except Array of features to exclude from the default set.
	 */
	public function default_supports( array $except = [] ): static {
		$this->arguments['supports'] = collect( $this->arguments['supports'] ?? [] )
			->merge( [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'revisions' ] )
			->unique()
			->reject( fn ( string $value ) => in_array( $value, $except, true ) )
			->values()
			->all();

		return $this;
	}

	/**
	 * Add a support feature to the post type.
	 *
	 * @param string $name The name of the support feature.
	 */
	public function add_support( string $name ): static {
		if ( ! isset( $this->arguments['supports'] ) || ! is_array( $this->arguments['supports'] ) ) {
			$this->arguments['supports'] = [];
		}

		if ( ! in_array( $name, $this->arguments['supports'], true ) ) {
			$this->arguments['supports'][] = $name;
		}

		return $this;
	}

	/**
	 * Remove a support feature to the post type.
	 *
	 * @param string $name The name of the support feature.
	 */
	public function remove_support( string $name ): static {
		if ( ! isset( $this->arguments['supports'] ) || ! is_array( $this->arguments['supports'] ) ) {
			return $this;
		}

		$this->arguments['supports'] = collect( $this->arguments['supports'] )
			->reject( fn( $value ) => $value === $name )
			->values()
			->all();

		return $this;
	}

	/**
	 * Set the taxonomies argument for the post type.
	 *
	 * @param array<string> $taxonomies Array of taxonomies for the post type.
	 */
	public function taxonomies( array $taxonomies ): static {
		$this->arguments['taxonomies'] = $taxonomies;

		return $this;
	}

	/**
	 * Set the register_meta_box_cb argument for the post type.
	 *
	 * @param callable $register_meta_box_cb The callback to register meta boxes.
	 */
	public function register_meta_box_cb( callable $register_meta_box_cb ): static {
		$this->arguments['register_meta_box_cb'] = $register_meta_box_cb;

		return $this;
	}

	/**
	 * Set the has_archive argument for the post type.
	 *
	 * @param bool|string $has_archive Whether the post type has an archive.
	 */
	public function has_archive( bool|string $has_archive = true ): static {
		$this->arguments['has_archive'] = $has_archive;

		return $this;
	}

	/**
	 * Set the rewrite argument for the post type.
	 *
	 * @param bool|array $rewrite The rewrite rules for the post type.
	 * @phpstan-param bool|array{
	 *   slug?: string,
	 *   with_front?: bool,
	 *   feeds?: bool,
	 *   pages?: bool,
	 *   ep_mask?: int,
	 * } $rewrite
	 */
	public function rewrite( array|bool $rewrite ): static {
		$this->arguments['rewrite'] = $rewrite;

		return $this;
	}

	/**
	 * Set the query_var argument for the post type.
	 *
	 * @param bool|string $query_var Whether to use a query var.
	 */
	public function query_var( bool|string $query_var = true ): static {
		$this->arguments['query_var'] = $query_var;

		return $this;
	}

	/**
	 * Set the template argument for the post type.
	 *
	 * @param array<array<mixed>> $template Array of blocks to use as the default
	 *                                      initial state session. Each item
	 *                                      should be an array containing block
	 *                                      name and optional attributes. Default
	 *                                      empty array.
	 */
	public function template( array $template ): static {
		$this->arguments['template'] = $template;

		return $this;
	}

	/**
	 * Set the template_lock argument for the post type.
	 *
	 * @param bool|string $template_lock Whether to lock the template.
	 */
	public function template_lock( bool|string $template_lock ): static {
		$this->arguments['template_lock'] = $template_lock;

		return $this;
	}

	/**
	 * Handle dynamic method calls to set arguments.
	 *
	 * @throws \InvalidArgumentException If no arguments are provided.
	 *
	 * @param string       $name The name of the method called.
	 * @param array<mixed> $arguments The arguments passed to the method.
	 */
	public function __call( string $name, array $arguments ): static {
		if ( empty( $arguments ) ) {
			throw new \InvalidArgumentException( 'No arguments provided for ' . $name );
		}

		$this->arguments[ $name ] = $arguments[0];

		return $this;
	}
}
