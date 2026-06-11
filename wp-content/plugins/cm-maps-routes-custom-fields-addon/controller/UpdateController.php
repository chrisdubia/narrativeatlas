<?php
namespace com\cminds\mapsroutesmanager\addon\customfields\controller;

use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;

class UpdateController extends Controller {
	
	const OPTION_NAME = 'cmmrmcf_update_methods';
	
	static $actions = array('plugins_loaded');

	static function plugins_loaded() {
		global $wpdb;
		
		if (defined('DOING_AJAX') AND DOING_AJAX) return;
		
		$updates = get_option(self::OPTION_NAME);
		if (empty($updates)) $updates = array();
		$count = count($updates);
		
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (preg_match('/^update((_[0-9]+)+)/', $method, $match)) {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		
		if ($count != count($updates)) {
			update_option(self::OPTION_NAME, $updates);
		}
		
	}

	static function update_1_0_2() {
		global $wpdb;
		
		$options = array(Settings::OPTION_ROUTE_CUSTOM_FIELDS, Settings::OPTION_LOCATION_CUSTOM_FIELDS);
		foreach ($options as $optionName) {
			$fields = get_option(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
			if (is_array($fields)) {
				if (is_array($fields)) foreach ($fields as &$field) {
					if (empty($field['type'])) {
						$field['type'] = Settings::TYPE_STRING;
					}
				}
				update_option($optionName, $fields, $autoload = true);
			}
		}

	}
		
}