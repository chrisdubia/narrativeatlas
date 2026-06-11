<?php
namespace com\cminds\mapsroutesmanager\model;

use com\cminds\mapsroutesmanager\App;

class Labels extends Model {
	
	const FILENAME = 'labels.tsv';
	const OPTION_LABEL_PREFIX = 'cmmrm_label_';
	const TEXT_DOMAIN = 'cm-maps-routes';
	const DB_OPTION_LABELS_NAME = 'cmmrm_option_labels';
	
	protected static $labels = array();
	protected static $labelsByCategories = array();
	private static $labels_cache = array();
	
	
	public static function init() {
		
		parent::init();
		
		add_action('cmmrm_load_label_file', array(__CLASS__, 'loadLabelFile'), 1);
		
		static::loadLabelFile();
		do_action('cmmrm_labels_init');
		
		/* You can use the following filters to add new labels for plugin:
		// add_filter('cmmrm_labels_init_labels', function($labels) {
			// $labels['label_name'] = array('default' => 'Value', 'desc' => 'Description', 'category' => 'Other');
			// return $labels;
		// });
		// add_filter('cmmrm_labels_init_labels_by_categories', function($labelsByCategories) {
			// $labelsByCategories['Other'][] = 'label_name';
			// return $labelsByCategories;
		// });
		*/
		
		static::$labels = apply_filters('cmmrm_labels_init_labels', static::$labels);
		static::$labelsByCategories = apply_filters('cmmrm_labels_init_labels_by_categories', static::$labelsByCategories);
		
	}
	

	public static function getLabel($label_key) {
		$option_name = self::OPTION_LABEL_PREFIX . $label_key;
		$default = self::getDefaultLabel($label_key);

		if ( empty( self::$labels_cache ) ) {
			self::$labels_cache = unserialize( get_option( self::DB_OPTION_LABELS_NAME ) );
		}
		if ( !isset( self::$labels_cache[ $option_name ] ) ) {
			self::$labels_cache[ $option_name ] = get_option($option_name, (empty($default) ? $label_key : $default));
		}

		$result = self::$labels_cache[ $option_name ];

		return $result;
	}
	
	public static function setLabel($label_key, $value) {
		$option_name = self::OPTION_LABEL_PREFIX . $label_key;

		if ( empty( self::$labels_cache ) ) {
			self::$labels_cache = unserialize( get_option( self::DB_OPTION_LABELS_NAME ) );
		}
		self::$labels_cache[ $option_name ] = $value;

		update_option( self::DB_OPTION_LABELS_NAME, serialize( self::$labels_cache ), $autoload = true);
	}
	
	public static function getLocalized($labelKey, $labelDval = '') {
		if($labelKey == static::getLabel($labelKey)) {
			if($labelDval != '') {
				return __($labelDval, static::TEXT_DOMAIN);
			} else {
				if(function_exists('pll__')) {
					return pll__(static::getLabel($labelKey), static::TEXT_DOMAIN);
				} else {
					return __(static::getLabel($labelKey), static::TEXT_DOMAIN);
				}
			}
		} else {
			if(function_exists('pll__')) {
				return pll__(static::getLabel($labelKey), static::TEXT_DOMAIN);
			} else {
				return __(static::getLabel($labelKey), static::TEXT_DOMAIN);
			}
		}
	}
	
	
	public static function getDefaultLabel($key) {
		if ($label = static::getLabelDefinition($key)) {
			return $label['default'];
		}
	}
	
	
	public static function getDescription($key) {
		if ($label = static::getLabelDefinition($key)) {
			return $label['desc'];
		}
	}
	
	
	public static function getLabelDefinition($key) {
		$labels = static::getLabels();
		return (isset($labels[$key]) ? $labels[$key] : NULL);
	}
	
	
	public static function getLabels() {
		return static::$labels;
	}
	
	
	public static function getLabelsByCategories() {
		return static::$labelsByCategories;
	}
	
	
	public static function getDefaultLabelsPath() {
		return App::path('asset') .'/labels/'. static::FILENAME;
	}

	
	public static function loadLabelFile($path = null) {
		$file = explode("\n", file_get_contents(empty($path) ? static::getDefaultLabelsPath() : $path));
		foreach ($file as $row) {
			$row = explode("\t", trim($row));
			if (count($row) >= 2) {
				$label = array(
					'default' => $row[1],
					'desc' => (isset($row[2]) ? $row[2] : null),
					'category' => (isset($row[3]) ? $row[3] : null),
				);
				//pot generate
				//echo '#. '.$row[0].'<br>msgid "'.$row[1].'"'.'<br>msgstr ""'.'<br><br>';
				if(function_exists('pll_register_string')) {
					pll_register_string($row[0], $row[1], static::TEXT_DOMAIN);
				}
				static::$labels[$row[0]] = $label;
				static::$labelsByCategories[$label['category']][] = $row[0];
			}
		}
	}
	
	
	static function processPostRequest() {
		$labels = static::getLabels();
		foreach ($labels as $labelKey => $label) {
			if (isset($_POST['label_'. $labelKey])) {
				static::setLabel($labelKey, stripslashes($_POST['label_'. $labelKey]));
			}
		}
	}
	
	
	static function __($msg) {
		return \__($msg, static::TEXT_DOMAIN);
	}
	
	
}
