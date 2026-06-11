<?php
namespace com\cminds\mapsroutesmanager\model;

abstract class SettingsAbstract {

	const TYPE_BOOL = 'bool';
	const TYPE_INT = 'int';
	const TYPE_STRING = 'string';
	const TYPE_TEXTAREA = 'textarea';
	const TYPE_RICH_TEXT = 'rich_text';
	const TYPE_RADIO = 'radio';
	const TYPE_SELECT = 'select';
	const TYPE_MULTISELECT = 'multiselect';
	const TYPE_MULTICHECKBOX = 'multicheckbox';
	const TYPE_CSV_LINE = 'csv_line';
	const TYPE_USERS_LIST = 'users_list';
	const TYPE_COLOR = 'color';
	const TYPE_CUSTOM = 'custom';

	const PAGE_CREATE_KEY = '--create--';
	const PAGE_DEFINITION = 'newPage';

	public static $categories = array();
	public static $subcategories = array();
	public static $options = array();

	public static function getOptionsConfig() {
		return array();
	}

	public static function getOptionsConfigByCategory($category, $subcategory = null) {
		if ( empty(static::$options) ) {
			static::$options = static::getOptionsConfig();
		}

		if ( !empty($category) ) {
			$categories = static::getCategories();
			if ( !empty($categories) && !in_array($category, $categories) ) {
				static::$options = static::getOptionsConfig();
			}
		}
		return array_filter(static::$options, function($val) use ($category, $subcategory) {
			if ($val['category'] == $category) {
				return (is_null($subcategory) OR $val['subcategory'] == $subcategory);
			}
		});
	}

	public static function getOptionConfig($name) {
		if ( empty(static::$options) || !isset(static::$options[$name]) ) {
			static::$options = static::getOptionsConfig();
		}
		if (isset(static::$options[$name])) {
			return static::$options[$name];
		}
		
		return null;
	}

	public static function setOption($name, $value) {
		static::$options = array();
		$options = static::getOptionsConfig();
		if (isset($options[$name])) {
			$field = $options[$name];
			$value = apply_filters('cmmrm_settings_before_save_value', $value, $name, $field);

			if($name == 'CMMRM_map_tiles') {
				$newValue = array();
				if(!empty($value['tile_url'])) {
					foreach ($value['tile_url'] as $i => $tile) {
						if($value['tile_name'][$i] != '' && $value['tile_url'][$i] != '') {
							$newValue[] = array(
								'tile_name' => $value['tile_name'][$i],
								'tile_url' => $value['tile_url'][$i],
								'tile_default' => $value['tile_default'][$i],
							);
						}
					}
				}
				$value = $newValue;
			}

			\update_option($name, static::cast($value, $field['type']), $autoload = true);
			if (isset($field['afterSave']) AND is_callable($field['afterSave'])) {
				call_user_func($field['afterSave'], $field);
			}
		} else {
			\update_option($name, $value, $autoload = true);
		}
		static::$options = static::getOptionsConfig();
	}

	public static function getOption($name) {
		if ( ! isset(static::$options[$name])) {
			static::$options = static::getOptionsConfig();
		}
		if ( isset(static::$options[$name]) ) {
			$field = static::$options[$name];
			$defaultValue = (isset($field['default']) ? $field['default'] : null);
			if (is_object($defaultValue) AND is_callable($defaultValue)) $defaultValue = call_user_func($defaultValue);
			return static::cast(\get_option($name, $defaultValue), $field['type']);
		}
	}

	public static function getCategories() {
		$categories = array();
		if ( empty(static::$options) ) {
			static::$options = static::getOptionsConfig();
		}
		foreach ( static::$options as $option ) {
			$categories[] = $option['category'];
		}
		return $categories;
	}

	public static function getSubcategories($category) {
		$subcategories = array();
		if ( empty(static::$options) ) {
			static::$options = static::getOptionsConfig();
		}
		foreach ( static::$options as $option) {
			if ($option['category'] == $category) {
				$subcategories[] = $option['subcategory'];
			}
		}
		return $subcategories;
	}

