<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * MEC integration admin tab
 *
 * @package BuddyBossPro/Integration/MEC
 * @since 1.0.0
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Setup MEC integration admin tab class.
 *
 * @since 1.0.0
 */
class BP_MEC_Group_Feeds {

	public function init() {
		// add_action('save_post_mec-events', array($this, 'on_add_events'), 10, 3);
		add_action('mec_assign_event_to_group', array($this, 'on_assign_event_to_group'), 10, 3);
		add_action('bp_register_activity_actions', array($this, 'register_activity_actions'));
	}

	/**
	 * Register our activity actions with BuddyBoss
	 *
	 * @since 1.0.0
	 * @uses bp_activity_set_action()
	 */
	public function register_activity_actions() {

		$bp = buddypress();
		if(!$bp){
			return;
		}

		if(!isset($bp->groups)){
			return;
		}

		if(!isset($bp->groups->id)){
			return;
		}


		// Group activity stream items.
		bp_activity_set_action(
			buddypress()->groups->id,
			'mec_event_create',
			esc_html__('New MEC Event', 'mec-buddyboss'),
			array(
				$this,
				'event_activity_action_callback',
			)
		);

		bp_activity_set_action(
			buddypress()->groups->id,
			'mec_added_event_to_group',
			esc_html__('Added event to group', 'mec-buddyboss'),
			array(
				$this,
				'assign_event_to_group_activity_action_callback',
			)
		);

		bp_activity_set_action(
			buddypress()->groups->id,
			'mec_removed_event_to_group',
			esc_html__('Removed event to group', 'mec-buddyboss'),
			array(
				$this,
				'assign_event_to_group_activity_action_callback',
			)
		);
	}

	/**
	 * MEC Event activity action.
	 *
	 * @param string $action Action activity.
	 * @param object $activity Activity object.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function event_activity_action_callback($action, $activity) {

		if (buddypress()->groups->id === $activity->component && !bp_mec_is_group_setup($activity->item_id)) {
			return $action;
		}

		$user_id = $activity->user_id;
		$group_id = $activity->item_id;
		$event_id = $activity->secondary_item_id;

		// // User link.
		$user_link = bp_core_get_userlink($user_id);
		$post = get_post($event_id);
		if (!$post) {
			return $action;
		}

		// // Event.
		$event_permalink = get_permalink($event_id);
		$event_title = $post->post_title;
		$event_link = '<a href="' . $event_permalink . '">' . $event_title . '</a>';

		$group = groups_get_group($group_id);
		$group_link = bp_get_group_link($group);

		$activity_action = sprintf(
			esc_html__('%1$s scheduled a MEC Event %2$s in the group %3$s', 'mec-buddyboss'),
			$user_link,
			$event_link,
			$group_link
		);

		return $activity_action;
	}

	public function on_add_events($post_ID, $post, $update) {

		if (!bp_is_group()) {
			return;
		}

		if (bp_mec_is_mec_groups_enabled(0) == false) {
			return;
		}

		if(empty($post_ID)){
			return;
		}

		$group_id = bp_get_current_group_id();
		if ($group_id == null || $group_id <= 0) {
			error_log("The event:{$post_ID} is not group level added!");
			return;
		}

		BP_MEC_Group_Helper::update_event_group_id( $post->ID, $group_id );

		wp_publish_post( $post_ID );

		// Check activity component active or not.
		if (!bp_is_active('activity')) {
			return;
		}

		$event_activity = get_post_meta($post_ID, 'mec_notification_activity_id', true);
		if ($event_activity != null) {
			error_log('Last Added Activity:' . $event_activity);
			return;
		}

		$user_id = get_current_user_id();

		// Get meeting group.
		$group = groups_get_group($group_id);

		// Check group exists.
		if (empty($group->id)) {
			return;
		}

		$type = 'mec_event_create';

		/* translators: %1$s - user link, %2$s - group link. */
		$action = sprintf(__('%1$s scheduled a MEC Event in the group %2$s', 'mec-buddyboss'), bp_core_get_userlink($user_id), '<a href="' . bp_get_group_permalink($group) . '">' . esc_attr($group->name) . '</a>');

