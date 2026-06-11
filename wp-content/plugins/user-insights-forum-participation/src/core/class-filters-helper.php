<?php
/**
 * class Filter helpers.
 *
 * @package    User_Insights_Forum_Participation
 * @subpackage Core
 * @copyright  Copyright (c) 2024, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace User_Insights_Forum_Participation\Core;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Filters Helper class
 */
class Filters_Helper {

	/**
	 * Sets up the bootstrapper.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Hooks to actions and filters.
	 */
	private function setup() {
		add_filter( 'usin_field_types', array( $this, 'register_field_types' ) );
		add_filter( 'usin_fields', array( $this, 'register_fields' ) );
		add_filter( 'usin_db_map', array( $this, 'filter_db_map' ) );
		add_filter( 'usin_custom_query_filter_bp_group_forum_participation', array( $this, 'filter_query' ), 10, 2 );
	}

	/**
	 * Registers new field type.
	 *
	 * @param array $field_types Field types.
	 *
	 * @return array
	 */
	public function register_field_types( $field_types ) {

		if ( ! usin_modules()->is_module_active( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
			return $field_types;
		}

		if ( ! isset( $field_types['bbp_forum_participation'] ) ) {
			$field_types['bbp_forum_participation'] = array(
				'operators' => array(
					array( 'key' => 'participated', 'val' => __( 'Participated In' ) ),
					array( 'key' => 'not_participated', 'val' => __( 'Not Participated In' ) ),
				),
				'type'      => 'option',
			);
		}

		return $field_types;
	}

	/**
	 * Registers field
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public function register_fields( $fields ) {

		if ( ! usin_modules()->is_module_active( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
			return $fields;
		}

		if ( is_array( $fields ) ) {
			$fields[] = array(
				'name'        => __( 'Group Members' ),
				'id'          => 'bp_group_forum_participation',
				'hideOnTable' => true,
				'show'        => false,
				'order'       => false,
				'filter'      => array(
					'type'          => 'bbp_forum_participation',
					'options'       => \USIN_BuddyPress::get_groups(),
					'disallow_null' => false,
				),
			);
		}

		return $fields;
	}

	/**
	 * Filters db mapping
	 *
	 * @param array $db_map Db map.
	 *
	 * @return array
	 */
	public function filter_db_map( $db_map ) {

		if ( ! isset( $db_map['bp_group_forum_participation'] ) ) {
			$db_map['bp_group_forum_participation'] = array(
				'db_ref'        => 'bbp_forum_participation',
				'db_table'      => '',
				'custom_select' => true,
				'set_alias'     => true,
			);
		}

		return $db_map;
	}

	/**
	 * Filters custom query
	 *
	 * @param array  $custom_query_data Custom query.
	 * @param object $filter Filter object.
	 *
	 * @return array
	 */
	public function filter_query( $custom_query_data, $filter ) {
		global $wpdb;

		$group    = isset( $filter->condition ) ? groups_get_group( $filter->condition ) : null;
		$operator = isset( $filter->operator ) && in_array(
			$filter->operator,
			array( 'participated', 'not_participated' ),
			true
		) ? $filter->operator : 'participated';

		if ( empty( $group->id ) || ! bp_group_is_forum_enabled( $group ) ) {
			// What should return here?.
			return $custom_query_data;
		}

		$forum_ids = bbp_get_group_forum_ids( $group->id );
		$forum_id  = current( $forum_ids ); // Assuming only one forum can be attached to group as discussed with Brajesh sir.

		if ( empty( $forum_id ) ) {
			// What should return here?.
			return $custom_query_data;
		}

		$group_members_table = buddypress()->groups->table_name_members;
		$topic_post_type     = bbp_get_topic_post_type();

		$topic_query = $wpdb->prepare( "  SELECT DISTINCT `post_author` FROM {$wpdb->posts} WHERE `post_type` = %s AND `post_status` = 'publish' AND `post_parent` = %d ", esc_html( $topic_post_type ), absint( $forum_id ) );
		$reply_query = $wpdb->prepare( " UNION  SELECT DISTINCT `post_author` FROM {$wpdb->posts} WHERE `post_type` = %s AND `post_status` = 'publish' AND `post_parent` IN (SELECT DISTINCT `ID` FROM {$wpdb->posts} WHERE `post_type` = %s AND `post_parent` = %d) ", bbp_get_reply_post_type(), esc_html( $topic_post_type ), absint( $forum_id ) );

		$group_forum_member_query = $topic_query . $reply_query;
		$group_query              = '';
		if ( 'participated' === $operator ) {
			$group_query = $wpdb->prepare( "SELECT `user_id` FROM {$group_members_table} WHERE `group_id` = %d AND `is_confirmed` = 1 AND `user_id` IN ($group_forum_member_query)", absint( $group->id ) );
		} elseif ( 'not_participated' === $operator ) {
			$group_query = $wpdb->prepare( "SELECT `user_id` FROM {$group_members_table} WHERE `group_id` = %d AND `is_confirmed` = 1 AND `user_id` NOT IN ($group_forum_member_query)", absint( $group->id ) );
		}

		$custom_query_data['where'] = " AND wp_users.ID IN (" . $custom_query_data['where'] . $group_query . ')';

		return $custom_query_data;
	}
}
