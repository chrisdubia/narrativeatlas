<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\controller;

use com\cminds\mapsroutesmanager\addon\buddypress\controller\abstracts\ValidLicenseController;
use com\cminds\mapsroutesmanager\addon\buddypress\model;
use com\cminds\mapsroutesmanager\addon\buddypress\App;

/**
 * Add user's profile tab "Maps" to show the user's maps for all users.
 *
 */
class MapsSegmentController extends ValidLicenseController {

	static $filters = array(
	);

	protected static $actions = array(
        'bp_setup_nav' => array('priority' => 100),
        'bp_group_options_nav' => array('priority' => 100),
	);
	
	static function bp_group_options_nav() {
		global $bp;

		if ( !App::isLicenseOk() ) {
            return;
        }

		$group_id = $bp->groups->current_group->id;
		$group = groups_get_group(array('group_id'=>$group_id));
		$url = get_site_url().'/'.$bp->groups->slug.'/'.$group->slug.'/maps/';

		$getoptions = unserialize(get_option('cmmrm_option_labels'));
		echo '<li id="nav-ourmaps-groups-li"><a href="'.$url.'">'.ucfirst($getoptions['cmmrm_label_buddypress_profile_our_maps_tab']).'</a></li>';
	}

    static function bp_setup_nav() {
        global $bp;

        if ( !App::isLicenseOk() ) {
            return;
        }

		$showMaps = (bool)model\Settings::getOption(model\Settings::OPTION_USER_PROFILE_MAPS_TAB);
        if ( !$showMaps ) {
            return;
		}
		
		$parent_url = '';
		if(!empty($bp->displayed_user->domain)) {
			$parent_url = $bp->displayed_user->domain;
		}
		$parent_slug = '';
		if(!empty($bp->profile->slug)) {
			$parent_slug = $bp->profile->slug;
		}

		$getoptions = unserialize(get_option('cmmrm_option_labels'));
        bp_core_new_nav_item( array(
            'name'                  => ucfirst($getoptions['cmmrm_label_buddypress_profile_maps_tab']),
            'slug'                  => 'maps',
            'parent_url'            => $parent_url,
            'parent_slug'           => $parent_slug,
            'screen_function'       => array(get_class(), 'maps_screen'),
            'position'              => 200,
            'default_subnav_slug'   => 'maps'
        ) );
    }

    static function maps_screen() {
        if ( !App::isLicenseOk() ) {
            return;
        }
        add_action( 'bp_template_title', array(get_class(), 'maps_screen_title') );
        add_action( 'bp_template_content', array(get_class(), 'maps_screen_content') );
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
    }

    static function maps_screen_title() {
		$getoptions = unserialize(get_option('cmmrm_option_labels'));
        echo ucfirst($getoptions['cmmrm_label_buddypress_profile_maps_tab']);
    }

    static function maps_screen_content() {
        wp_enqueue_style('cmmrm-buddypress-frontend');
        wp_enqueue_script('cmmrm-buddypress-frontend');

        $userId = model\Activity::getBuddyPressDisplayedUserId();
        $routesIds = model\Route::getByUser($userId, model\Route::RETURN_ID);
        $showManage = model\Settings::showManageElements($userId, model\Settings::OPTION_PROFILE_MAPS_SHOW_MANAGE_SHORTCODE);

		$getoptions = unserialize(get_option('cmmrm_option_labels'));
        $showMapText = ucfirst($getoptions['cmmrm_label_buddypress_profile_show_map']);
        $manageMapsText = ucfirst($getoptions['cmmrm_label_buddypress_profile_manage_maps_tab']);

        echo self::loadFrontendView('user-maps', compact('routesIds', 'userId', 'showManage', 'showMapText', 'manageMapsText'));
    }

	static function getMapsIndexUrl() {
		return get_post_type_archive_link(model\Route::POST_TYPE);
	}

}