	protected static function boolval($val) {
		return (boolean) $val;
	}

	protected static function arrayval($val) {
		if (is_array($val)) return $val;
		else if (is_object($val)) return (array)$val;
		else return array();
	}

	protected static function cast($val, $type) {
		if ($type == static::TYPE_STRING) {
			return \trim(\strval($val));
		}
		else if ($type == static::TYPE_BOOL) {
			return (\intval($val) ? 1 : 0);
		}
		else if (in_array($type, array(static::TYPE_MULTISELECT, static::TYPE_USERS_LIST, static::TYPE_MULTICHECKBOX))) {
			if (empty($val)) return array();
			else return $val;
		}
		else if ($type == static::TYPE_MULTISELECT OR $type == static::TYPE_USERS_LIST) {
			if (empty($val)) return array();
			else return $val;
		}
		else {
			$castFunction = $type . 'val';
			if (function_exists('\\' . $castFunction)) {
				return call_user_func('\\' . $castFunction, $val);
			}
			else if (method_exists(__CLASS__, $castFunction)) {
				return call_user_func(array(__CLASS__, $castFunction), $val);
			} else {
				return $val;
			}
		}
	}

	protected static function csv_lineval($value) {
		if (!is_array($value)) $value = explode(',', $value);
		return array_filter($value);
	}

	public static function processPostRequest($data) {

		// Create new pages
		if ( empty(static::$options) ) {
			static::$options = static::getOptionsConfig();
		}
		foreach ($data as $key => &$value) {
			if ($value == static::PAGE_CREATE_KEY AND !empty(static::$options[$key][static::PAGE_DEFINITION])) {
				$post = array_merge(array(
					'post_author' => get_current_user_id(),
					'post_status' => 'publish',
					'post_type' => 'page',
					'comment_status' => 'closed',
					'ping_status' => 'closed',
				), static::$options[$key][static::PAGE_DEFINITION]);
				$postId = wp_insert_post($post);
				if (is_numeric($postId)) {
					$value = $postId;
				}
			}
		}

		// Save data
		$data = array_map('stripslashes_deep', $data);
		foreach ( static::$options as $name => $optionConfig ) {
			if ( isset($data[$name]) ) {
				static::setOption($name, $data[$name]);
			} else {
				static::setOption($name, null);
			}
		}

	}

	public static function userId($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		return $userId;
	}

	public static function isLoggedIn($userId = null) {
		$userId = static::userId($userId);
		return !empty($userId);
	}

	public static function getRolesOptions() {
		global $wp_roles;
		$result = array();
		if (!empty($wp_roles) AND is_array($wp_roles->roles)) foreach ($wp_roles->roles as $name => $role) {
			$result[$name] = $role['name'];
		}
		return $result;
	}

	public static function getPagesOptions() {
		$result = array(null => '--');
		if(is_admin()) {
			$pages = \get_posts(array('post_type' =>'page', 'post_status' =>'publish', 'numberposts' => -1, 'fields' => 'ids'));
			$result = array(null => '--');
			if (is_array($pages)) {
				foreach ($pages as $page_id) {
					$result[$page_id] = \get_the_title($page_id);
				}
			}
		}
		return $result;
	}

	public static function getPageTemplatesOptions() {
		$theme = \wp_get_theme();
		$templates = (array)$theme->get_page_templates();
		$result = array();
		if ($pageTemplate = locate_template('page.php', false, false)) {
			$result['page.php'] = 'page.php';
		}
		/*
		if ($singleTemplate = locate_template('single.php', false, false)) {
			$result['single.php'] = 'Theme\'s single.php';
		}
		*/
		if ($pageTemplate = locate_template('singular.php', false, false)) {
			$result['singular.php'] = 'singular.php';
		}
		if ($pageTemplate = locate_template('index.php', false, false)) {
			$result['index.php'] = 'index.php';
		}
		return array_merge($result, $templates);
	}

}