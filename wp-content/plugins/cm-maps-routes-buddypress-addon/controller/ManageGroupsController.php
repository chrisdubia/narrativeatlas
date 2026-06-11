<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\controller;

use com\cminds\mapsroutesmanager\addon\buddypress\App;
use com\cminds\mapsroutesmanager\addon\buddypress\controller\abstracts\ValidLicenseController;
use com\cminds\mapsroutesmanager\addon\buddypress\model;
use com\cminds\mapsroutesmanager\model\Route;

class ManageGroupsController extends ValidLicenseController {

	static $filters = array(
	);

	protected static $actions = array(
        'cmmrm_route_editor_middle' => array('args' => 1),
		'save_post' => array('args' => 1)
	);
	
	static function cmmrm_route_editor_middle(Route $route) {
		global $wpdb;
		if ( !App::isLicenseOk() ) {
            return;
        }
		if ( !bp_is_active( 'groups' ) ||
             !model\Settings::getOption(model\Settings::OPTION_BP_GROUPS_ENABLE_FOR_ROUTE)) {
            return;
        }
		$bp_groups_privacy = model\Settings::getOption(model\Settings::OPTION_BP_GROUPS_PRIVACY_FOR_ROUTE);
		if($bp_groups_privacy == 'all') {
			$current_user_id = get_current_user_id();
			if($current_user_id) {
				$buddypressGroups = $wpdb->get_results("SELECT g.*, gm.is_admin FROM ".$wpdb->prefix."bp_groups as g, ".$wpdb->prefix."bp_groups_members as gm where g.id = gm.group_id and gm.user_id='".$current_user_id."' and gm.is_confirmed='1' order by g.name asc", ARRAY_A);
			} else {
				$buddypressGroups = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."bp_groups order by name asc", ARRAY_A);
			}
		} else {
			$buddypressGroups = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."bp_groups WHERE status='".$bp_groups_privacy."' order by name asc", ARRAY_A);
		}
		if($route->getId()) {
			$buddypressCurrentGroups = get_post_meta($route->getId(), '_cmmrm_bp_groups', true);
			if($buddypressCurrentGroups == '') {
				$buddypressCurrentGroups = array();
			}
		} else {
			$buddypressCurrentGroups = array();
		}
        echo self::loadFrontendView('manage-groups', compact('buddypressGroups', 'buddypressCurrentGroups'));
	}

	static function save_post($post_id) {
		if ( !App::isLicenseOk() ) {
            return;
        }
		if ( !bp_is_active( 'groups' ) ||
             !model\Settings::getOption(model\Settings::OPTION_BP_GROUPS_ENABLE_FOR_ROUTE)) {
            return;
        }
		$bp_groups = $_POST['bpgroups'];
		update_post_meta($post_id, '_cmmrm_bp_groups', $bp_groups);
	}

}