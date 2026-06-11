<?php

/**
 * Safely start a PHP session only when appropriate for front-end requests.
 * Avoids starting sessions during REST API, Cron, and non-AJAX admin requests.
 */
function mec_bb_safe_session_start($force = false)
{
    // Do not start sessions for REST API or CRON requests
    if (!$force) {
        if ((defined('REST_REQUEST') && REST_REQUEST) || (function_exists('wp_doing_cron') && wp_doing_cron())) {
            return false;
        }
    }

    // If headers already sent, do not attempt to start a session
    if (headers_sent()) {
        return false;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        // phpcs:ignore WordPress.PHP.SessionFunctions.SessionFunctions
        session_start();
        return true;
    }

    return false;
}

function bp_mec_is_mec_enabled($default = 0)
{
	return (bool) bp_get_option('bp-mec-enable', $default);
}

function bp_mec_is_mec_groups_enabled($default = 0, $is_checked_component = false)
{

	return (bool) bp_get_option('bp-mec-enable-groups', $default);
}

function bp_mec_is_mec_events_menu_enabled($default = 0, $is_checked_component = false)
{

	return (bool) bp_get_option('bp-mec-enable-events-menu', $default);
}

function bp_mec_is_mec_filters_enabled($default = 0)
{
	return (bool) bp_get_option('bp-mec-enable-filters', $default);
}

function bp_mec_show_preview_in_activity_enabled($default = 0)
{
	return (bool) bp_get_option('bp-mec-show-event-preview-in-activity', $default);
}

function bp_mec_is_hide_btn_back_to_events_list($default = 0)
{
	return (bool) bp_get_option('bp-mec-hide-btn-back-to-events-list-in-fes-form', $default);
}

function bp_mec_echo_fes_form_styles_and_scripts($default = 0)
{

	$hide = bp_mec_is_hide_btn_back_to_events_list();
	if (!$hide) {
		return;
	}

?>
	<style type="text/css">
		.mec-fes-form-top-actions {
			display: none !important;
		}
	</style>
<?php

}

function bp_mec_is_mec_assign_groups_enabled($default = 0)
{
	return (bool) bp_get_option('bp-mec-enable-assign-groups', $default);
}

function bp_mec_get_settings($key, $default = '')
{

	return bp_get_option("bb_mec_$key", $default);
}

function mec_bp_get_all_groups_formated($event_id)
{
	$metas_assigned = BP_MEC_Group_Helper::get_event_groups($event_id);
	$ret = '';
	$user_id = get_current_user_id();
	if ($metas_assigned != null) {
		foreach ($metas_assigned as $k => $group) {

			$ret .= '<span class="mec-bp-groups-assign-list">';
			$ret .= $group['name'];

			if (bp_mec_is_user_can_event_change($group['id']) == true) {
				$ret .= '<a href="#" onclick="return mec_bp_Events_Assign(\'' . $event_id . '\',\'' . $group['id'] . '\',\'del\')">x</a>';
			}

			$ret .= '</span>';
		}
	}

	return $ret;
}

/**
 * Output the 'checked' value, if needed, for a given status on the group admin screen
 *
 * @since 1.0.0
 *
 * @param string      $setting The setting you want to check against ('members',
 *                             'mods', or 'admins').
 * @param object|bool $group   Optional. Group object. Default: current group in loop.
 */
function bp_mec_group_show_manager_setting($setting, $group = false)
{
	$group_id = isset($group->id) ? $group->id : $group;

	$status = bp_mec_group_get_manager($group_id);

	if ($setting === $status) {
		echo ' checked="checked"';
	}
}

/**
 * Check group mec is setup or not.
 *
 * @since 1.0.0
 * @param int $group_id Group ID.
 *
 * @return bool Returns true if mec is setup.
 */
function bp_mec_is_group_setup($group_id)
{

	if (!bp_is_active('groups')) {
		return false;
	}

	$user_id = bp_loggedin_user_id();
	if (!$user_id) {
		return false;
	}

	if (groups_is_user_mod($user_id, $group_id)) {
		return true;
	}

	if (groups_is_user_creator($user_id, $group_id)) {
		return true;
	}

	$group_mec = groups_get_groupmeta($group_id, 'bp-group-mec', true);

	if (!$group_mec) {
		return false;
	}

	return true;
}

/**
 * Get the mec manager of a group.
 *
 * This function can be used either in or out of the loop.
 *
 * @since 1.0.0
 *
 * @param int|bool $group_id Optional. The ID of the group whose status you want to
 *                           check. Default: the displayed group, or the current group
 *                           in the loop.
 * @return bool|string Returns false when no group can be found. Otherwise
 *                     returns the group mec manager, from among 'members',
 *                     'mods', and 'admins'.
 */
