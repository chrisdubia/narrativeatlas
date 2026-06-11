<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * MEC integration admin tab
 *
 * @package BuddyBoss/Integration/MEC
 * @since 1.0.0
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Setup MEC integration admin tab class.
 *
 * @since 1.0.0
 */
class BP_MEC_Admin_Integration_Tab extends BP_Admin_Integration_tab {

	/**
	 * Current section.
	 *
	 * @var $current_section
	 */
	protected $current_section;

	/**
	 * Initialize
	 *
	 * @since 1.0.0
	 */
	public function initialize() {
		$this->tab_order = 50;
		$this->current_section = 'bp_mec-integration';
		$this->intro_template = $this->root_path . '/templates/admin/integration-tab-intro.php';
	}

	/**
	 * MEC Integration is active?
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_active() {
		return (bool) apply_filters('bp_mec_integration_is_active', true);
	}

	/**
	 * Load the settings html
	 *
	 * @since 1.0.0
	 */
	public function form_html() {
		// Check license is valid.
		if (function_exists('bbp_pro_is_license_valid') && !bbp_pro_is_license_valid()) {
			if (is_file($this->intro_template)) {
				require $this->intro_template;
			}
		} else {
			parent::form_html();
		}
	}

	/**
	 * Method to save the fields.
	 *
	 * @since 1.0.0
	 */
	public function settings_save() {
		$bp_mec_api_key = filter_input(INPUT_POST, 'bp-mec-api-key', FILTER_SANITIZE_STRING);
		$bp_mec_api_secret = filter_input(INPUT_POST, 'bp-mec-api-secret', FILTER_SANITIZE_STRING);
		$bp_mec_api_email = filter_input(INPUT_POST, 'bp-mec-api-email', FILTER_VALIDATE_EMAIL);

		if (!empty($bp_mec_api_secret) && !empty($bp_mec_api_key) && !empty($bp_mec_api_email)) {
			bp_mec_conference()->mec_api_key = $bp_mec_api_key;
			bp_mec_conference()->mec_api_secret = $bp_mec_api_secret;

			$user_info = bp_mec_conference()->get_user_info($bp_mec_api_email);

			if (200 !== $user_info['code']) {
				unset($_POST['bp-mec-api-email']); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				bp_delete_option('bp-mec-api-email');
				bp_delete_option('bp-mec-api-host');
				bp_delete_option('bp-mec-api-host-user');
				bp_delete_option('bp-mec-api-host-user-settings');
			} else {
				bp_update_option('bp-mec-api-host', $user_info['response']->id);
				bp_update_option('bp-mec-api-host-user', wp_json_encode($user_info['response']));

				// Get user settings of host user.
				$user_settings = bp_mec_conference()->get_user_settings($user_info['response']->id);

				// Save user settings into group meta.
				if (200 === $user_settings['code'] && !empty($user_settings['response'])) {
					bp_update_option('bp-mec-api-host-user-settings', wp_json_encode($user_settings['response']));

					if (isset($user_settings['response']->feature->webinar) && true === $user_settings['response']->feature->webinar) {
						bp_update_option('bp-mec-enable-webinar', true);
					} else {
						bp_delete_option('bp-mec-enable-webinar');
					}
				} else {
					bp_delete_option('bp-mec-api-host-user-settings');
					bp_delete_option('bp-mec-enable-webinar');
				}
			}
		}

		parent::settings_save();
	}

	/**
	 * Register setting fields for mec integration.
	 *
	 * @since 1.0.0
	 */
	public function register_fields() {

		$sections = $this->get_settings_sections();

		foreach ((array) $sections as $section_id => $section) {

			// Only add section and fields if section has fields.
			$fields = $this->get_settings_fields_for_section($section_id);

			if (empty($fields)) {
				continue;
			}

			$section_title = !empty($section['title']) ? $section['title'] : '';
			$section_callback = !empty($section['callback']) ? $section['callback'] : false;
			$tutorial_callback = !empty($section['tutorial_callback']) ? $section['tutorial_callback'] : false;

			// Add the section.
			$this->add_section($section_id, $section_title, $section_callback, $tutorial_callback);

			// Loop through fields for this section.
			foreach ((array) $fields as $field_id => $field) {

				$field['args'] = isset($field['args']) ? $field['args'] : array();

				if (!empty($field['callback']) && !empty($field['title'])) {
					$sanitize_callback = isset($field['sanitize_callback']) ? $field['sanitize_callback'] : array();
					$this->add_field($field_id, $field['title'], $field['callback'], $sanitize_callback, $field['args']);
				}
			}
		}
	}

