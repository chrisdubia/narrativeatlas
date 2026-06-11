<?php
namespace com\cminds\mapsroutesmanager\addon\customfields\controller;

use com\cminds\mapsroutesmanager\addon\customfields\model\Labels;
use com\cminds\mapsroutesmanager\addon\customfields\App;
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;

class SettingsController extends Controller {
	
	const SETTINGS_TAB_ID = 'custom-fields';
	
	static $fieldTypes = array(
		Settings::TYPE_STRING => 'Single-line text field',
		Settings::TYPE_TEXTAREA => 'Multi-line text area',
		Settings::FIELD_TYPE_5_GRADE_SCALE => '5-grade scale',
	);
	
	protected static $actions = array(
		'admin_init',
		'cmmrm_labels_init',
		//'cmmrm_settings_save',
	);

	protected static $filters = array(
		'cmmrm_options_config',
		'cmmrm_settings_pages',
		'cmmrm_settings_pages_groups',
		'cmmrm_settings_before_save_value' => array('args' => 3),
	);

	protected static $ajax = array(
	);
	
	static function admin_init() {
		wp_enqueue_style('cmmrmcf-backend');
		wp_enqueue_script('cmmrmcf-backend');
	}
	
	static function cmmrm_labels_init() {
		do_action('cmmrm_load_label_file', App::path('asset/labels/labels.tsv'));
	}
	
	static function cmmrm_options_config($config) {
		
		$config[Settings::OPTION_ROUTE_CUSTOM_FIELDS] = array(
			'category' => static::SETTINGS_TAB_ID,
			'subcategory' => 'routes',
			'title' => 'Custom fields for routes',
			'type' => Settings::TYPE_CUSTOM,
			'content' => array(App::namespaced('controller\SettingsController'), 'getSettingField'),
		);
		$config[Settings::OPTION_INDEX_ROUTE_SHOW_GRADE_FILTERS] = array(
			'category' => static::SETTINGS_TAB_ID,
			'subcategory' => 'routes',
			'title' => 'Show index page filters for each grade field',
			'desc' => 'If you created a 5-grade scale custom field for the routes you can enable showing the grade filters on the maps routes index page.',
			'type' => Settings::TYPE_BOOL,
			'default' => 0,
		);
		$config[Settings::OPTION_ROUTE_SNIPPET_SHOW_GRADE_FIELDS] = array(
			'category' => static::SETTINGS_TAB_ID,
			'subcategory' => 'routes',
			'title' => 'Show icons for each grade field in the route\'s snippet',
			'desc' => 'If you created a 5-grade scale custom field for the routes you can enable showing the grade icons on the routes\' snippet.',
			'type' => Settings::TYPE_BOOL,
			'default' => 0,
		);
		
		$config[Settings::OPTION_LOCATION_CUSTOM_FIELDS] = array(
			'category' => static::SETTINGS_TAB_ID,
			'subcategory' => 'locations',
			'title' => 'Custom fields for locations',
			'type' => Settings::TYPE_CUSTOM,
			'content' => array(App::namespaced('controller\SettingsController'), 'getSettingField'),
		);
		
		return $config;
	}
	
	static function getSettingField($settingName) {
		$fields = Settings::getOption($settingName);
		if (!is_array($fields)) $fields = array();
		$fieldTypes = apply_filters('cmmrm_custom_fields_types', SettingsController::$fieldTypes);
		return self::loadBackendView('custom-fields', compact('settingName', 'fields', 'fieldTypes'));
	}
	
	public static function cmmrm_settings_pages($pages) {
		$pages[static::SETTINGS_TAB_ID] = 'Custom Fields';
		return $pages;
	}
	
	public static function cmmrm_settings_pages_groups($subcategories) {
		$subcategories[static::SETTINGS_TAB_ID]['routes'] = 'Routes';
		$subcategories[static::SETTINGS_TAB_ID]['locations'] = 'Locations';
		return $subcategories;
	}
	
	static function cmmrm_settings_before_save_value($value, $name, $field) {
		if (Settings::OPTION_LOCATION_CUSTOM_FIELDS == $name OR Settings::OPTION_ROUTE_CUSTOM_FIELDS == $name) {
			$newValue = array();
			foreach ($value['label'] as $i => $label) {
				if ($i == 0) continue;
				$newValue[] = array(
					'label' => $label,
					'meta_key' => $value['meta_key'][$i],
					'type' => $value['type'][$i],
				);
			}
			$value = $newValue;
		}
		return $value;
	}

}