function bp_mec_group_get_manager($group_id = false)
{
	global $groups_template;

	if (!$group_id) {
		$bp = buddypress();

		if (isset($bp->groups->current_group->id)) {
			// Default to the current group first.
			$group_id = $bp->groups->current_group->id;
		} elseif (isset($groups_template->group->id)) {
			// Then see if we're in the loop.
			$group_id = $groups_template->group->id;
		} else {
			return false;
		}
	}

	$manager = groups_get_groupmeta($group_id, 'bp-group-mec-manager', true);

	// Backward compatibility. When '$manager' is not set, fall back to a default value.
	if (!$manager) {
		$manager = apply_filters('bp_mec_group_manager_fallback', 'admins');
	}

	/**
	 * Filters the album status of a group.
	 *
	 * @since 1.0.0
	 *
	 * @param string $manager Membership level needed to manage albums.
	 * @param int    $group_id      ID of the group whose manager is being checked.
	 */
	return apply_filters('bp_mec_group_get_manager', $manager, $group_id);
}

function bp_mec_is_user_can_event_change($group_id = null)
{
	$user_id = get_current_user_id();
	$manager = bp_mec_group_get_manager($group_id);
	if (bp_mec_groups_can_user_manage_mec($user_id, $group_id, $manager) === true) {
		return true;
	}

	return false;
}

/**
 * Check whether a user is allowed to manage mec meetings in a given group.
 *
 * @since 1.0.0
 *
 * @param int $user_id ID of the user.
 * @param int $group_id ID of the group.
 * @return bool true if the user is allowed, otherwise false.
 */
function bp_mec_groups_can_user_manage_mec($user_id, $group_id, $manager = null)
{
	$is_allowed = false;

	if (!is_user_logged_in()) {
		return false;
	}

	// Site admins always have access.
	if (bp_current_user_can('bp_moderate')) {
		return true;
	}

	if (!groups_is_user_member($user_id, $group_id)) {
		return false;
	}

	if ($manager === null && strlen($manager) <= 0) {
		$manager = bp_mec_group_get_manager($group_id);
	}

	$is_admin = groups_is_user_admin($user_id, $group_id);
	$is_mod = groups_is_user_mod($user_id, $group_id);

	if ('members' === $manager) {
		$is_allowed = true;
	} elseif ('mods' === $manager && ($is_mod || $is_admin)) {
		$is_allowed = true;
	} elseif ('admins' === $manager && $is_admin) {
		$is_allowed = true;
	}

	return apply_filters('bp_mec_groups_can_user_manage_event', $is_allowed);
}

function bp_mec_settings_tutorial()
{
	return '';
}

function bp_mec_is_active_top_plugins()
{
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	// Check if BuddyBoss is active
	$buddyboss_active = is_plugin_active('buddyboss-platform/bp-loader.php');

	// Check if MEC is active (multiple possible paths)
	$mec_active = is_plugin_active('modern-events-calendar/mec.php') ||
		is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php') ||
		is_plugin_active('modern-events-calendar/modern-events-calendar.php') ||
		class_exists('MEC') ||
		defined('MEC_VERSION');


	if ($buddyboss_active && $mec_active) {
		return true;
	}

	return false;
}

/**
 * Return Groups
 *
 * @param array $args
 *
 * @return array
 */
function bp_mec_get_groups($args = array())
{
	$defaults = array(
		'per_page' => -1,
		'show_hidden' => true,
	);

	if (!current_user_can('administrator')) {

		$defaults['user_id'] = bp_loggedin_user_id();
	}

	$args = wp_parse_args($args, $defaults);

	$groups = groups_get_groups($args);
	return isset($groups['groups']) && count($groups['groups']) > 0 ? $groups['groups'] : null;
}

function mec_bb_get_edit_or_create_event_link($event_id = 0, $check_capability = true)
{

	if ($event_id != 0 && !mec_bb_current_user_can_edit_event($event_id)) {

		return '';
	}

	$group_id = null;
	if (bp_is_groups_component() && function_exists('bp_get_current_group_id')) {

		$group_id = bp_get_current_group_id();
	}

	if ($group_id != null) {

		$url = trailingslashit(bp_get_group_permalink(groups_get_group($group_id)) . 'mec-group/create-event/');
	} else {

		$url = bp_loggedin_user_domain() . 'mec-main/create-event';
	}


	if ($event_id) {

		$url .= '?post_id=' . $event_id;
	}

	return esc_url($url);
}

function mec_bb_current_user_can_edit_event($event_id)
{

	return (new \MEC_feature_fes())->current_user_can_upsert_event($event_id);
}

