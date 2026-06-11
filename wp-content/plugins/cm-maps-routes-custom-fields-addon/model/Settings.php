<?php
namespace com\cminds\mapsroutesmanager\addon\customfields\model;

use com\cminds\mapsroutesmanager\addon\customfields\App;

class Settings extends SettingsAbstract {
	
	const FIELD_TYPE_5_GRADE_SCALE = '5_grade_scale';
	
	const OPTION_ROUTE_CUSTOM_FIELDS = 'cmmrm_route_custom_fields';
	const OPTION_INDEX_ROUTE_SHOW_GRADE_FILTERS = 'cmmrm_index_route_show_grade_filters';
	const OPTION_ROUTE_SNIPPET_SHOW_GRADE_FIELDS = 'cmmrm_route_snippet_show_grade_fields';
	const OPTION_LOCATION_CUSTOM_FIELDS = 'cmmrm_location_custom_fields';
	
	
	public static function getOptionsConfig() {
		$className = 'com\cminds\mapsroutesmanager\model\Settings';
		if (class_exists($className)) {
			return call_user_func(array($className, __FUNCTION__));
		} else {
			return array();
		}
	}
	
	
}
