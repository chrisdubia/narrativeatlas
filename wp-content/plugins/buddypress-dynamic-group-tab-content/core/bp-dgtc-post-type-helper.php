<?php
/**
 * Content type registration.
 *
 * @package    BuddyPress Dynamic Group Tab Content
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Helps Registering Post type and taxonomy.
 */
class BP_Dynamic_Group_Tab_Content_Post_Type_Helper {

	/**
	 * Boot the class.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup hooks.
	 */
	private function setup() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register post type and taxonomy.
	 */
	public function register() {

		$post_type = bp_dynamic_group_tab_content_get_post_type();
		$taxonomy  = bp_dynamic_group_tab_content_get_taxonomy();

		register_post_type( $post_type, array(
			'label'  => __( 'Group Tab Content', 'buddypress-dynamic-group-tab-content' ),
			'labels' => array(
				'name'          => __( 'Tab Contents', 'buddypress-dynamic-group-tab-content' ),
				'singular_name' => __( 'Tab Content', 'buddypress-dynamic-group-tab-content' ),
				'menu_name'     => __( 'Group Tab Content', 'buddypress-dynamic-group-tab-content' ),
				'all_items'     => __( 'Tab Contents', 'buddypress-dynamic-group-tab-content' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'edit.php?post_type=bpgtc_group_tab',
			'menu_position' => 71,
			'taxonomies'    => array( $taxonomy ),
			'supports'      => array( 'title', 'editor' ),
		) );


		register_taxonomy( $taxonomy, $post_type, array(
			'hierarchical'      => true,
			'labels'            => array(
				'name'              => _x( 'Type', 'taxonomy general name', 'buddypress-dynamic-group-tab-content' ),
				'singular_name'     => _x( 'Type', 'taxonomy singular name', 'buddypress-dynamic-group-tab-content' ),
				'search_items'      => __( 'Search Types', 'buddypress-dynamic-group-tab-content' ),
				'all_items'         => __( 'All types', 'buddypress-dynamic-group-tab-content' ),
				'parent_item'       => __( 'Parent type', 'buddypress-dynamic-group-tab-content' ),
				'parent_item_colon' => __( 'Parent Type:', 'buddypress-dynamic-group-tab-content' ),
				'edit_item'         => __( 'Edit Type', 'buddypress-dynamic-group-tab-content' ),
				'update_item'       => __( 'Update Type', 'buddypress-dynamic-group-tab-content' ),
				'add_new_item'      => __( 'Add New Type', 'buddypress-dynamic-group-tab-content' ),
				'new_item_name'     => __( 'New Type Name', 'buddypress-dynamic-group-tab-content' ),
				'menu_name'         => __( 'Type', 'buddypress-dynamic-group-tab-content' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => false,
			'show_in_menu'      => true,
		) );
	}
}

BP_Dynamic_Group_Tab_Content_Post_Type_Helper::boot();
