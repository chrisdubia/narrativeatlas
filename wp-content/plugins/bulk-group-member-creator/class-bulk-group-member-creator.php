<?php
/**
 * Plugin Name: BP Add Bulk User to group
 * Version: 1.0.0
 * Author: BuddyDev
 */
/**
 * Login page terms and Conditions helper.
 */
//namespace Narrativeatlas_Helper\Modules\Bulk_Group_Member_Creator;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Class Bulk_Group_Member_Creator
 */
class Bulk_Group_Member_Creator {

	/**
	 * Initialize class
	 */
	public static function boot() {
		$self = new self();

		$self->setup();
	}

	/**
	 * Setup class
	 */
	private function setup() {

		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'create_members' ) );

		add_action( 'wp_ajax_bulk_group_member_creator_get_groups', array( $this, 'get_groups' ) );
		add_action( 'wp_ajax_bulk_group_member_creator_add_to_group', array( $this, 'add_to_group' ) );
	}

	/**
	 * Batch process users.
	 */
	public function add_to_group() {

		// verify nonce
		// check credentials.
		if ( ! is_super_admin() ) {
			wp_send_json_error( array( 'message' => __( 'Invalid access' ) ) );
		}

		$emails = $_POST['emails'];
		$emails = array_map( 'trim', explode( ',', $emails ) );
		if ( empty( $emails ) ) {
			wp_send_json_error( array( "message" => __( 'No email specified.' ) ) );
		}

		$messages  = array();
		$group_ids = array_map( 'absint', explode( ",", $_POST['group_ids'] ) );

		foreach ( $emails as $email ) {

			if ( empty( $email ) ) {
				continue;
			}

			$user = get_user_by( 'email', $email );

			if ( ! $user ) {
				$messages[] = array( 'email' => $email, 'message' => __( 'Not Exists' ) );
				continue;
			}

			foreach ( $group_ids as $group_id ) {
				groups_join_group( $group_id, $user->ID );
			}
			$messages[] = array( 'email' => $email, 'message' => __( 'Added to group' ) );
		}

		wp_send_json_success( $messages );
	}

	/**
	 * Load module assets
	 */
	public function load_assets() {
		$url     = plugin_dir_url(__FILE__);
		$version = '1.0.0';

		wp_register_style(
			'bulk-group-member-creator',
			$url . 'bulk-group-member-creator.css',
			array(),
			$version
		);

		wp_register_script(
			'bulk-group-member-creator',
			$url . 'bulk-group-member-creator.js',
			array( 'jquery', 'underscore', 'jquery-ui-autocomplete' ),
			$version,
			true
		);
	}

	/**
	 * Add menu for adding interface
	 */
	public function add_menu() {

		add_management_page(
			__( 'Bulk Group Member Creator', 'narrativeatlas-helper' ),
			__( 'Bulk Group Member Creator', 'narrativeatlas-helper' ),
			'create_users',
			'bulk-group-member-creator',
			array( $this, 'render' )
		);
	}

	/**
	 * Create members
	 */
	public function create_members() {

	    if ( empty($_POST ) || ! is_super_admin() ) {
	        return;
        }

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk-group-member-creator-create-members' ) ) {
			return;
		}

		$post = wp_unslash( $_POST );

		if ( empty( $post['bulk-group-member-creator-emails'] ) || empty( $post['bulk-group-member-creator-selected-group-ids'] ) ) {
			return;
		}

		$emails = array_map( 'trim', explode( ',', $post['bulk-group-member-creator-emails'] ) );
		$group_ids = array_map( 'absint', $post['bulk-group-member-creator-selected-group-ids'] );

		$joined      = array();
		$not_existed = array();
		foreach ( $emails as $email ) {
			$user = get_user_by( 'email', $email );

			if( ! $user ) {
				$not_existed[] = $email;
				continue;
			}

			foreach ( $group_ids as $group_id ) {
				groups_join_group( $group_id, $user->ID );
			}

			$joined[] = $user->ID;
		}
	}

	/**
	 * Render screen
	 */
	public function render() {

		?>
        <div class="wrap bulk-group-member-creator-wrap">
            <div class="left-side">
            <h3><?php _e( 'Bulk Group Member Creator', 'narrativeatlas-helper' ); ?></h3>
            <hr>
            <form method="post" id="bulk-group-member-creator-form" name="bulk-group-member-creator-form">
                <p>
                    <label for="bulk-group-member-creator-emails"><?php _e( 'Emails', 'narrativeatlas-helper' ); ?></label>
                    <textarea rows="5" cols="50" id="bulk-group-member-creator-emails"
                              placeholder="<?php _e( 'Enter comma(,) separated emails...', 'narrativeatlas-helper' ) ?>"
                              name="bulk-group-member-creator-emails"></textarea>
                </p>

                <p>
                    <label for="bulk-group-member-creator-autocomplete"><?php _e( 'Groups' ); ?></label>
                    <input type="text"
                           data-nonce="<?php echo wp_create_nonce( 'bulk-group-member-creator-get-groups' ); ?>"
                           placeholder="<?php _e( 'Search groups here...' ); ?>"
                           name="bulk-group-member-creator-autocomplete" id="bulk-group-member-creator-autocomplete"/>
                </p>

                <p>
                <ul id="bulk-group-member-creator-group-list"></ul>
                </p>
				<?php wp_nonce_field( 'bulk-group-member-creator-create-members' ); ?>

                <p>
                    <input type="submit" id="bulk-group-member-creator-form-submit-btn" class="button-primary" value="<?php _e( 'Add', 'narrativeatlas-helper' ); ?>">
                </p>
            </form>
            </div>
            <div class="right-side">
                <div id="bgmu-status"></div>
                <h3><?php _e( 'Result', 'narrativeatlas-helper' ); ?></h3>

                <ul id="bgmu-logs">

                </ul>
                <hr>
            </div>
        </div>
		<?php

        wp_enqueue_style( 'bulk-group-member-creator' );
        wp_enqueue_script( 'bulk-group-member-creator' );
	}

	/**
	 * Get groups
	 */
	public function get_groups() {
		check_ajax_referer( 'bulk-group-member-creator-get-groups' );

		$search_query = isset( $_POST['q'] ) ? wp_unslash( $_POST['q'] ) : '';

		if ( empty( $search_query ) ) {
			wp_send_json_error( __( 'Query parameter is empty.', 'narrativeatlas-helper' ) );
		}

		$groups = groups_get_groups( "show_hidden=true&search_terms={$search_query}" );

		if ( empty( $groups['total'] ) ) {
			wp_send_json_error( array() );
		}

		$list = array();

		foreach ( $groups['groups'] as $group ) {
			$list[] = array(
				'label' => bp_get_group_name( $group ),
				'icon'  => bp_core_fetch_avatar( array( 'item_id' => $group->id, 'object' => 'group', ) ),
				'url'   => bp_get_group_permalink( $group ),
				'id'    => $group->id,
			);
		}

		wp_send_json_success( $list );
	}
}

Bulk_Group_Member_Creator::boot();