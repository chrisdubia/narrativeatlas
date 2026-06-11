<?php
/*
Plugin Name: CM Maps Routes Manager BuddyPress Integration Addon
Description: AddOn for CM Maps Routes Manager Pro and BuddyPress supports adding the maps functionality to BuddyPress social network.
Author: CreativeMindsSolutions
Version: 1.1.1
Requires Plugins: cm-maps-routes-manager-pro
*/

if (version_compare('5.3', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

require_once dirname(__FILE__) . '/App.php';

class BP_Rmaps_Component extends BP_Component {
	
	public function __construct() {
		$getoptions = unserialize(get_option('cmmrm_option_labels'));
		parent::start(
			'rmaps',
			$getoptions['cmmrm_label_buddypress_menu_title'],
			buddypress()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 200,
			)
		);
	}
	
	public function register_nav( $main_nav = array(), $sub_nav = array() ) {
		global $bp;
		
		if(!com\cminds\mapsroutesmanager\addon\buddypress\App::isLicenseOk()) {
            return;
        }
		
		$getoptions = unserialize(get_option('cmmrm_option_labels'));
		
		$user_profile_maps_tab = get_option('cmmrm_buddypress_user_profile_maps_tab', 0);
		
		if($user_profile_maps_tab) {
				
			$parent_url = '';
			if(!empty($bp->displayed_user->domain)) {
				$parent_url = $bp->displayed_user->domain;
			}
			$parent_slug = '';
			if(!empty($bp->profile->slug)) {
				$parent_slug = $bp->profile->slug;
			}

			$main_nav = array(
				'name'                => ucfirst($getoptions['cmmrm_label_buddypress_profile_maps_tab']),
				'slug'                => 'maps',
				'parent_url'          => $parent_url,
				'parent_slug'         => $parent_slug,
				'position'            => 200,
				'screen_function'     => array('com\cminds\mapsroutesmanager\addon\buddypress\controller\MapsSegmentController', 'maps_screen'),
				'default_subnav_slug' => 'maps',
				'item_css_id'         => $this->id,
			);
		}

		parent::register_nav( $main_nav, $sub_nav );
	}

}

function bp_setup_rmaps() {
	buddypress()->rmaps = new BP_Rmaps_Component();
}
add_action( 'bp_setup_components', 'bp_setup_rmaps', 6 );

add_action('bp_include', 'load_cmmrm_buddypress_addon');
function load_cmmrm_buddypress_addon () {
    com\cminds\mapsroutesmanager\addon\buddypress\App::bootstrap(__FILE__);
}

add_action('wp_loaded', 'cmmrm_buddypress_addon_requires_bp');

function cmmrm_buddypress_addon_requires_bp() {
    if ( !did_action('bp_include') ) {
        add_action('admin_notices', function () {
            $error = sprintf(__('%s requires <b><a href="https://wordpress.org/plugins/buddypress/">BuddyPress</a></b> plugin to be installed and activated.'),
                'CM Maps Routes Manager BuddyPress Integration Addon');
            echo '<div class="error fade">
				<p>'. $error .'</p>
			</div>';
        });
    }
}

function cmmrm_groups_setup_nav() {
    global $bp;
	$getoptions = unserialize(get_option('cmmrm_option_labels'));
    bp_core_new_subnav_item( array(
       'name' => ucfirst($getoptions['cmmrm_label_buddypress_profile_maps_tab']),
       'slug' => 'maps',
       'parent_url' => $bp->loggedin_user->domain . $bp->groups->slug . '/',
       'parent_slug' => $bp->groups->slug,
       'screen_function' => 'cmmrm_groups_cmmaps_to_show_screen',
       'position' => 40 ) );
}
add_action( 'bp_setup_nav', 'cmmrm_groups_setup_nav' );

function cmmrm_groups_cmmaps_to_show_screen() {
	add_action( 'bp_template_title', 'cmmrm_groups_cmmaps_to_show_screen_title' );
	add_action( 'bp_template_content', 'cmmrm_groups_cmmaps_to_show_screen_content' );
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function cmmrm_groups_cmmaps_to_show_screen_title() {
	echo '';
}

function cmmrm_groups_cmmaps_to_show_screen_content() {
	echo do_shortcode('[cm-route-bp-index]');
}

function cmmrmbp_shortcode($atts) {
	global $wpdb;
	$output = '';
	$current_user_id = get_current_user_id();
	if($current_user_id > 0) {
		$buddypressGroups = $wpdb->get_results("SELECT g.*, gm.is_admin FROM ".$wpdb->prefix."bp_groups as g, ".$wpdb->prefix."bp_groups_members as gm where g.id = gm.group_id and gm.user_id='".$current_user_id."' and gm.is_confirmed='1' order by g.name asc", ARRAY_A);
		if(count($buddypressGroups) > 0) {
			foreach($buddypressGroups as $sgroup) {
				$group = groups_get_group(array('group_id'=>$sgroup['id']));
				$group_permalink = trailingslashit(bp_get_root_domain().'/'.bp_get_groups_root_slug().'/'.$group->slug.'/maps/');
				$output .= '<a href="'.$group_permalink.'">'.$sgroup['name'].' Maps</a><br />';
			}
		}
	}
	return $output;
}
add_shortcode( 'user-groups-map-link', 'cmmrmbp_shortcode' );