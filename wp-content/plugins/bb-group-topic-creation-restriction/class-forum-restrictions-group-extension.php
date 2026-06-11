<?php
/**
 * Class BB Group Extension
 *
 * @package    BB_Group_Topic_Creation_Restriction
 * @copyright  Copyright (c) 2024, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma
 * @since      1.0.0
 */

namespace Narrativeatlas\BB_Prestrictions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register new group extension.
 */
class Forum_Restrictions_Group_Extension extends \BP_Group_Extension {

	/**
	 * The constructor.
	 */
	public function __construct() {

		$args = array(
			'slug'            => 'forum-restrictions',
			'name'            => __( 'Forum Restrictions', 'bb-group-topic-creation-restriction' ),
			'enable_nav_item' => false,
			'nav_item_name'   => __( 'Forum Restrictions', 'bb-group-topic-creation-restriction' ),
			'screens'         => array(
				'edit' => array(
					'slug'                 => 'forum-restrictions',
					'name'                 => __( 'Forum Restrictions', 'bb-group-topic-creation-restriction' ),
					'position'             => 55,
					'screen_callback'      => array( $this, 'edit_screen' ),
					'screen_save_callback' => array( $this, 'edit_screen_save' ),
				),
			),
			'access'          => 'admin',
		);

		parent::init( $args );
	}

	/**
	 * Renders edit screen content.
	 *
	 * @param int $group_id Group id.
	 */
	public function edit_screen( $group_id = null ) {
		$allowed_role = groups_get_groupmeta( $group_id, '_forum_topic_allowed_creator_role', true );
		$allowed_role = '' == $allowed_role ? 'members' : $allowed_role;

		?>
		<h4 class="bb-section-title"><?php esc_html_e( 'Forum Restrictions', 'bb-group-topic-creation-restriction' ); ?></h4>
			<fieldset>

			<p class="group-setting-label"><?php esc_html_e( 'Which members of this group are allowed to create forum topic?', 'bb-group-topic-creation-restriction' ); ?></p>

			<div class="bp-radio-wrap">
				<input type="radio" name="group-forum-topic-allowed-creator-role" id="group-forum-topic-allowed-creator-role-members" class="bs-styled-radio" value="members"<?php checked( $allowed_role, 'members' ); ?> />
				<label for="group-forum-topic-allowed-creator-role-members"><?php esc_html_e( 'All group members', 'bb-group-topic-creation-restriction' ); ?></label>
			</div>

			<div class="bp-radio-wrap">
				<input type="radio" name="group-forum-topic-allowed-creator-role" id="group-forum-topic-allowed-creator-role-mods" class="bs-styled-radio" value="mods"<?php checked( $allowed_role, 'mods' ); ?> />
				<label for="group-forum-topic-allowed-creator-role-mods"><?php esc_html_e( 'Organizers and Moderators only', 'bb-group-topic-creation-restriction' ); ?></label>
			</div>

			<div class="bp-radio-wrap">
				<input type="radio" name="group-forum-topic-allowed-creator-role" id="group-forum-topic-allowed-creator-role-admins" class="bs-styled-radio" value="admins"<?php checked( $allowed_role, 'admins' ) ?> />
				<label for="group-forum-topic-allowed-creator-role-admins"><?php esc_html_e( 'Organizers only', 'bb-group-topic-creation-restriction' ); ?></label>
			</div>
		</fieldset>
		<?php
		wp_nonce_field( "group-forum-restrictions-form-{$group_id}" );
	}

	/**
	 * Saves settings
	 *
	 * @param int $group_id Group id.
	 */
	public function edit_screen_save( $group_id = null ) {

		// Bail if not a POST action or not item admin?.
		if ( ! bbp_is_post_request() || ! bp_is_item_admin() ) {
			return;
		}

		if ( ! bbp_verify_nonce_request( "group-forum-restrictions-form-{$group_id}" ) ) {
			bbp_add_error( 'bbp_edit_group_forum_restrictions_screen_save', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bb-group-topic-creation-restriction' ) );
			return;
		}

		$allowed_role = isset( $_POST['group-forum-topic-allowed-creator-role'] ) ? sanitize_text_field( wp_unslash( $_POST['group-forum-topic-allowed-creator-role'] ) ) : '';

		if ( in_array( $allowed_role, array( 'members', 'mods', 'admins' ), true ) ) {
			groups_update_groupmeta( $group_id, '_forum_topic_allowed_creator_role', $allowed_role );

			bp_core_add_message( __( 'Group Forum restrictions were successfully updated.', 'bb-group-topic-creation-restriction' ) );

			bp_core_redirect( trailingslashit( bp_get_group_permalink( buddypress()->groups->current_group ) . '/admin/' . $this->slug ) );
		}
	}
}
