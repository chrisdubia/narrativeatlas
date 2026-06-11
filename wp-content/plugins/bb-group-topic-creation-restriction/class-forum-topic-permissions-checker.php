<?php
/**
 * Class for Forum topic permission checker
 *
 * @package    Forum_Topic_Permissions_Checker
 * @copyright  Copyright (c) 2024, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma
 * @since      1.0.0
 */

namespace Narrativeatlas\BB_Prestrictions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Forum topic permission checker.
 */
class Forum_Topic_Permissions_Checker {

	/**
	 * Hooks to action
	 */
	public function setup() {
		add_filter( 'bbp_current_user_can_access_create_topic_form', array( $this, 'check_access' ) );
		add_action( 'bbp_new_topic_pre_extras', array( $this, 'pre_extras' ) );
	}

	/**
	 * Checks user permissions.
	 *
	 * @param bool $can Weather can create or not.
	 *
	 * @return bool
	 */
	public function check_access( $can ) {

		if ( ! bp_is_group() || ! bp_group_is_forum_enabled() ) {
			return $can;
		}

		return $this->has_allowed_role( get_current_user_id(), bp_get_current_group_id() );
	}

	/**
	 * Before inserting topic
	 *
	 * @param int $forum_id Forum id.
	 */
	public function pre_extras( $forum_id ) {

		if ( ! bbp_is_forum_group_forum( $forum_id ) || ! bp_is_group() ) {
			return;
		}

		$group           = groups_get_current_group();
		$group_forum_ids = bbp_get_group_forum_ids( $group->id );

		if ( in_array( $forum_id, (array) $group_forum_ids ) && ! $this->has_allowed_role( get_current_user_id(), $group->id ) ) {
			bbp_add_error( 'bbp_topic_permissions', __( '<strong>ERROR</strong>: You do not have allowed to create new discussions. Please contact group administrator.', 'bb-group-topic-creation-restriction' ) );
		}
	}

	/**
	 * Checks if user has allowed role.
	 *
	 * @param int $user_id  User id.
	 * @param int $group_id Group id.
	 *
	 * @return bool|int
	 */
	private function has_allowed_role( $user_id, $group_id ) {
		$allowed_role = groups_get_groupmeta( $group_id, '_forum_topic_allowed_creator_role', true );
		$allowed_role = '' === $allowed_role ? 'members' : $allowed_role;

		$has = false;
		if ( 'members' === $allowed_role ) {
			$has = groups_is_user_member( $user_id, $group_id );
		} elseif ( 'mods' === $allowed_role ) {
			$has = groups_is_user_mod( $user_id, $group_id ) || groups_is_user_admin( $user_id, $group_id );
		} elseif ( 'admins' === $allowed_role ) {
			$has = groups_is_user_admin( $user_id, $group_id );
		}

		return $has;
	}
}
