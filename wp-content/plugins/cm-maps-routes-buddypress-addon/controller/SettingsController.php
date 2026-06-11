<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress\controller;

use com\cminds\mapsroutesmanager\addon\buddypress\App;
use com\cminds\mapsroutesmanager\addon\buddypress\model;

class SettingsController extends Controller {

	protected static $actions = array(
		'cmmrm_labels_init' => array('args' => 1),
	);

	protected static $filters = array(
        'cmmrm_options_config' => array('args' => 1),
        'cmmrm_settings_pages' => array('args' => 1),
        'cmmrm_settings_pages_groups' => array('args' => 1),
    );

	protected static $ajax = array(
	);

    static function cmmrm_options_config($config) {
        return array_merge($config, model\Settings::getOptionsConfig());
    }

    static function cmmrm_settings_pages($categories) {
        $categories[model\Settings::CMRM_BUDDYPRESS_CATEGORY_SLUG] = model\Settings::CMRM_BUDDYPRESS_CATEGORY_NAME;
        return $categories;
    }

    static function cmmrm_settings_pages_groups($groups) {
        $groups[model\Settings::CMRM_BUDDYPRESS_CATEGORY_SLUG]['user-profile-maps'] = 'User profile: User\'s Maps Tab';
        $groups[model\Settings::CMRM_BUDDYPRESS_CATEGORY_SLUG]['user-profile-manage-maps'] = 'User profile: Manage Maps Tab';
        $groups[model\Settings::CMRM_BUDDYPRESS_CATEGORY_SLUG]['activity-feed'] = 'Activity Feed';
		if ( bp_is_active( 'groups' ) ) {
			$groups[model\Settings::CMRM_BUDDYPRESS_CATEGORY_SLUG]['groups'] = 'Groups';
		}
        return $groups;
    }

    static function cmmrm_labels_init() {
		do_action('cmmrm_load_label_file', App::path('asset/labels/labels.tsv'));
	}

}