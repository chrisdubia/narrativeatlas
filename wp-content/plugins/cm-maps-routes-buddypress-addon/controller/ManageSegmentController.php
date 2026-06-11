<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\controller;

use com\cminds\mapsroutesmanager\addon\buddypress\App;
use com\cminds\mapsroutesmanager\addon\buddypress\controller\abstracts\ValidLicenseController;
use com\cminds\mapsroutesmanager\addon\buddypress\model;

/**
 * Add user's profile tab "Manage maps" to edit/delete/publish own maps by that user.
 *
 */
class ManageSegmentController extends ValidLicenseController {

	static $filters = array(
	);

	protected static $actions = array(
        'bp_setup_nav' => array('priority' => 100),
	);

    static function bp_setup_nav() {
        global $bp;

        $userId = model\Activity::getBuddyPressDisplayedUserId();
        $manageMapsTab = model\Settings::OPTION_USER_PROFILE_MANAGE_MAPS_TAB;
        if ( !model\Settings::showManageElements( $userId, $manageMapsTab ) ) {
            return;
        }

        if ( !App::isLicenseOk() ) {
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
            'name'                  => ucfirst($getoptions['cmmrm_label_buddypress_profile_manage_maps_tab']),
            'slug'                  => 'manage_maps',
            'parent_url'            => $parent_url,
            'parent_slug'           => $parent_slug,
            'screen_function'       => array(get_class(), 'maps_screen'),
            'position'              => 300,
            'default_subnav_slug'   => 'manage_maps'
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
        echo ucfirst($getoptions['cmmrm_label_buddypress_profile_manage_maps_tab']);
    }

    static function maps_screen_content() {
        wp_enqueue_style('cmmrm-buddypress-frontend');
        echo self::loadFrontendView('manage-routes');
    }

    static function getMapsIndexUrl() {
        return get_post_type_archive_link(model\Route::POST_TYPE);
    }

}