	/**
	 * Get setting sections for mec integration.
	 *
	 * @since 1.0.0
	 *
	 * @return array $settings Settings sections for mec integration.
	 */
	public function get_settings_sections() {

		if (version_compare(BP_PLATFORM_VERSION, '1.5.7.3', '<=')) {
			$settings = array(
				'bp_mec_settings_section' => array(
					'page' => 'mec',
					'title' => __('MEC Settings', 'mec-buddyboss'),
				),
				'bp_mec_gutenberg_section' => array(
					'page' => 'mec',
					'title' => __('MEC Gutenberg Blocks', 'mec-buddyboss'),
				),
			);
		} else {
			$settings = array(
				'bp_mec_settings_section' => array(
					'page' => 'mec',
					'title' => __('MEC Settings', 'mec-buddyboss'),
					'tutorial_callback' => 'bp_mec_settings_tutorial',
				),
				'bp_mec_gutenberg_section' => array(
					'page' => 'mec',
					'title' => __('MEC Gutenberg Blocks', 'mec-buddyboss'),
					'tutorial_callback' => 'bp_mec_settings_tutorial',
				),
			);
		}

		return $settings;
	}

	/**
	 * Get setting fields for section in mec integration.
	 *
	 * @param string $section_id Section ID.
	 * @since 1.0.0
	 *
	 * @return array|false $fields setting fields for section in mec integration false otherwise.
	 */
	public function get_settings_fields_for_section($section_id = '') {

		// Bail if section is empty.
		if (empty($section_id)) {
			return false;
		}

		$fields = $this->get_settings_fields();
		$fields = isset($fields[$section_id]) ? $fields[$section_id] : false;

		return $fields;
	}

