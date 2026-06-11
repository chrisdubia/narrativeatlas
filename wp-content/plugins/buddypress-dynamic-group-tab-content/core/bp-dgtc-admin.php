<?php
/**
 * Admin Metabox.
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
 * Helps with admin.
 */
class BP_Dynamic_Group_Tab_Content_Admin_Helper {

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
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ), 10, 2 );
		add_action( 'wp_ajax_bpdgtc_get_groups_list', array( $this, 'group_auto_suggest_handler' ) );
		// save post.
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_js' ) );

	}

	/**
	 * Register shortcode metabox.
	 *
	 * @param string $post_type post type.
	 * @param WP_Post $post post object.
	 */
	public function register_meta_boxes( $post_type, $post ) {

		$tab_post_type = bp_dynamic_group_tab_content_get_post_type();

		if ( $tab_post_type != $post_type ) {
			return;
		}

		// Group association Override.
		add_meta_box( 'bp-dynamic-group-tab-group-meta-box', __( 'Associated Group', 'buddypress-dynamic-group-tab-content' ), array(
			$this,
			'group_metabox',
		), $tab_post_type, 'normal', 'high' );

		// shortcode info.
		add_meta_box( 'bp-dynamic-group-tab-content-shortcode', __( 'Tab Info', 'buddypress-dynamic-group-tab-content' ), array(
			$this,
			'display_tab_info',
		), $tab_post_type, 'advanced', 'high' );

	}

	/**
	 * Save all data as post meta
	 *
	 * @param int $post_id post id.
	 *
	 * @return null
	 */
	public function save_post( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$post = get_post( $post_id );

		if ( $post->post_type !== bp_dynamic_group_tab_content_get_post_type() ) {
			return;
		}

		if ( empty( $_POST['_bpdgtc_group_association'] ) || ! wp_verify_nonce( $_POST['_bpdgtc_group_association'], 'bpdgtc_group_association' ) ) {
			return;
		}

		$group_id = absint( $_POST['_bpdgtc_associated_group_id'] );

		if ( empty( $group_id ) ) {
			delete_post_meta( $post_id, '_bpdgtc_associated_group_id' );
		} else {
			update_post_meta( $post_id, '_bpdgtc_associated_group_id', $group_id );
		}

	}

	/**
	 * Load Js
	 *
	 * @param string $hook hook name.
	 */
	public function load_js( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}
		$tab_post_type = bp_dynamic_group_tab_content_get_post_type();

		if ( $tab_post_type != get_post_type() ) {
			return;
		}

		wp_register_script( 'bpdgtc-admin-groups-helper', bp_dynamic_group_tab_content()->get_url() . 'assets/js/bpdgtc-admin-groups-helper.js', array(
			'jquery',
			'jquery-ui-autocomplete',
		) );
		wp_enqueue_script( 'bpdgtc-admin-groups-helper' );
	}

	/**
	 * Group metabox.
	 *
	 * @param WP_Post $post post object.
	 */
	public function group_metabox( $post ) {
		$selected_group_id = get_post_meta( $post->ID, '_bpdgtc_associated_group_id', true );
		?>
        <div id="bpdgtc-selected-groups-list">
            <input type="hidden" value="<?php echo esc_attr( $selected_group_id ); ?>" id="_bpdgtc_associated_group_id"
                   name="_bpdgtc_associated_group_id"/>
            <div class="bpdgtc-selected-group">
				<?php if ( $selected_group_id ) : $group = groups_get_group( $selected_group_id ); ?>
                    <div>
                        <a class="bpdgtc-remove-group" href="#">X</a>
                        <a href="<?php echo bp_get_group_permalink( $group ); ?>"><?php echo bp_get_group_name( $group ); ?> </a>
                    </div>

				<?php endif; ?>
            </div>
        </div>

        <h3><?php _e( 'Select Group', 'buddypress-member-types-pro' ); ?></h3>
        <p>
            <input type="text" placeholder="<?php _e( 'Type group name.', 'buddypress-member-types-pro' ); ?>"
                   id="bpdgtc-group-selector"/>
        </p>
        <p class='buddypress-member-types-pro-help'>
			<?php _e( 'The content will be visible in these groups only.', 'buddypress-member-types-pro' ); ?>
        </p>

		<?php wp_nonce_field( 'bpdgtc_group_association', '_bpdgtc_group_association' ); ?>
        <style type="text/css">
            .bpdgtc-remove-group {
                padding-right: 5px;
                color: red;
            }
        </style>
		<?php
	}

	/**
	 * Group response builder
	 */
	public function group_auto_suggest_handler() {

		$search_term = isset( $_POST['q'] ) ? $_POST['q'] : '';
		$excluded    = isset( $_POST['included'] ) ? wp_parse_id_list( $_POST['included'] ) : '';

		$groups = groups_get_groups( array(
			'search_terms' => $search_term,
			'exclude'      => $excluded,
			'show_hidden'  => true,
		) );

		$groups = $groups['groups'];

		$list = array();
		foreach ( $groups as $group ) {
			$list[] = array(
				'label' => $group->name,
				'url'   => bp_get_group_permalink( $group ),
				'id'    => $group->id,
			);
		}

		echo json_encode( $list );
		exit( 0 );

	}

	/**
	 * Display Metabox.
	 *
	 * @param WP_Post $post post object.
	 */
	public function display_tab_info( $post ) {
		$tax   = bp_dynamic_group_tab_content_get_taxonomy();
		$terms = get_the_terms( $post, $tax );

		if ( empty( $terms ) ) :?>
            <div class="bp-dynamic-group-tab-content-notice">
                <p><?php _e( 'Please make sure to assign a content type to the post.', 'buddypress-dynamic-group-tab-content' ); ?></p>
            </div>
		<?php else :
			$term = array_pop( $terms )
			?>
            <div class="bp-dynamic-group-tab-content-info">
                <p><?php _e( "This post will be visible on a group page when the following shortcode is used.", 'buddypress-dynamic-group-tab-content' ); ?></p>
            </div>
            <div class="bp-dynamic-group-tab-content-shortcode-text">
                <span>[bp_dynamic_group_tab_content type="<?php echo $term->slug ?>"]</span>
            </div>
		<?php endif; ?>
        <style type="text/css">

            .bp-dynamic-group-tab-content-notice {
                background: #925640;
                color: #fff;
                padding: 10px;
            }

            .bp-dynamic-group-tab-content-notice p {

            }

            .bp-dynamic-group-tab-content-shortcode-text {
                padding: 10px;
                background: #FFBC39;
            }

            .bp-dynamic-group-tab-content-shortcode-text span {
            }
        </style>
		<?php
	}

}

BP_Dynamic_Group_Tab_Content_Admin_Helper::boot();