// Change username during MEC registration - only when BuddyBoss is active
function mec_bp_modify_username($username, $email)
{
	// PRIORITY 1: Try to get name from form data for auto mode
	if (isset($_POST['book']['tickets']) && is_array($_POST['book']['tickets'])) {
		foreach ($_POST['book']['tickets'] as $ticket) {
			if (isset($ticket['name']) && !empty(trim($ticket['name']))) {
				// Clean the name: replace spaces with dashes and remove special characters
				$clean_name = trim($ticket['name']);
				$clean_name = preg_replace('/\s+/', '-', $clean_name);
				$clean_name = preg_replace('/[^a-zA-Z0-9\-]/', '', $clean_name);
				$clean_name = preg_replace('/-+/', '-', $clean_name);
				$clean_name = trim($clean_name, '-');

				$name_username = sanitize_user($clean_name);

				if ($name_username !== $email && !empty($name_username)) {
					return $name_username;
				}
			}
		}
	}

	// PRIORITY 2: Try to get name from fields data
	if (isset($_POST['book']['fields']['name']) && !empty(trim($_POST['book']['fields']['name']))) {
		// Clean the name: replace spaces with dashes and remove special characters
		$clean_name = trim($_POST['book']['fields']['name']);
		$clean_name = preg_replace('/\s+/', '-', $clean_name);
		$clean_name = preg_replace('/[^a-zA-Z0-9\-]/', '', $clean_name);
		$clean_name = preg_replace('/-+/', '-', $clean_name);
		$clean_name = trim($clean_name, '-');

		$name_username = sanitize_user($clean_name);

		if ($name_username !== $email && !empty($name_username)) {
			return $name_username;
		}
	}

	// PRIORITY 3: Manual username from form
	if (isset($_POST['book']['username']) && !empty(trim($_POST['book']['username']))) {
		$form_username = sanitize_user($_POST['book']['username']);

		if ($form_username !== $email && !empty($form_username)) {
			return $form_username;
		}
	}

	return $username;
}

// Add the filter with higher priority and always (not just when BuddyBoss is active)
add_filter('mec_user_register_username', 'mec_bp_modify_username', 5, 2);

// Global variable to store form username
global $mec_bp_form_username;
$mec_bp_form_username = null;

// Hook into MEC booking form processing to capture username early
function mec_bp_capture_form_username()
{
	global $mec_bp_form_username;

    // Skip entirely for REST API, CRON, and non-POST requests
    if ((defined('REST_REQUEST') && REST_REQUEST) || (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')) {
        return;
    }

    // Start session safely (non-REST/non-CRON). Track if we started it to close later.
    $session_started_here = mec_bb_safe_session_start(false);

	if (isset($_POST['book']['username']) && !empty(trim($_POST['book']['username']))) {
		$mec_bp_form_username = sanitize_user($_POST['book']['username']);

		// Also try to get email to store in session
		$email = '';
		if (isset($_POST['book']['tickets'][0]['email'])) {
			$email = sanitize_email($_POST['book']['tickets'][0]['email']);
		} elseif (isset($_POST['tickets'][0]['email'])) {
			$email = sanitize_email($_POST['tickets'][0]['email']);
		}

		// Store in session if we have email
		if (!empty($email)) {
			$_SESSION['mec_form_username_' . $email] = $mec_bp_form_username;
		}
	}

    // Close the session immediately to avoid blocking HTTP requests
    if ($session_started_here && session_status() === PHP_SESSION_ACTIVE) {
        // phpcs:ignore WordPress.PHP.SessionFunctions.SessionFunctions
        session_write_close();
    }
}

// Ensure any open PHP session is closed immediately for REST API requests
function mec_bb_close_session_for_rest() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // phpcs:ignore WordPress.PHP.SessionFunctions.SessionFunctions
        session_write_close();
    }
}
add_action('rest_api_init', 'mec_bb_close_session_for_rest', 0);

// Hook into early form processing
add_action('wp_loaded', 'mec_bp_capture_form_username', 1);
add_action('init', 'mec_bp_capture_form_username', 1);

// Alternative username filter using global variable and session
function mec_bp_modify_username_alternative($username, $email)
{
	global $mec_bp_form_username;

    // Start session safely to read any stored username; close right after.
    $session_started_here = mec_bb_safe_session_start(false);

    $result_username = $username;

    // First try global variable
    if ($mec_bp_form_username && $mec_bp_form_username !== $email) {
        $result_username = $mec_bp_form_username;
    } else {
        // Then try session
        if (isset($_SESSION['mec_form_username_' . $email]) && !empty($_SESSION['mec_form_username_' . $email])) {
            $session_username = sanitize_user($_SESSION['mec_form_username_' . $email]);
            if ($session_username !== $email) {
                $result_username = $session_username;
            }
        }
    }

    if ($session_started_here && session_status() === PHP_SESSION_ACTIVE) {
        // phpcs:ignore WordPress.PHP.SessionFunctions.SessionFunctions
        session_write_close();
    }

    return $result_username;
}

