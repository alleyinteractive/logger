<?php
/**
 * Taxonomy_Arguments class file
 *
 * @package Mantle
 */

namespace Mantle\Support\Registration;

use Mantle\Contracts\Support\Arrayable;
use Mantle\Support\Str;
use Mantle\Support\Traits\Makeable;

/**
 * Fluent interface for creating arguments to register a taxonomy.
 */
class Taxonomy_Arguments implements Arrayable {
	use Makeable;

	/**
	 * Arguments to register the taxonomy with.
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
	 * Set the label for the taxonomy.
	 *
	 * @param string      $singular The singular label for the taxonomy.
	 * @param string|null $plural The plural label for the taxonomy. Optional.
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
				'name'                       => $plural,
				'singular_name'              => $singular,
				/* translators: %s: Plural label for the taxonomy. */
				'search_items'               => sprintf( __( 'Search %s' ), $plural ),
				/* translators: %s: Plural label for the taxonomy. */
				'popular_items'              => sprintf( __( 'Popular %s' ), $plural ),
				/* translators: %s: Plural label for the taxonomy. */
				'all_items'                  => sprintf( __( 'All %s' ), $plural ),
				/* translators: %s: Singular label for the taxonomy. */
				'parent_item'                => sprintf( __( 'Parent %s' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'parent_item_colon'          => sprintf( __( 'Parent %s:' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'edit_item'                  => sprintf( __( 'Edit %s' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'view_item'                  => sprintf( __( 'View %s' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'update_item'                => sprintf( __( 'Update %s' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'add_new_item'               => sprintf( __( 'Add New %s' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'new_item_name'              => sprintf( __( 'New %s Name' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'template_name'              => sprintf( __( 'Single Item: %s' ), $singular ),
				/* translators: %s: Plural label for the taxonomy. */
				'separate_items_with_commas' => sprintf( __( 'Separate %s with commas' ), $plural ),
				/* translators: %s: Plural label for the taxonomy. */
				'add_or_remove_items'        => sprintf( __( 'Add or remove %s' ), $plural ),
				/* translators: %s: Plural label for the taxonomy. */
				'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s' ), $plural ),
				/* translators: %s: Plural label for the taxonomy. */
				'not_found'                  => sprintf( __( 'No %s found' ), $plural ),
				/* translators: %s: Plural label for the taxonomy. */
				'no_terms'                   => sprintf( __( 'No %s' ), $plural ),
				/* translators: %s: Singular label for the taxonomy. */
				'filter_by_item'             => sprintf( __( 'Filter by %s' ), $singular ),
				/* translators: %s: Plural label for the taxonomy. */
				'back_to_items'              => sprintf( __( 'Back to %s' ), $plural ),
				/* translators: %s: Singular label for the taxonomy. */
				'item_link'                  => sprintf( __( '%s Link' ), $singular ),
				/* translators: %s: Singular label for the taxonomy. */
				'item_link_description'      => sprintf( __( 'A link to a %s' ), $singular ),
			],
		);

		$this->arguments['labels']['name'] = $plural;

		$this->arguments['labels']['singular_name'] = $singular;

		return $this;
	}

	/**
	 * Set a specific label for the taxonomy.
	 *
	 * @see get_taxonomy_labels()
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
	 * Set the description for the taxonomy.
	 *
	 * @param string $description The description for the taxonomy.
	 */
	public function description( string $description ): static {
		$this->arguments['description'] = $description;

		return $this;
	}

	/**
	 * Set the public argument for the taxonomy.
	 *
	 * @param bool $public Whether the taxonomy is public.
	 */
	public function public( bool $public = true ): static {
		$this->arguments['public'] = $public;

		return $this;
	}

	/**
	 * Set the publicly_queryable argument for the taxonomy.
	 *
	 * @param bool $publicly_queryable Whether the taxonomy is publicly queryable.
	 */
	public function publicly_queryable( bool $publicly_queryable = true ): static {
		$this->arguments['publicly_queryable'] = $publicly_queryable;

		return $this;
	}

	/**
	 * Set the hierarchical argument for the taxonomy.
	 *
	 * @param bool $hierarchical Whether the taxonomy is hierarchical.
	 */
	public function hierarchical( bool $hierarchical = true ): static {
		$this->arguments['hierarchical'] = $hierarchical;

		return $this;
	}

	/**
	 * Set the show_ui argument for the taxonomy.
	 *
	 * @param bool $show_ui Whether to generate a default UI for managing this taxonomy.
	 */
	public function show_ui( bool $show_ui = true ): static {
		$this->arguments['show_ui'] = $show_ui;

		return $this;
	}

	/**
	 * Set the show_in_menu argument for the taxonomy.
	 *
	 * @param bool|string $show_in_menu Whether to show the taxonomy in the admin menu.
	 */
	public function show_in_menu( bool|string $show_in_menu = true ): static {
		$this->arguments['show_in_menu'] = $show_in_menu;

		return $this;
	}

	/**
	 * Set the show_in_nav_menus argument for the taxonomy.
	 *
	 * @param bool $show_in_nav_menus Whether to show the taxonomy in navigation menus.
	 */
	public function show_in_nav_menus( bool $show_in_nav_menus = true ): static {
		$this->arguments['show_in_nav_menus'] = $show_in_nav_menus;

		return $this;
	}

	/**
	 * Set the show_in_rest argument for the taxonomy.
	 *
	 * @param bool $show_in_rest Whether to include the taxonomy in the REST API.
	 */
	public function show_in_rest( bool $show_in_rest = true ): static {
		$this->arguments['show_in_rest'] = $show_in_rest;

		return $this;
	}

	/**
	 * Set the rest_base argument for the taxonomy.
	 *
	 * @param string $rest_base The base slug for the REST API routes.
	 */
	public function rest_base( string $rest_base ): static {
		$this->arguments['rest_base'] = $rest_base;

		return $this;
	}

	/**
	 * Set the rest_namespace argument for the taxonomy.
	 *
	 * @param string $rest_namespace The namespace for the REST API routes.
	 */
	public function rest_namespace( string $rest_namespace ): static {
		$this->arguments['rest_namespace'] = $rest_namespace;

		return $this;
	}

	/**
	 * Set the rest_controller_class argument for the taxonomy.
	 *
	 * @param string $rest_controller_class The controller class for the REST API.
	 */
	public function rest_controller_class( string $rest_controller_class ): static {
		$this->arguments['rest_controller_class'] = $rest_controller_class;

		return $this;
	}

	/**
	 * Set the show_tagcloud argument for the taxonomy.
	 *
	 * @param bool $show_tagcloud Whether to show the tag cloud in the admin.
	 */
	public function show_tagcloud( bool $show_tagcloud = true ): static {
		$this->arguments['show_tagcloud'] = $show_tagcloud;

		return $this;
	}

	/**
	 * Set the show_in_quick_edit argument for the taxonomy.
	 *
	 * @param bool $show_in_quick_edit Whether to show the taxonomy in the quick edit panel.
	 */
	public function show_in_quick_edit( bool $show_in_quick_edit = true ): static {
		$this->arguments['show_in_quick_edit'] = $show_in_quick_edit;

		return $this;
	}

	/**
	 * Set the show_admin_column argument for the taxonomy.
	 *
	 * @param bool $show_admin_column Whether to show the taxonomy in the admin columns.
	 */
	public function show_admin_column( bool $show_admin_column = true ): static {
		$this->arguments['show_admin_column'] = $show_admin_column;

		return $this;
	}

	/**
	 * Set the meta_box_cb argument for the taxonomy.
	 *
	 * @param callable|null $meta_box_cb The callback function for the meta box.
	 */
	public function meta_box_cb( ?callable $meta_box_cb ): static {
		$this->arguments['meta_box_cb'] = $meta_box_cb;

		return $this;
	}

	/**
	 * Set the meta_box_sanitize_cb argument for the taxonomy.
	 *
	 * @param callable|null $meta_box_sanitize_cb The callback function for sanitizing meta box input.
	 */
	public function meta_box_sanitize_cb( ?callable $meta_box_sanitize_cb ): static {
		$this->arguments['meta_box_sanitize_cb'] = $meta_box_sanitize_cb;

		return $this;
	}

	/**
	 * Set the capabilities argument for the taxonomy.
	 *
	 * @param array<string, string> $capabilities The capabilities for the taxonomy.
	 */
	public function capabilities( array $capabilities ): static {
		$this->arguments['capabilities'] = $capabilities;

		return $this;
	}

	/**
	 * Set the rewrite argument for the taxonomy.
	 *
	 * @param false|array<string, mixed> $rewrite The rewrite rules for the taxonomy.
	 * @phpstan-param false|array{
	 *   slug?: string,
	 *   with_front?: bool,
	 *   hierarchical?: bool,
	 *   ep_mask?: string,
	 * } $rewrite
	 */
	public function rewrite( false|array $rewrite ): static {
		$this->arguments['rewrite'] = $rewrite;

		return $this;
	}

	/**
	 * Set the query_var argument for the taxonomy.
	 *
	 * @param false|string $query_var The query variable for the taxonomy.
	 */
	public function query_var( false|string $query_var ): static {
		$this->arguments['query_var'] = $query_var;

		return $this;
	}

	/**
	 * Set the update_count_callback argument for the taxonomy.
	 *
	 * @param callable|null $update_count_callback The callback function for updating the count.
	 */
	public function update_count_callback( ?callable $update_count_callback ): static {
		$this->arguments['update_count_callback'] = $update_count_callback;

		return $this;
	}

	/**
	 * Set the default_term argument for the taxonomy.
	 *
	 * @param array|string $default_term The default term for the taxonomy.
	 * @phpstan-param array{
	 *   name?: string,
	 *   slug?: string,
	 *   description?: string,
	 * } $default_term
	 */
	public function default_term( array|string $default_term ): static {
		$this->arguments['default_term'] = $default_term;

		return $this;
	}

	/**
	 * Whether terms in this taxonomy should be sorted in the order they are
	 * provided to wp_set_object_terms(). Default null which equates to false.
	 *
	 * @param bool $sort Whether to sort terms in the taxonomy.
	 */
	public function sort( bool $sort = false ): static {
		$this->arguments['sort'] = $sort;

		return $this;
	}

	/**
	 * Array of arguments to automatically use inside wp_get_object_terms() for
	 * this taxonomy.
	 *
	 * @param array<mixed> $args Additional arguments for the taxonomy.
	 */
	public function args( array $args ): static {
		$this->arguments['args'] = $args;

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