		$activity_id = groups_record_activity(
			array(
				'user_id' => $user_id,
				'action' => $action,
				'content' => '',
				'type' => $type,
				'item_id' => $group_id,
				'secondary_item_id' => $post_ID,
			)
		);

		if ($activity_id) {

			// save activity id in meeting.
			if (!empty($event_activity)) {

				update_post_meta($post_ID, 'mec_notification_activity_id', $activity_id);

				// setup activity meta for notification activity.
				bp_activity_update_meta($activity_id, 'mec_notification_activity_id', true);
			} else {

				update_post_meta($post_ID, 'mec_notification_activity_id', $activity_id);
			}

			// update activity meta.
			bp_activity_update_meta($activity_id, 'bp_event_id', $post_ID);

			groups_update_groupmeta($group_id, 'last_activity', bp_core_current_time());
		}

	}

	/**
	 * MEC Event activity action.
	 *
	 * @param string $action Action activity.
	 * @param object $activity Activity object.
	 *
	 * @return string
	 */
	public function assign_event_to_group_activity_action_callback($action, $activity) {

		if (buddypress()->groups->id === $activity->component && !bp_mec_is_group_setup($activity->item_id)) {
			return $action;
		}

		return $action;
	}

	public function on_assign_event_to_group($event_id, $group_id, $assign_action) {

		$is_admin = is_admin();
		if (!bp_is_group() && !$is_admin) {
			return;
		}

		if (bp_mec_is_mec_groups_enabled(0) == false) {
			return;
		}

		if(empty($event_id)){
			return;
		}

		// Check activity component active or not.
		if (!bp_is_active('activity')) {
			return;
		}

		// $event_activity = get_post_meta($event_id, 'mec_notification_activity_id', true);
		// if ($event_activity != null) {
		// 	error_log('Last Added Activity:' . $event_activity);
		// 	return;
		// }

		$user_id = get_current_user_id();

		// Get meeting group.
		$group = groups_get_group($group_id);

		// Check group exists.
		if (empty($group->id)) {
			return;
		}

		if ($group_id == null || $group_id <= 0) {
			error_log("The event:{$event_id} is not group level added!");
			return;
		}

		$post = get_post($event_id);
		if (!$post) {
			return;
		}

		// // Event.
		$event_permalink = get_permalink($event_id);
		$event_title = $post->post_title;
		$event_link = '<a href="' . $event_permalink . '">' . $event_title . '</a>';

		switch( $assign_action ){

			case 'add':

				$type = 'mec_added_event_to_group';
				/* translators: %1$s - user link, %2$s - group link. */
				$action = sprintf(
					__('%1$s added event "%2$s" to the group %3$s', 'mec-buddyboss'),
					bp_core_get_userlink($user_id),
					$event_link,
					'<a href="' . bp_get_group_permalink($group) . '">' . esc_attr($group->name) . '</a>'
				);

				break;
			case 'delete':

				$type = 'mec_removed_event_to_group';
				/* translators: %1$s - user link, %2$s - group link. */
				$action = sprintf(
					__('%1$s removed event "%2$s" from the group %3$s', 'mec-buddyboss'),
					bp_core_get_userlink($user_id),
					$event_link,
					'<a href="' . bp_get_group_permalink($group) . '">' . esc_attr($group->name) . '</a>'
				);

				break;
		}

		$activity_id = groups_record_activity(
			array(
				'user_id' => $user_id,
				'action' => $action,
				'content' => '',
				'type' => $type,
				'item_id' => $group_id,
				'secondary_item_id' => $event_id,
			)
		);

		if ($activity_id) {

			// save activity id in meeting.
			if (!empty($event_activity)) {

				update_post_meta($event_id, 'mec_notification_activity_id', $activity_id);

				// setup activity meta for notification activity.
				bp_activity_update_meta($activity_id, 'mec_notification_activity_id', true);
			} else {

				update_post_meta($event_id, 'mec_notification_activity_id', $activity_id);
			}

			// update activity meta.
			bp_activity_update_meta($activity_id, 'bp_event_id', $event_id);

			groups_update_groupmeta($group_id, 'last_activity', bp_core_current_time());
		}

	}

}
