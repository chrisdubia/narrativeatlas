<?php
namespace com\cminds\mapsroutesmanager\addon\customfields\controller;

use com\cminds\mapsroutesmanager\addon\customfields\controller\abstracts\ValidLicenseController;
use com\cminds\mapsroutesmanager\addon\customfields\model\Route;
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;
use com\cminds\mapsroutesmanager\addon\customfields\helper\RateGradeHelper;

class RouteController extends ValidLicenseController {
	
	const PARAM_FILTER_PREFIX = 'route_custom_field_';
	
	static $actions = array(
		'cmmrm_route_editor_middle' => array('args' => 1, 'priority' => 15),
		'cmmrm_route_after_save' => array('args' => 1),
		'cmmrm_single_route_properties' => array('args' => 1, 'priority' => 30),
		'pre_get_posts' => array('args' => 1),
		'cmmrm_categories_filter' => array('priority' => 1000),
		'route_snippet_end' => array('args' => 2, 'priority' => 100),
	);
	
	static $filters = array(
		'cmmrm_get_filter_url' => array('args' => 3),
	);
	
	/*
 	static function template_include($template) {
 		global $wp_query;
 		var_dump($wp_query->request);exit;
 		return $template;
 	}
	*/
	
	static function embedAssets() {
		wp_enqueue_style('cmmrmcf-frontend');
	}
	
	static function cmmrm_route_editor_middle($routeObj) {
		
		static::embedAssets();
		wp_enqueue_script('cmmrmcf-editor');
		
		$route = '';
		if ($id = $routeObj->getId()) {
			$route = Route::getInstance($id);
		}
		
		$fields = Settings::getOption(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
		if (is_array($fields)) {
			foreach ($fields as $field) {
				$value = (empty($route) ? '' : $route->getCustomField($field['meta_key']));
				echo static::loadFrontendView('editor-field', compact('field', 'value', 'route'));
			}
		}
	}

	static function cmmrm_route_after_save($routeObj) {
		if ($id = $routeObj->getId()) {
			$route = Route::getInstance($id);
			$fields = Settings::getOption(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
			if (is_array($fields)) {
				foreach ($fields as $field) {
					$metaKey = $field['meta_key'];
					$value = (isset($_POST['route_custom_fields'][$metaKey]) ? $_POST['route_custom_fields'][$metaKey] : '');
					$route->setCustomField($metaKey, $value);
				}
			}
		}
	}
	
	static function cmmrm_single_route_properties($routeObj) {
		if ($id = $routeObj->getId()) {
			$route = Route::getInstance($id);
			$fields = Settings::getOption(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
			if (is_array($fields)) {
				foreach ($fields as $field) {
					$value = (empty($route) ? '' : $route->getCustomField($field['meta_key']));
					echo static::loadFrontendView('single-custom-field', compact('field', 'value', 'route'));
				}
			}
		}
	}
	
	static function pre_get_posts(\WP_Query $query) {
		
		//if (is_admin()) return;
		
		if (isset($query->query['meta_query'])) {
			$metaQuery = $query->query['meta_query'];
		} else {
			$metaQuery = array();
		}
		
		$addedQueries = 0;
		//$fields = Settings::getOption(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
		$fields = get_option('cmmrm_route_custom_fields', array());
		if (is_array($fields)) {
			foreach ($fields as $field) {
				//if ($field['type'] == Settings::FIELD_TYPE_5_GRADE_SCALE) {
					$value = static::getCustomFieldValueFromUrl($field['meta_key']);
					if (strlen($value) > 0) {
						$metaQuery[] = array(
							'key' => Route::META_CUSTOM_FIELD_PREFIX . $field['meta_key'],
							'value' => $value,
							'compare' => '=',
							'type' => 'CHAR',
						);
						$addedQueries++;
					}
				//}
			}
		}
		
		//var_dump($metaQuery);exit;
		
		if ($addedQueries > 0) {
			$query->set('meta_query', $metaQuery);
		}
		
	}
	
	static protected function getCustomFieldValueFromUrl($key) {
		$param = static::getCustomFieldParamName($key);
		return filter_input(INPUT_GET, $param);
	}
	
	static protected function getCustomFieldParamName($key) {
		return static::PARAM_FILTER_PREFIX . $key;
	}
	
	static function cmmrm_categories_filter() {
		if (!Settings::getOption(Settings::OPTION_INDEX_ROUTE_SHOW_GRADE_FILTERS)) {
			return;
		}
		$fields = Settings::getOption(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
		foreach ($fields as $field) {
			if ($field['type'] == Settings::FIELD_TYPE_5_GRADE_SCALE) {
				$current = $value = static::getCustomFieldValueFromUrl($field['meta_key']);
				$baseUrl = apply_filters('cmmrm_get_index_filter_url', '', $includeCategory = true);
				$urlParam = static::getCustomFieldParamName($field['meta_key']);
				echo self::loadFrontendView('filter', compact('field', 'current', 'baseUrl', 'urlParam'));
			}
		}
	}
	
	static function cmmrm_get_filter_url($url, $query, $includeCategory) {
		$fields = Settings::getOption(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
		if (is_array($fields)) {
			foreach ($fields as $field) {
				if ($field['type'] == Settings::FIELD_TYPE_5_GRADE_SCALE) {
					if ($value = static::getCustomFieldValueFromUrl($field['meta_key'])) {
						$urlParam = static::getCustomFieldParamName($field['meta_key']);
						$url = add_query_arg($urlParam, urlencode($value), $url);
					}
				}
			}
		}
		return $url;
	}
	
	static function route_snippet_end($routeObj, $atts) {
		if (!Settings::getOption(Settings::OPTION_ROUTE_SNIPPET_SHOW_GRADE_FIELDS)) return;
		static::embedAssets();
		$route = Route::getInstance($routeObj->getid());
		$out = '';
		$fields = Settings::getOption(Settings::OPTION_ROUTE_CUSTOM_FIELDS);
		if (is_array($fields)) foreach ($fields as $field) {
			if ($field['type'] == Settings::FIELD_TYPE_5_GRADE_SCALE) {
				$icons = RateGradeHelper::getFrontend($field, $route->getCustomField($field['meta_key']));
				$out .= '<div class="cmmrm-custom-fields-grades-field flex-auto"><strong>'. RateGradeHelper::getFieldLabel($field) .'</strong>' . $icons .'</div>';
			}
		}
		echo '<div class="cmmrm-custom-fields-grades">'. $out .'</div>';
	}
	
}