// Add alternative filter with even higher priority
add_filter('mec_user_register_username', 'mec_bp_modify_username_alternative', 1, 2);

// Ensure BuddyBoss users are properly integrated after MEC registration
function mec_bp_after_user_registration($user_id, $attendee, $args)
{

	// Only run if BuddyBoss is active
	if (!function_exists('bp_is_active') || !bp_is_active()) {
		return;
	}

	// Get user data
	$user_data = get_userdata($user_id);
	if (!$user_data) {
		return;
	}


	// Ensure user has proper BuddyBoss profile setup
	$username = $user_data->user_login;

	// Set BuddyBoss specific user meta
	update_user_meta($user_id, 'nickname', $username);
	update_user_meta($user_id, 'bp_latest_update', '');

	// Set last activity to current time to ensure user appears in member directory
	if (function_exists('bp_update_user_last_activity')) {
		$current_time = bp_core_current_time();
		bp_update_user_last_activity($user_id, $current_time);
	}

	// Ensure user is properly activated in BuddyBoss
	if (function_exists('bp_core_activate_signup')) {
		// This function properly activates the user in BuddyBoss
		$activation_result = bp_core_activate_signup($username);
	}

	// Set default member type if configured
	if (function_exists('bp_member_type_default_on_registration')) {
		$default_member_type = bp_member_type_default_on_registration();
		if (!empty($default_member_type) && function_exists('bp_set_member_type')) {
			bp_set_member_type($user_id, $default_member_type);
		}
	}

	// Clear member count cache to refresh directory
	wp_cache_delete('bp_total_member_count', 'bp');
	wp_cache_delete('bp_core_members_count', 'bp');
	wp_cache_delete('bp_member_count', 'bp');

	// Trigger BuddyBoss user registration actions
	do_action('bp_core_activated_user', $user_id, '', false);
	do_action('bp_core_signup_user', array('user_id' => $user_id));
}

// Hook into MEC user registration if available
if (function_exists('bp_is_active') && bp_is_active()) {
	add_action('mec_user_registered', 'mec_bp_after_user_registration', 10, 3);
}

// Ensure booking process continues after user registration
function mec_bp_ensure_booking_continues($user_id, $attendee, $args)
{
	// This hook ensures that the booking process continues normally
	// after user registration, even when BuddyBoss integration is active
	return $user_id;
}

// Hook to ensure booking flow continues
if (function_exists('bp_is_active') && bp_is_active()) {
	add_filter('mec_user_registration_result', 'mec_bp_ensure_booking_continues', 10, 3);
}

// Additional hook to ensure users appear in member directory
function mec_bp_force_member_directory_update($user_id)
{
	if (!function_exists('bp_is_active') || !bp_is_active()) {
		return;
	}

	// Force update member directory
	if (function_exists('bp_update_user_last_activity')) {
		bp_update_user_last_activity($user_id, bp_core_current_time());
	}

	// Clear all relevant caches
	wp_cache_flush();

	// Force refresh member count
	if (function_exists('bp_core_get_total_member_count')) {
		delete_transient('bp_total_member_count');
		bp_core_get_total_member_count(true); // Force refresh
	}
}

// Hook this to user registration
add_action('user_register', 'mec_bp_force_member_directory_update', 20);


// AJAX handler to capture username during MEC booking process
function mec_capture_username_ajax()
{
    // AJAX context: force session start for this handler only, then close it.
    $session_started_here = mec_bb_safe_session_start(true);

	// Parse form data to extract username and email
	$raw_post_data = file_get_contents('php://input');
	if (!empty($raw_post_data)) {
		parse_str($raw_post_data, $parsed_data);

		$username = '';
		$email = '';

		// Extract username
		if (isset($parsed_data['book']['username']) && !empty($parsed_data['book']['username'])) {
			$username = sanitize_user($parsed_data['book']['username']);
		}

		// Extract email from tickets
		if (isset($parsed_data['book']['tickets'][0]['email']) && !empty($parsed_data['book']['tickets'][0]['email'])) {
			$email = sanitize_email($parsed_data['book']['tickets'][0]['email']);
		}

		// Store in session if we have both
		if (!empty($username) && !empty($email) && $username !== $email) {
			$_SESSION['mec_form_username_' . $email] = $username;
		}
	}

    if ($session_started_here && session_status() === PHP_SESSION_ACTIVE) {
        // phpcs:ignore WordPress.PHP.SessionFunctions.SessionFunctions
        session_write_close();
    }
}

// Hook into MEC AJAX booking form
add_action('wp_ajax_mec_book_form', 'mec_capture_username_ajax', 1);
add_action('wp_ajax_nopriv_mec_book_form', 'mec_capture_username_ajax', 1);
