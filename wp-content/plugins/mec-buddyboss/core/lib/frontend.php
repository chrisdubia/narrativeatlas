<?php
/**
 *
 */
class BP_MEC_Frontend {

	public function init() {

		add_filter('wp', array($this, 'checked_group_access'), 10, 1);

		add_action('wp_enqueue_scripts', array($this, 'enqueue_style'));

		if (bp_mec_is_mec_enabled() && is_user_logged_in()) {
			add_action('bp_setup_nav', array($this, 'setup_nav'));
		}

		if (bp_mec_is_mec_groups_enabled()) {
			add_action('bp_setup_nav', array($this, 'setup_nav_group'), 100);
			add_filter('bp_nouveau_customizer_group_nav_items', array( $this, 'customizer_group_nav_items' ),10,2);
		}
		// Load mec admin page.
		add_action('bp_screens', array($this, 'mec_admin_page'));

		add_action('mec_fes_metabox_details', array($this, 'mec_fes_form'), 10, 1);
		add_action('save_post_mec-events', array($this, 'on_add_events'), 10, 3);
		add_action('save_post_mec_calendars', array($this, 'on_add_shortcode'), 10, 3);
		add_filter('mec-single-event-meta-title', array($this, 'add_options'), 99, 2);
		add_action('mec_metabox_details', array($this, 'meta_box_bp_group'), 60);
		add_action('admin_enqueue_scripts', array($this, 'assets_add'), 10, 1);

		// BuddyBoss groups
		add_filter( 'mec_skin_query_args', array( $this, 'shortcode_filter_query_args' ), 10, 2 );
		if( is_admin() ){

			add_action( 'mec_shortcode_filters_tab_links', array( $this, 'shortcode_filters_tab_links' ) );
			add_action( 'mec_shortcode_filters_content', array( $this, 'shortcode_filters_content' ) );
		}

		add_action( 'pre_get_posts', array( $this, 'hide_group_events' ) );

		add_filter( 'mec_fes_form_current_user_can_submit_event', array( __CLASS__, 'filter_current_user_can_submit_event' ) );

		add_action( 'init', array( $this, 'add_events_to_user_menu' ), 99 );
	}

	public static function add_events_to_user_menu(){

		if( defined('THEME_HOOK_PREFIX') ){

			add_action( THEME_HOOK_PREFIX . 'after_bb_groups_menu', array( __CLASS__, 'add_menu_after_bb_groups_menu') );
		}
	}

	public static function add_menu_after_bb_groups_menu(){

		if( !is_user_logged_in() ){

			return;
		}

		if( !bp_mec_is_mec_events_menu_enabled() ){

			return;
		}

		$user_domain = bp_loggedin_user_domain();
		$events_link = trailingslashit( $user_domain . 'mec-main' );
		?>
		<li id="wp-admin-bar-my-account-events" class="menupop parent">
			<a class="ab-item" aria-haspopup="true" href="<?php echo esc_url( $events_link ); ?>">
				<i class="bb-icon-l bb-icon-calendar"></i>
				<span><?php esc_html_e( 'Events', 'mec-buddyboss' ); ?></span>
			</a>
		</li>
		<?php
	}

	private function access_deny( $args = array() ) {

			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			include MECBUDDYBOSSDIR . 'core' . DS . 'templates' . DS . 'event-404.php';
			exit();
	}

	public function checked_group_access($wp) {

		global $post;

		if (
			'mec-events' != get_post_type()
			||
			!is_singular()
			|| is_admin()
		) {
			return $wp;

		}

		if (!bp_mec_is_mec_enabled() || !bp_mec_is_mec_groups_enabled() || !function_exists('groups_get_user_groups')) {
			return $wp;
		}

		$event_groups = BP_MEC_Group_Helper::get_event_groups( $post->ID );
		if (!$event_groups) {
			return $wp;
		}

		$has_public_group = in_array( 'public', array_column( $event_groups, 'status' ) );
		$show_public_events_to_all = (bool)bp_mec_get_settings( 'show_public_events_to_all', '');
		if( $has_public_group && $show_public_events_to_all ) {

			return $wp;
		}

		$user_id = null;
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
		}

