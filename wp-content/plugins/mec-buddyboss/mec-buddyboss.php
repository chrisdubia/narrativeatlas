<?php

/**
 *	Plugin Name: BuddyBoss Integration
 *	Plugin URI: http://webnus.net/modern-events-calendar
 *	Description: An integration between MEC and BuddyBoss.
 *	Author: Webnus
 *	Version: 2.6.0
 *  Text Domain: mec-buddyboss
 *  Domain Path: /languages
 *	Author URI: http://webnus.net
 *
 *  @package         Mec_Buddyboss
 **/

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

define('MEC_BUDDYBOSS_FILE', __FILE__);
define('MECBUDDYBOSSVERSION', '2.6.0');
define('MECBUDDYBOSSDIR', plugin_dir_path(__FILE__));
define('MECBUDDYBOSSURL', plugin_dir_url(__FILE__));
define('MECBUDDYBOSSDASSETS', MECBUDDYBOSSURL . '/assets/');
define('MECBUDDYBOSSNAME', 'BuddyBoss Integration');
define('MECBUDDYBOSSSLUG', 'mec-buddyboss');
define('MECBUDDYBOSSOPTIONS', 'mec_buddyboss_integration_options');
define('MECBUDDYBOSSTEXTDOMAIN', 'mec-buddyboss');
define('MECBUDDYBOSSABSPATH', dirname(__FILE__));

include MECBUDDYBOSSDIR . DS . 'mec-bp-functions.php';
include MECBUDDYBOSSDIR . DS . 'core' . DS . 'lib' . DS . 'feeds.php';

if (bp_mec_is_active_top_plugins() == true) {
	add_action('bp_setup_integrations', 'mec_bp_add_integration');
	function mec_bp_add_integration()
	{
		require_once MECBUDDYBOSSDIR . DS . 'core' . DS . 'lib' . DS . '/integration.php';
		buddypress()->integrations['mec-buddyboss'] = new BP_MEC_Integration();
	}

	function mec_bp_load()
	{

		if (!bp_mec_is_mec_enabled()) {

			return;
		}

		if (defined('DOING_AJAX') && DOING_AJAX) {
			require_once MECBUDDYBOSSDIR . DS . 'core' . DS . 'lib' . DS . '/ajax.php';
			$ajax = new BP_MEC_Ajax();
			$ajax->init();
		}

		require_once MECBUDDYBOSSDIR . DS . 'core' . DS . 'lib' . DS . '/frontend.php';
		require_once MECBUDDYBOSSDIR . DS . 'core' . DS . 'lib' . DS . '/group-helper.php';
		$ajax = new BP_MEC_Frontend();
		$ajax->init();

		$feed = new BP_MEC_Group_Feeds();
		$feed->init();
	}

	add_action('plugins_loaded', 'mec_bp_load', 10, 1);
}


function mec_buddyboss_license()
{
	require_once plugin_dir_path(__FILE__) . 'core/checkLicense/update-activation.php';
}
add_action('admin_init', 'mec_buddyboss_license');



function load_languages()
{
	$locale = apply_filters('plugin_locale', get_locale(), MECBUDDYBOSSTEXTDOMAIN);

	// WordPress language directory /wp-content/languages/mec-en_US.mo
	$language_filepath = MECBUDDYBOSSDIR . 'languages' . DIRECTORY_SEPARATOR . MECBUDDYBOSSTEXTDOMAIN . '-' . $locale . '.mo';
	// If language file exists on WordPress language directory use it
	if (file_exists($language_filepath)) {
		load_textdomain(MECBUDDYBOSSTEXTDOMAIN, $language_filepath);
	} else {
		load_plugin_textdomain(MECBUDDYBOSSTEXTDOMAIN, false, dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);
	}
}
add_action('init', 'load_languages');



function mec_bp_get_activity_content_body($content, &$activity)
{

	if (!bp_mec_show_preview_in_activity_enabled()) {

		return $content;
	}

	if (!str_contains($activity->type, 'mec')) {

		return $content;
	}

	if (isset($activity->secondary_item_id) && -1 !== strpos($activity->type, 'mec')) {

		$activity->type = 'new_blog_mec-events';
		$activity->component = 'blogs';
	}

	return $content;
}
add_filter('bp_get_activity_content_body', 'mec_bp_get_activity_content_body', 1, 2);
