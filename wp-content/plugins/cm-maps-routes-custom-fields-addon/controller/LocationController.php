<?php
namespace com\cminds\mapsroutesmanager\addon\customfields\controller;

use com\cminds\mapsroutesmanager\addon\customfields\controller\abstracts\ValidLicenseController;
use com\cminds\mapsroutesmanager\addon\customfields\model\Route;
use com\cminds\mapsroutesmanager\addon\customfields\model\Location;
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;

class LocationController extends ValidLicenseController {
	
	static $actions = array(
		'cmmrm_route_editor_location_bottom' => array('args' => 1, 'priority' => 15),
		'cmmrm_location_after_save' => array('args' => 2),
		'cmmrm_editor_wp_footer_js' => array('args' => 1),
		'cmmrm_single_location_before_images' => array('args' => 1),
	);

	static function cmmrm_route_editor_location_bottom($routeObj) {
		$route = '';
		if ($id = $routeObj->getId()) {
			$route = Route::getInstance($id);
		}
		$fields = Settings::getOption(Settings::OPTION_LOCATION_CUSTOM_FIELDS);
		if (is_array($fields)) foreach ($fields as $field) {
			$metaKey = $field['meta_key'];
			echo static::loadFrontendView('editor-field', compact('field', 'route'));
		}
		wp_enqueue_script('cmmrmcf-editor');
	}
	
	static function cmmrm_editor_wp_footer_js($routeObj) {
		$fields = Settings::getOption(Settings::OPTION_LOCATION_CUSTOM_FIELDS);
		$route = Route::getInstance($routeObj->getid());
		$locationsIds = $routeObj->getLocationsIds();
		$values = array();
		foreach ($locationsIds as $locationId) {
			if ($location = Location::getInstance($locationId)) {
				if (is_array($fields)) foreach ($fields as $field) {
					$metaKey = $field['meta_key'];
					$values[$locationId][$metaKey] = $location->getCustomField($metaKey);
				}
			}
		}
		?>CMMRM_CustomFields_Editor.propagateValues(<?php echo json_encode($values); ?>);<?php
	}
	
	static function cmmrm_location_after_save($locationObj, $i) {
		if ($id = $locationObj->getId()) {
			$location = Location::getInstance($id);
			$fields = Settings::getOption(Settings::OPTION_LOCATION_CUSTOM_FIELDS);
			if (is_array($fields)) foreach ($fields as $field) {
				$metaKey = $field['meta_key'];
				$value = (isset($_POST['location_custom_fields'][$metaKey][$i]) ? $_POST['location_custom_fields'][$metaKey][$i] : '');
				$location->setCustomField($metaKey, $value);
			}
		}
	}
	
	static function cmmrm_single_location_before_images($locationObj) {
		if ($id = $locationObj->getId()) {
			$location = Location::getInstance($id);
			$fields = Settings::getOption(Settings::OPTION_LOCATION_CUSTOM_FIELDS);
			if (is_array($fields)) foreach ($fields as $field) {
				$value = (empty($location) ? '' : $location->getCustomField($field['meta_key']));
				echo static::loadFrontendView('single-custom-field', compact('field', 'value', 'location'));
			}
		}
	}
	
}