	/**
	 * Integration > MEC Conference > Enable.
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_enable_field() {
		?>
		<input name="bp-mec-enable" id="bp-mec-enable" type="checkbox" value="1" <?php checked(bp_mec_is_mec_enabled());?>/>
		<label for="bp-mec-enable">
			<?php esc_html_e('Allow MEC events on this site', 'mec-buddyboss');?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > Enable Groups
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_groups_enable_field() {
		?>
		<input name="bp-mec-enable-groups" id="bp-mec-enable-groups" type="checkbox" value="1" <?php checked(bp_mec_is_mec_groups_enabled());?>/>
		<label for="bp-mec-enable-groups">
			<?php esc_html_e('Allow MEC events in social groups', 'mec-buddyboss');?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > Enable Events menu
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_event_menu_enable_field() {
		?>
		<input name="bp-mec-enable-events-menu" id="bp-mec-enable-events-menu" type="checkbox" value="1" <?php checked(bp_mec_is_mec_events_menu_enabled());?>/>
		<label for="bp-mec-enable-events-menu">
			<?php esc_html_e('Enable user event menu', 'mec-buddyboss');?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > Enable Assign on Groups
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_groups_assign_enable_field() {
		?>
		<input name="bp-mec-enable-assign-groups" id="bp-mec-enable-assign-groups" type="checkbox" value="1" <?php checked(bp_mec_is_mec_assign_groups_enabled());?>/>
		<label for="bp-mec-enable-assign-groups">
			<?php esc_html_e('Allow MEC social groups to Assign Events', 'mec-buddyboss');?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > Enable Groups
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_filters_enable_field() {
		?>
		<input name="bp-mec-enable-filters" id="bp-mec-enable-filters" type="checkbox" value="1" <?php checked(bp_mec_is_mec_filters_enabled());?>/>
		<label for="bp-mec-enable-filters">
			<?php esc_html_e('Allow MEC events in social filters', 'mec-buddyboss');?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > Enable Groups
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_hide_btn_back_to_events_list_field() {
		?>
		<input name="bp-mec-hide-btn-back-to-events-list-in-fes-form" id="bp-mec-hide-btn-back-to-events-list-in-fes-form" type="checkbox" value="1" <?php checked(bp_mec_is_hide_btn_back_to_events_list());?>/>
		<label for="bp-mec-hide-btn-back-to-events-list-in-fes-form">
			<?php esc_html_e('Hide button "Back to events list" in Event Submission Form', 'mec-buddyboss');?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > Enable Groups
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_show_preview_in_activity() {
		?>
		<input name="bp-mec-show-event-preview-in-activity" id="bp-mec-show-event-preview-in-activity" type="checkbox" value="1" <?php checked(bp_mec_show_preview_in_activity_enabled());?>/>
		<label for="bp-mec-show-event-preview-in-activity">
			<?php esc_html_e('Show preview in activity', 'mec-buddyboss');?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > DateTime Format
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_datetime_format() {

		$datetime_format = bp_mec_get_settings( 'datetime_format', 'M d Y');
		$datetime_format = !empty( $datetime_format ) ? $datetime_format : 'M d Y';
		?>
		<input name="bb_mec_datetime_format" type="text" id="bb_mec_datetime_format"id="mec_settings_bb_datetime_format" value="<?php echo esc_attr( $datetime_format ); ?>" />
		<?php
	}

	/**
	 * Integration > MEC Conference > Hide Group Events
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_hide_group_events() {

		$hide_group_events = bp_mec_get_settings( 'hide_group_events', '');
		$checked = (bool) $hide_group_events;
		?>
		<label for="bb_mec_hide_group_events">
			<input name="bb_mec_hide_group_events" id="bb_mec_hide_group_events" value="1" type="checkbox" <?php checked( true, $checked ); ?> />
			<?php esc_html_e('Hide group events from skins', 'mec-buddyboss'); ?>
		</label>
		<?php
	}

	/**
	 * Integration > MEC Conference > Show Public Events for All
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_show_public_events_to_all() {

		$show_public_events_to_all = bp_mec_get_settings( 'show_public_events_to_all', '');
		$checked = (bool) $show_public_events_to_all;
		?>
		<label for="bb_mec_show_public_events_to_all">
			<input name="bb_mec_show_public_events_to_all" id="bb_mec_show_public_events_to_all" value="1" type="checkbox" <?php checked( true, $checked ); ?> />
			<?php esc_html_e('Show single event if event is in public group for all users ', 'mec-buddyboss'); ?>
		</label>
		<?php
	}



	/**
	 * Integration > MEC Conference > Event Submission Roles
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_event_submission_roles() {

		$selected_roles = bp_mec_get_settings( 'event_submission_roles', array());
		$selected_roles = !empty( $selected_roles ) && is_array( $selected_roles ) ? $selected_roles : array();

		global $wp_roles;
		$roles = $wp_roles->get_names();

		// Always include bbPress roles even if forums are disabled
		$bbp_roles = array(
			'bbp_keymaster'   => 'Keymaster',
			'bbp_moderator'   => 'Moderator',
			'bbp_participant' => 'Participant',
		);

		// Merge bbPress roles with existing roles, ensuring bbPress roles are always available
		foreach ( $bbp_roles as $bbp_role_key => $bbp_role_name ) {
			if ( ! isset( $roles[ $bbp_role_key ] ) ) {
				$roles[ $bbp_role_key ] = $bbp_role_name;
			}
		}

		wp_enqueue_script('mec-select2-script');
        wp_enqueue_style('mec-select2-style');
		?>
		<script>
			jQuery(document).ready(function($){
				$('#bb_mec_event_submission_roles').select2();
			});
		</script>
		<select id="bb_mec_event_submission_roles" class="select2" name="bb_mec_event_submission_roles[]" multiple="multiple">
			<?php foreach($roles as $role_key => $role_name):
				if($role_key=="bbp_spectator" || $role_key=="bbp_blocked") continue;

				if($role_key=="bbp_keymaster"){ ?>
				<option value="<?php echo esc_attr($role_key); ?>" <?php echo (is_array($selected_roles) and in_array(trim($role_key), $selected_roles)) ? 'selected="selected"' : ''; ?>>Organizer</option>
			<?php } else if($role_key=="bbp_participant") { ?>
				<option value="<?php echo esc_attr($role_key); ?>" <?php echo (is_array($selected_roles) and in_array(trim($role_key), $selected_roles)) ? 'selected="selected"' : ''; ?>>Members</option>
			<?php } else { ?>
				<option value="<?php echo esc_attr($role_key); ?>" <?php echo (is_array($selected_roles) and in_array(trim($role_key), $selected_roles)) ? 'selected="selected"' : ''; ?>><?php echo $role_name; ?></option>
			<?php } endforeach; ?>
		</select>
		<p><?php esc_html_e( 'Select roles that have access to submission events', 'mec-buddyboss' ); ?></p>
		<?php
	}

	/**
	 * Integration > MEC Conference > Remove Access Event Submission Roles
	 *
	 * @since 1.0.0
	 */
	public function bp_mec_settings_callback_remove_access_event_submission_users() {

		$selected_users = bp_mec_get_settings( 'remove_access_event_submission_for_users', array());
		$selected_users = !empty( $selected_users ) && is_array( $selected_users ) ? $selected_users : array();

		$users = get_users();

		wp_enqueue_script('mec-select2-script');
        wp_enqueue_style('mec-select2-style');
		?>
		<script>
			jQuery(document).ready(function($){
				$('#bb_mec_remove_access_event_submission_for_users').select2();
			});
		</script>
		<select id="bb_mec_remove_access_event_submission_for_users" class="mec-notification-dropdown-select2" name="bb_mec_remove_access_event_submission_for_users[]" multiple="multiple">
			<?php foreach( $users as $user ): ?>
				<option value="<?php echo esc_attr( $user->ID ); ?>" <?php echo (is_array($selected_users) and in_array(trim($user->ID), $selected_users)) ? 'selected="selected"' : ''; ?>><?php echo (isset($user->data->display_name) and trim($user->data->display_name)) ? trim($user->data->display_name) : '(' . trim($user->data->user_login) . ')'; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Register setting fields for mec integration.
	 *
	 * @since 1.0.0
	 *
	 * @return array $fields setting fields for mec integration.
	 */
	public function get_settings_fields() {

		$fields = array();

		$fields['bp_mec_settings_section'] = array(
			'bp-mec-enable' => array(
				'title' => __('Enable MEC', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_enable_field'),
				'sanitize_callback' => 'string',
				'args' => array(),
			),
		);

		if (bp_mec_is_mec_enabled()) {

			$fields['bp_mec_settings_section']['bb_mec_datetime_format'] = array(
				'title' => __('DateTime Format', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_datetime_format'),
				'sanitize_callback' => 'sanitize_text_field',
				'args' => array(),
			);

			$fields['bp_mec_settings_section']['bp-mec-enable-events-menu'] = array(
				'title' => __('Events Menu', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_event_menu_enable_field'),
				'sanitize_callback' => 'absint',
				'args' => array(),
			);

			if (bp_is_active('groups')) {
				$fields['bp_mec_settings_section']['bp-mec-enable-groups'] = array(
					'title' => __('Event Groups', 'mec-buddyboss'),
					'callback' => array($this, 'bp_mec_settings_callback_groups_enable_field'),
					'sanitize_callback' => 'absint',
					'args' => array(),
				);

				if (bp_mec_is_mec_groups_enabled()) {
					$fields['bp_mec_settings_section']['bp-mec-enable-assign-groups'] = array(
						'title' => __('Assign Event to Groups', 'mec-buddyboss'),
						'callback' => array($this, 'bp_mec_settings_callback_groups_assign_enable_field'),
						'sanitize_callback' => 'absint',
						'args' => array(),
					);
				}

				$fields['bp_mec_settings_section']['bb_mec_hide_group_events'] = array(
					'title' => __('Hide Events', 'mec-buddyboss'),
					'callback' => array($this, 'bp_mec_settings_callback_hide_group_events'),
					'sanitize_callback' => 'absint',
					'args' => array(),
				);

				$fields['bp_mec_settings_section']['bb_mec_show_public_events_to_all'] = array(
					'title' => __('Show Public Events', 'mec-buddyboss'),
					'callback' => array($this, 'bp_mec_settings_callback_show_public_events_to_all'),
					'sanitize_callback' => 'absint',
					'args' => array(),
				);
			}

			$fields['bp_mec_settings_section']['bp-mec-enable-filters'] = array(
				'title' => __('Enable Filters', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_filters_enable_field'),
				'sanitize_callback' => 'absint',
				'args' => array(),
			);

			$fields['bp_mec_settings_section']['bp-mec-hide-btn-back-to-events-list-in-fes-form'] = array(
				'title' => __('Hide Button', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_hide_btn_back_to_events_list_field'),
				'sanitize_callback' => 'absint',
				'args' => array(),
			);

			$fields['bp_mec_settings_section']['bp-mec-show-event-preview-in-activity'] = array(
				'title' => __('Show Preview', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_show_preview_in_activity'),
				'sanitize_callback' => 'absint',
				'args' => array(),
			);

			$fields['bp_mec_settings_section']['bb_mec_event_submission_roles'] = array(
				'title' => __('Event submission roles', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_event_submission_roles'),
				'sanitize_callback' => 'wp_slash',
				'args' => array(),
			);

			$fields['bp_mec_settings_section']['bb_mec_remove_access_event_submission_for_users'] = array(
				'title' => __('Event submission exclude users', 'mec-buddyboss'),
				'callback' => array($this, 'bp_mec_settings_callback_remove_access_event_submission_users'),
				'sanitize_callback' => '',
				'args' => array(),
			);
		}

		return $fields;
	}

	/**
	 * Settings saved.
	 */
	public function settings_saved() {
		$this->db_install_mec_meetings();
		parent::settings_saved();
	}

	/**
	 * Install database tables for the Groups mec meetings.
	 *
	 * @since 1.0.0
	 */
	public function db_install_mec_meetings() {

		// check mec enabled.
		if (!bp_mec_is_mec_enabled()) {
			return;
		}
	}
}
