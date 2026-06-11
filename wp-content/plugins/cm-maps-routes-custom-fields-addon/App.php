<?php
namespace com\cminds\mapsroutesmanager\addon\customfields;

use com\cminds\mapsroutesmanager\addon\customfields\core\Core;
use com\cminds\mapsroutesmanager\addon\customfields\model\Settings;

require_once dirname(__FILE__) . '/core/Core.php';

class App extends Core {
	
	const VERSION = '1.2.4';
	const PREFIX = 'cmmrmcf';
	const SLUG = 'cm-maps-routes-custom-fields-addon';
	const PLUGIN_NAME = 'CM Maps Routes Custom Fields Addon';
	const PLUGIN_WEBSITE = '';
	const PARENT_PLUGIN_SETTINGS_MENU_SLUG = 'cmmrm-settings';
	const PARENT_PREFIX = 'cmmrm';
	const PARENT_SLUG = 'cm-maps-routes-manager';
	const PARENT_MENU = 'cmmrm';
	
	static function bootstrap($pluginFile) {
		parent::bootstrap($pluginFile);
		add_shortcode('route-custom-field', array(__CLASS__, 'routecustomfield'));
	}
	
	static function routecustomfield($atts) {
		$atts = shortcode_atts( array(
			'id' => get_the_ID(),
			'key' => ''
		), $atts, 'route-custom-field' );

		if($atts['id'] != '' && $atts['key'] != '' ) {
			return get_post_meta($atts['id'], 'cmmrmcf_'.$atts['key'], true);
		} else {
			return '';
		}
	}

	static protected function getClassToBootstrap() {
		$classToBootstrap = array_merge(
			parent::getClassToBootstrap(),
			static::getClassNames('controller'),
			static::getClassNames('model'),
			static::getClassNames('metabox')
		);
		if (static::isLicenseOk()) {
			$classToBootstrap = array_merge($classToBootstrap, static::getClassNames('shortcode'), static::getClassNames('widget'));
		}
		return $classToBootstrap;
	}
	
	static function init() {
		parent::init();
		wp_register_script('cmmrmcf-utils', static::url('asset/js/utils.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmmrmcf-backend', static::url('asset/js/backend.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmmrmcf-editor', static::url('asset/js/editor.js'), array('jquery'), static::VERSION, true);
		wp_register_style('cmmrmcf-backend', static::url('asset/css/backend.css'), null, static::VERSION);
		wp_register_style('cmmrmcf-frontend', static::url('asset/css/frontend.css'), array(), static::VERSION);
		wp_register_script('cmmrmcf-frontend', static::url('asset/js/frontend.js'), array('jquery'), static::VERSION, true);
	}
	
	static function activatePlugin() {
		parent::activatePlugin();
		if (!class_exists('com\cminds\mapsroutesmanager\App') OR !call_user_func(array('com\cminds\mapsroutesmanager\App', 'isPro'))) {
			die('CM Maps Routes Manager Pro plugin is missing. Please install and activate it before this addon.');
		}
	}
	
}