		if (!$user_id) {
			return $this->access_deny();
		}

		$user_groups = groups_get_user_groups($user_id);

		if (!isset($user_groups['groups']) || !isset($user_groups['total'])) {
			return $this->access_deny();
		}

		$is_access = false;

		$gids = array_values($user_groups['groups']);
		foreach ($event_groups as $eg) {

			if (in_array($eg['id'], $gids)) {
				$is_access = true;
			}
		}

		if ($is_access == false) {

			$args = array();

			$group_links_html = array();
			foreach( $event_groups as $e_group ) {

				if( !$e_group['is_visible'] ){

					continue;
				}

				$group_links_html[] = '<a class="mec-bp-group-link" href="' . $e_group['link'] . '">' . $e_group['name'] . '</a>';
			}

			if( !empty( $group_links_html ) ){


				$args['message_html'] = sprintf(
					__('You Cannot Access to Event. You must be a member of one of the following groups to view. %1s', 'mec-buddyboss'),
					'<div class="mec-bp-group-links">' . implode( ', ', $group_links_html ) . '</div>'
				);
			}

			return $this->access_deny( $args );
		}

		return $wp;
	}

	public function assets_add() {
		wp_enqueue_script('mec-bp-main-js', MECBUDDYBOSSDASSETS . 'js/backend.js', array(), '1.0');
	}

	public function add_options($tabs, $activated) {

		if (bp_mec_is_mec_groups_enabled(0) == false) {
			return $tabs;
		}

		if (bp_mec_is_mec_assign_groups_enabled(false) == false) {
			return $tabs;
		}

		$zoomTab = array(
			__('BuddyBoss Group', 'mec-buddyboss') => 'mec-bp',
		);
		$tabs = array_merge($tabs, $zoomTab);
		return $tabs;
	}

	public function meta_box_bp_group($post) {

		if (bp_mec_is_mec_groups_enabled(0) == false) {
			return;
		}

		if (bp_mec_is_mec_assign_groups_enabled(false) == false) {
			return;
		}

		if (!function_exists('groups_get_groups')) {
			return;
		}

		$selected = BP_MEC_Group_Helper::get_event_groups_ids($post->ID);

		?>
		<div class="mec-meta-box-fields mec-event-tab-content" id="mec-bp">
            <h4><?php echo __('BuddyBoss Group Selection', 'mec-buddyboss'); ?></h4>
            <div class="mec-form-row">

            <?php
			$groups = bp_mec_get_groups();
		if ($groups != null):
		?>
			<select id="mec_selected_group" class="mec-bp-group-dropdown-select2" name="mec[bp_selected_group][]" multiple="multiple">
				<?php
				foreach ($groups as $kg => $group) {
					if (bp_mec_is_user_can_event_change($group->id)) {
						$selected_inp = isset($selected[$group->id]) ? 'selected="selected"' : '';
						echo '<option value="' . $group->id . '" ' . $selected_inp . '>' . $group->name . '</option>';
					}
				}
			?>
			</select>
			<span class="mec-tooltip">
					<div class="box top">
						<h5 class="title">
							<?php echo __('Assign To Group', 'mec-buddyboss'); ?>
						</h5>
						<div class="content"><p>
							<?php esc_html_e('Select BuddyBoss group for access the event.','mec-buddyboss') ?>
							</p></div>
					</div>
					<i title="" class="dashicons-before dashicons-editor-help"></i>
				</span>
			<?php else: ?>
			<b>
			<?php _e('Group not found!', 'mec-buddyboss');?>
			</b>
			<?php endif;?>

            	<label class="mec-col-2" for="mec_selected_group"><?php esc_html_e('Select  Group', 'mec-buddyboss');?></label>
            </div>
        </div>


    	<?php
}

	public function mec_fes_form( $post ) {

		if (bp_mec_is_mec_groups_enabled(0) == false) {
			return;
		}

		if (bp_mec_is_mec_assign_groups_enabled(false) == false) {
			return;
		}

		if (!function_exists('groups_get_groups')) {
			return;
		}

		$selected = BP_MEC_Group_Helper::get_event_groups_ids($post->ID);

		$selected_group = BP_MEC_Group_Helper::get_event_groups_ids($post->ID);
		$current_group_id = bp_get_current_group_id();

		if (empty($selected_group) && !empty($current_group_id)) {
		    $selected_group[$current_group_id] = $current_group_id;
		}

		?>
		<div class="mec-meta-box-fields" id="mec-event-note">
		    <h4><?php _e('Select Group', 'mec-buddyboss'); ?></h4>
		    <div id="mec_meta_box_select_group">
		        <?php
		        $groups = bp_mec_get_groups();
		        if ($groups != null): ?>
		            <select id="mec_fes_selected_group" class="mec-bp-group-dropdown-select2" name="mec[bp_selected_group][]" multiple="multiple">
		                <?php
		                foreach ($groups as $kg => $group) {
		                    if (bp_mec_is_user_can_event_change($group->id)) {
		                        $selected_inp = isset($selected_group[$group->id]) ? 'selected="selected"' : '';
		                        echo '<option value="' . $group->id . '" ' . $selected_inp . '>' . $group->name . '</option>';
		                    }
		                }
		                ?>
		            </select>
		        <?php else: ?>
		            <b><?php _e('Group not found!', 'mec-buddyboss'); ?></b>
		        <?php endif; ?>
		    </div>
		</div>

		<?php
	}

	public function on_add_events($post_ID, $post, $update) {

		if (bp_mec_is_mec_groups_enabled(0) == false) {
			return;
		}

		$mec = isset($_POST['mec']) ? $_POST['mec'] : null;
		if (!$mec) {
			return;
		}

		if( !bp_mec_is_mec_assign_groups_enabled() ){
			return;
		}

		$groups = isset($mec['bp_selected_group']) ? $mec['bp_selected_group'] : array();
		BP_MEC_Group_Helper::update_event_groups_ids( $post_ID, $groups );
	}

	public function on_add_shortcode($post_ID, $post, $update) {

		if (bp_mec_is_mec_groups_enabled(0) == false) {
			return;
		}

		$mec = isset($_POST['mec']) ? $_POST['mec'] : null;
		if (!$mec) {
			return;
		}

		$group = isset($mec['bp_groups']) ? $mec['bp_groups'] : null;
		if (!$group) {

			update_post_meta( $post_ID, 'bp_groups', array() );
			return;
		}
	}

	public function edit_screen_save($group_id) {

		// Bail if not a POST action.
		if (!bp_is_post_request()) {
			return;
		}

		$nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);

		// Admin Nonce check.
		if (is_admin()) {
			check_admin_referer('groups_edit_save_mec', 'mec_group_admin_ui');

			// Theme-side Nonce check.
		} elseif (empty($nonce) || (!empty($nonce) && !wp_verify_nonce($nonce, 'groups_edit_save_mec'))) {
			return;
		}

		$manager = filter_input(INPUT_POST, 'bp-group-mec-manager', FILTER_SANITIZE_STRING);
		$manager = !empty($manager) ? $manager : bp_mec_group_get_manager($group_id);
		groups_update_groupmeta($group_id, 'bp-group-mec-manager', $manager);
		groups_update_groupmeta($group_id, 'bp-group-mec', 1);

		/**
		 * Add action that fire before user redirect
		 *
		 * @Since 1.0.0
		 *
		 * @param int $group_id Current group id
		 */
		do_action('bp_group_admin_after_edit_screen_save', $group_id);

	}

	/**
	 * Setup navigation for group mec tabs.
	 *
	 * @since 1.0.0
	 */
	public function setup_nav_group() {
		// return if no group.
		if (!bp_is_group()) {
			return;
		}

		$current_group = groups_get_current_group();
		$group_link = bp_get_group_permalink($current_group);
		$sub_nav = array();

		// if current group has mec enable then return.
		if (is_user_logged_in()) {
			$sub_nav[] = array(
				'name' => __('Events', 'mec-buddyboss'),
				'slug' => 'mec-group',
				'parent_url' => $group_link,
				'parent_slug' => $current_group->slug,
				'screen_function' => array($this, 'mec_group_main_page'),
				'item_css_id' => 'mec',
				'position' => 100,
				'user_has_access' => $current_group->user_has_access,
				'no_access_url' => $group_link,
			);

			$sub_nav[] = array(
				'name' => __('Create Event', 'mec-buddyboss'),
				'slug' => 'create-event',
				'parent_url' => $group_link,
				'parent_slug' => 'mec-group', // $current_group->slug . '_mec',
				'screen_function' => array($this, 'mec_add_content'),
				'user_has_access' => $current_group->user_has_access,
				'no_access_url' => $group_link,

			);

		}

		$is_admin = groups_is_user_admin(bp_loggedin_user_id(), $current_group->id);

		// If the user is a group admin, then show the group admin nav item.
		if (bp_is_item_admin() && $is_admin) {
			$admin_link = trailingslashit($group_link . 'admin');

			$sub_nav[] = array(
				'name' => __('Events', 'mec-buddyboss'),
				'slug' => 'mec',
				'position' => 100,
				'parent_url' => $admin_link,
				'parent_slug' => $current_group->slug . '_manage',
				'screen_function' => 'groups_screen_group_admin',
				'user_has_access' => bp_is_item_admin(),
				'show_in_admin_bar' => true,
			);
		}

		foreach ($sub_nav as $nav) {
			bp_core_new_subnav_item($nav, 'groups');
		}

		// save edit screen options.
		if (bp_is_groups_component() && bp_is_current_action('admin') && bp_is_action_variable('mec', 0)) {
			$this->edit_screen_save($current_group->id);

			// Load mec admin page.
			add_action('bp_screens', array($this, 'mec_admin_page'));
		}
	}

	/**
	 * Customer group nav items.
	 *
	 * @since 1.1.0
	 */
	public function customizer_group_nav_items($nav_items, $group){

		$nav_items[ 'mec' ] = array(
			'name' => __( 'Events', 'mec-buddyboss'),
			'slug' => 'mec-group',
			'position' => 10,
			'parent_slug' => $nav_items['root']['slug'],
		);

		return $nav_items;
	}

	public function mec_group_main_page() {

		add_action('bp_template_content', array($this, 'mec_group_page_content'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'groups/single/home'));
	}

	public function mec_group_page_content() {

		$current_group = groups_get_current_group();

		$this->mec_created_base_content('group', $current_group->id);
	}

	public function mec_group_main_created_content() {
		add_action('bp_template_content', array($this, 'mec_created_base_content'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
	}
	public function mec_group_main_booked_content() {
		add_action('bp_template_content', array($this, 'mec_booked_base_content'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
	}

	/**
	 * MEC admin page callback
	 *
	 * @since 1.0.0
	 */
	public function mec_admin_page() {

		if (!bp_is_groups_component()) {
			return false;
		}

		if ('mec' !== bp_get_group_current_admin_tab()) {
			return false;
		}

		if (!bp_is_item_admin() && !bp_current_user_can('bp_moderate')) {
			return false;
		}
		add_action('groups_custom_edit_steps', array($this, 'edit_screen'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'groups/single/home'));
	}

	/**
	 * Show mec option form when editing a group
	 *
	 * @param object|bool $group (the group to edit if in Group Admin UI).
	 *
	 * @since 1.0.0
	 * @uses is_admin() To check if we're in the Group Admin UI
	 */
	public function edit_screen($group = false) {

		$group_id = empty($group->id) ? bp_get_new_group_id() : $group->id;

		if (empty($group->id)) {
			$group_id = bp_get_new_group_id();
		}

		if (empty($group_id)) {
			$group_id = bp_get_group_id();
		}

		if (empty($group_id)) {
			$group_id = $group->id;
		}

		// Should box be checked already?
		$checked = bp_mec_is_group_setup($group_id);

		?>

		<div class="bb-group-mec-settings-container">

			<h4 class="bb-section-title"><?php esc_html_e('Group MEC Settings', 'mec-buddyboss');?></h4>



			<div id="bp-group-mec-settings-additional" class="group-settings-selections ">

				<hr class="bb-sep-line" />
				<h4 class="bb-section-title"><?php esc_html_e('Group Permissions', 'mec-buddyboss');?></h4>

				<fieldset class="radio group-media">
					<legend class="screen-reader-text"><?php esc_html_e('Group Permissions', 'mec-buddyboss');?></legend>
					<p class="group-setting-label" tabindex="0"><?php esc_html_e('Which members of this group are allowed to create, edit and delete MEC Events?', 'mec-buddyboss');?></p>

					<div class="bp-radio-wrap">
						<input type="radio" name="bp-group-mec-manager" id="group-mec-manager-members" class="bs-styled-radio" value="members"<?php bp_mec_group_show_manager_setting('members', $group);?> />
						<label for="group-mec-manager-members"><?php esc_html_e('All group members', 'mec-buddyboss');?></label>
					</div>

					<div class="bp-radio-wrap">
						<input type="radio" name="bp-group-mec-manager" id="group-mec-manager-mods" class="bs-styled-radio" value="mods"<?php bp_mec_group_show_manager_setting('mods', $group);?> />
						<label for="group-mec-manager-mods"><?php esc_html_e('Organizers and Moderators only', 'mec-buddyboss');?></label>
					</div>

					<div class="bp-radio-wrap">
						<input type="radio" name="bp-group-mec-manager" id="group-mec-manager-admins" class="bs-styled-radio" value="admins"<?php bp_mec_group_show_manager_setting('admins', $group);?> />
						<label for="group-mec-manager-admins"><?php esc_html_e('Organizers only', 'mec-buddyboss');?></label>
					</div>
				</fieldset>

				<hr class="bb-sep-line" />
			</div>



			<div class="bp-mec-group-button-wrap">
				<button type="submit" class="bb-save-settings">
					<?php esc_html_e('Save Settings', 'mec-buddyboss');?>
				</button>
			</div>
			<?php wp_nonce_field('groups_edit_save_mec');?>
		</div>
		<?php
}

	public function enqueue_style() {
		$reset = '?r=' . MECBUDDYBOSSVERSION;

		wp_enqueue_style('mec-bp-main', MECBUDDYBOSSURL . '/assets/css/bp-mec.css' . $reset, false);

		wp_register_script('mec-bp-js-script', MECBUDDYBOSSURL . '/assets/js/frontend.js' . $reset, array('jquery'));

		wp_localize_script('mec-bp-js-script', 'MEC_BUDDYBOSS_VARS', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'is_current_user_profile' => get_current_user_id() && get_current_user_id() === bp_displayed_user_id() ? 'yes' : 'no',
		));

		wp_enqueue_script('mec-bp-js-script');

		wp_register_script('mec-bp-js-blockui', MECBUDDYBOSSURL . '/assets/js/jquery.blockUI.js' . $reset, array('jquery'));
		wp_enqueue_script('mec-bp-js-blockui');
	}

	function load_languages() {
		$locale = apply_filters('plugin_locale', get_locale(), MECBUDDYBOSSTEXTDOMAIN);

		// WordPress language directory /wp-content/languages/mec-en_US.mo
		$language_filepath = MECBUDDYBOSSDIR . 'languages' . DIRECTORY_SEPARATOR . MECBUDDYBOSSTEXTDOMAIN .'-' . $locale . '.mo';
		// If language file exists on WordPress language directory use it
		if (file_exists($language_filepath)) {
			load_textdomain(MECBUDDYBOSSTEXTDOMAIN, $language_filepath);
		} else {
			load_plugin_textdomain(MECBUDDYBOSSTEXTDOMAIN, false, dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);
		}
	}
	// Set up Cutsom BP navigation
	function setup_nav() {

		self::load_languages();
		// $can = self::current_user_can_submit_event();
		// if( !$can ){

		// 	return;
		// }
		global $bp;

		bp_core_new_nav_item(array(
			'name' => __('Events', 'mec-buddyboss'),
			'slug' => 'mec-main',
			'position' => 99,
			'screen_function' => array($this, 'mec_main_template'),
			'default_subnav_slug' => 'mec-main-created',
		));

		bp_core_new_subnav_item(
			array(
				'name' => __( 'Event List', 'mec-buddyboss' ),
				'slug' => 'mec-main-created',
				'show_for_displayed_user' => false,
				'parent_url' => bp_loggedin_user_domain() . '/mec-main/',
				'parent_slug' => 'mec-main',
				'position' => 10,
				'screen_function' => array($this, 'mec_created_content'),
			)
		);

		if( get_current_user_id() === bp_displayed_user_id() ){

			bp_core_new_subnav_item(array(
				'name' => __('Booked Events', 'mec-buddyboss'),
				'slug' => 'mec-main-booked',
				'parent_url' => bp_loggedin_user_domain() . '/mec-main/',
				'parent_slug' => 'mec-main',
				'position' => 10,
				'screen_function' => array($this, 'mec_booked_content'))
			);

			bp_core_new_subnav_item(array(
				'name' => __('Add Event', 'mec-buddyboss'),
				'slug' => 'create-event',
				'parent_url' => bp_loggedin_user_domain() . '/mec-main/',
				'parent_slug' => 'mec-main',
				'position' => 60,
				'item_css_id' => 'mec_add_event',
				'screen_function' => array($this, 'mec_add_content'),
			));
		}
	}

	public function mec_created_content() {
		add_action('bp_template_content', array($this, 'mec_created_base_content'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
	}
	public function mec_booked_content() {
		add_action('bp_template_content', array($this, 'mec_booked_base_content'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
	}

	public function mec_add_content() {

		add_action('bp_template_content', array($this, 'mec_add_event_content'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
	}

	public function mec_add_event_content($area = 'profile', $group_id = null) {

		$this->mec_created_base_content($area, $group_id, 'events_created_booked', true);
	}

	public function mec_created_base_content($area = 'profile', $group_id = null, $template = 'events_created_booked', $create_mode = false) {

		global $wp;
		if ($create_mode == false && strpos($wp->request, 'create-event') !== false) {
			$create_mode = true;
		}

		$main = new \MEC_main();
		$render = $main->getRender();

		$all = get_posts(array(
			'post_type' => 'mec-events',
			'numberposts' => -1,
			'post_status' => 'publish',
			'author' => bp_loggedin_user_id(),
			'meta_query' => array(
				'filter_bp_group' => array(),
			),
		));

		$data = new \stdClass;
		$data->created = true;
		$data->booked = false;
		$data->events = [];
		$data->create_mode = $create_mode;

		foreach ($all as $k => $post) {
			$data->events[] = $render->data($post->ID);
		}

		include MECBUDDYBOSSDIR . DS . 'core' . DS . 'templates' . DS . $template . '.php';
	}

	public function mec_booked_base_content($area = 'profile', $group_id = null) {

		$main = new \MEC_main();
		$render = $main->getRender();

		$all = get_posts(array(
			'post_type' => 'mec-books',
			'numberposts' => -1,
			'post_status' => 'publish',
			'author' => bp_loggedin_user_id(),
			'meta_query' => array(
				'filter_bp_group' => array(),
			),
		));

		$data = new \stdClass;
		$data->created = false;
		$data->booked = true;
		$data->events = [];
		$data->create_mode = false;

		foreach ($all as $k => $post) {
			$event_id = get_post_meta($post->ID, 'mec_event_id', true);

			$data->events[] = $render->data($event_id);
		}

		include MECBUDDYBOSSDIR . DS . 'core' . DS . 'templates' . DS . 'events_created_booked.php';
	}

	public function mec_main_template() {
		add_action('bp_template_content', array($this, 'mec_main_base_content'));
		bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
	}

	public function mec_main_base_content() {}

	public function load_menus($classes, $component) {

		return $classes;
	}

	public static function current_user_can_submit_event( $user_id = 0, $group_id = 0 ){

		if( !$user_id ){
			$user_id = get_current_user_id();
		}

		$user_data = get_userdata( $user_id );
		if( !is_a( $user_data, 'WP_User' ) ){
			return false;
		}

		// If viewing another user's profile, check if current user is connected with them
		if (bp_displayed_user_id() && bp_displayed_user_id() !== bp_loggedin_user_id()) {
			$friendship_status = friends_check_friendship_status(bp_loggedin_user_id(), bp_displayed_user_id());
			if ($friendship_status !== 'is_friend') {
				return false;
			}
		}

		$user_ids = bp_mec_get_settings( 'remove_access_event_submission_for_users', array());

		if(in_array( $user_id, (array)$user_ids, false )){
			return false;
		}

		$user_roles = is_array( $user_data->roles ) ? $user_data->roles : array();
		$roles = bp_mec_get_settings( 'event_submission_roles', false);
		if( false === $roles ){
			return true;
		}

		$roles = is_array( $roles ) ? $roles : array();

		// If no group context, check only role permissions
		if (!$group_id) {
			$intersect = array_intersect( $user_roles, $roles );
			return count( $intersect ) > 0;
		}

		// In group context, check both role and group permissions
		$manager = bp_mec_group_get_manager($group_id);
		$is_admin = groups_is_user_admin($user_id, $group_id);
		$is_mod = groups_is_user_mod($user_id, $group_id);

		// Check if user has the required role first
		$has_required_role = false;
		if ($is_admin && count(array_intersect($roles, array("bbp_keymaster"))) > 0) {
			$has_required_role = true;
		} elseif ($is_mod && count(array_intersect($roles, array("bbp_moderator"))) > 0) {
			$has_required_role = true;
		} elseif (count(array_intersect($roles, array("bbp_participant"))) > 0) {
			$has_required_role = true;
		}

		// Then check group manager permissions
		if ($has_required_role) {
			if ($manager == "members") {
				return true;
			} elseif ($manager == "mods" && ($is_mod || $is_admin)) {
				return true;
			} elseif ($manager == "admins" && $is_admin) {
				return true;
			}
		}

		return false;
	}

	public static function filter_current_user_can_submit_event( $can = true, $settings = array() ){

		if( count($settings)==0 ){
			$main = new \MEC_main();
			$settings = $main->get_settings();
		}

		$guest_status = (isset($settings['fes_guest_status']) and $settings['fes_guest_status'] == '1');
		if( !is_user_logged_in() && $guest_status ){

			return $can;
		}

		if(isset($_GET['group'])){
			return self::current_user_can_submit_event(0,$_GET['group']);
		}

		return self::current_user_can_submit_event();
	}

	public function shortcode_filters_tab_links( $post ){

		echo '<a class="mec-create-shortcode-tabs-link" data-href="mec_select_event_groups" href="#">' . esc_html__('BuddyBoss Groups' ,'mec-buddyboss') . '</a>';
	}

	public function shortcode_filters_content( $post ){

		$selected_groups = get_post_meta($post->ID, 'bp_groups', true);
		$selected_groups = is_array( $selected_groups ) ? $selected_groups : array();
		$include_by_group = get_post_meta($post->ID, 'bp_include_by_group', true);
		$exclude_by_group = get_post_meta($post->ID, 'bp_exclude_by_group', true);

		?>
		<div class="mec-form-row mec-create-shortcode-tab-content" id="mec_select_event_groups">
			<h4><?php esc_html_e('BuddyBoss Groups', 'mec-buddyboss'); ?></h4>
			<div class="mec-form-row mec-switcher">
				<div class="mec-col-4">
					<label for="mec_bp_include_by_group"><?php esc_html_e('Include By Group', 'mec-buddyboss'); ?></label>
				</div>
				<div class="mec-col-4">
					<input type="hidden" name="mec[bp_include_by_group]" value="0" />
					<input type="checkbox" name="mec[bp_include_by_group]" class="mec-checkbox-toggle" id="mec_bp_include_by_group" value="1" <?php if($include_by_group == '' or $include_by_group == 1) echo 'checked="checked"'; ?> />
					<label for="mec_bp_include_by_group"></label>
				</div>
			</div>
			<div class="mec-form-row mec-switcher">
				<div class="mec-col-4">
					<label for="mec_bp_exclude_by_group"><?php esc_html_e('Exclude By Group', 'mec-buddyboss'); ?></label>
				</div>
				<div class="mec-col-4">
					<input type="hidden" name="mec[bp_exclude_by_group]" value="0" />
					<input type="checkbox" name="mec[bp_exclude_by_group]" class="mec-checkbox-toggle" id="mec_bp_exclude_by_group" value="1" <?php if($exclude_by_group == 1) echo 'checked="checked"'; ?> />
					<label for="mec_bp_exclude_by_group"></label>
				</div>
			</div>

			<div class="mec-form-row mec-switcher">
				<div class="mec-col-4">
					<label for="mec_groups"><?php _e('Groups', 'mec-buddyboss'); ?></label>
				</div>
				<div class="mec-col-4">
					<?php
						$groups = bp_mec_get_groups();
						if ($groups != null):
						?>
						<select id="mec_selected_group" class="mec-bp-group-dropdown-select2" name="mec[bp_groups][]" multiple="multiple">
							<?php
							foreach ($groups as $kg => $group) {
								if (bp_mec_is_user_can_event_change($group->id)) {
									$selected_inp = in_array( $group->id, $selected_groups, false ) ? 'selected="selected"' : '';
									echo '<option value="' . $group->id . '" ' . $selected_inp . '>' . $group->name . '</option>';
								}
							}
						?>
						</select>
					<?php else: ?>
						<b>
						<?php esc_html_e('Group not found!', 'mec-buddyboss');?>
						</b>
					<?php endif;?>
				</div>
			</div>
		</div>
		<?php
	}

	public function shortcode_filter_query_args( $args, $skin_class ){

		$shortcode_id = $skin_class->id;

		$groups = get_post_meta($shortcode_id , 'bp_groups', true);
		$groups = is_array( $groups ) ? $groups : array();

		if( !empty( $groups ) ){

			$include_by_group = (bool)get_post_meta($shortcode_id, 'bp_include_by_group', true);
			$exclude_by_group = (bool)get_post_meta($shortcode_id, 'bp_exclude_by_group', true);

			if( $include_by_group ){
				$compare = 'EXISTS';
			}elseif( $exclude_by_group ){
				$compare = 'NOT EXISTS';
			}

			if( !empty( $compare ) ){

				$meta_query['relation'] = 'OR';
				foreach( $groups as $group_id ){

					$key = "mec_bp_group_{$group_id}";
					$meta_query[ $key ] = array(
						'key' => $key,
						'value' => $group_id,
						'compare' => $compare,
					);
				}

				$args['meta_query']['filter_bp_group'] = $meta_query;
			}
		}

		return $args;
	}

	public function hide_group_events( $query ){

		if( is_admin() && !wp_doing_ajax() ){
			return;
		}

		$post_type = $query->get( 'post_type' );

		if( 'mec-events' !== $post_type ){
			return;
		}

		$hide_group_events = (bool) bp_mec_get_settings( 'hide_group_events', '');

		if( !$hide_group_events ){
			return;
		}

		$meta_query = $query->get( 'meta_query' );

		if( is_array( $meta_query ) && !isset( $meta_query['filter_bp_group'] ) ){

			$meta_query['filter_bp_group'] = array(
				'key' => 'mec_bp_group_',
				'compare_key' => 'LIKE',
				'compare' => 'NOT EXISTS',
			);

			$query->set( 'meta_query', $meta_query );
		}
	}
}

?>
