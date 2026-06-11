<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\helper\AdminNotice;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Settings;

class SettingsController extends Controller {
	
	const ACTION_CLEAR_CACHE = 'clear-cache';
	
	const PAGE_ABOUT_URL = 'https://www.cminds.com/store/maps-routes-manager-plugin-for-wordpress-by-creativeminds/';
	const PAGE_USER_GUIDE_URL = 'https://www.cminds.com/wordpress-plugins-knowledge-base-and-documentation/?hscat=534-cm-maps-route-manager';
	
	protected static $actions = array(
		array('name' => 'admin_menu', 'priority' => 15),
		'admin_notices',
		'admin_head',
		'cmmrm_display_supported_shortcodes',
	);

	protected static $filters = array(
		'cmmrm-settings-category' => array('args' => 2, 'method' => 'settingsLabels'),
		'geodir_googlemap_script_extra',
		'cmmrm_get_route_index_params_names',
	);

	protected static $ajax = array(
		'cmmrm_admin_notice_dismiss',
	);
	
	static function admin_menu() {
		add_submenu_page(App::PREFIX, App::getPluginName() . ' Settings', 'Settings', 'manage_options', self::getMenuSlug(), array(get_called_class(), 'render'));
	}
	
	static function getMenuSlug() {
		return App::PREFIX . '-settings';
	}
	
	static function admin_notices() {
		if (!get_option('permalink_structure')) {
			printf('<div class="error"><p><strong>%s:</strong> to make the plugin works properly
				please enable the <a href="%s">Wordpress permalinks</a>.</p></div>',
				App::getPluginName(), admin_url('options-permalink.php'));
		}
		if (!Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY)) {
			printf('<div class="error"><p><strong>%s:</strong> you need to enter the <strong>Google Maps App Key</strong> in the plugin settings.
				<a href="%s" class="button">Open Settings</a></p></div>',
				App::getPluginName(), admin_url('admin.php?page='. self::getMenuSlug())
			);
		}
	}
	
	static function render() {
		wp_enqueue_style('cmmrm-settings');
		wp_enqueue_script('cmmrm-backend');
		echo self::loadView('backend/template', array(
			'title' => App::getPluginName() . ' Settings',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('licensing-box') . self::loadBackendView('settings', array(
				'clearCacheUrl' => self::createBackendUrl(self::getMenuSlug(), array('action' => self::ACTION_CLEAR_CACHE), self::ACTION_CLEAR_CACHE),
			)),
		));
	}
	
	static function settingsLabels($result, $category) {
		if ($category == 'labels') {
			$result = self::loadBackendView('labels');
		}
		return $result;
	}
	
	static function processRequest() {
		$fileName = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		if (is_admin() AND $fileName == 'admin.php' AND !empty($_GET['page']) AND $_GET['page'] == self::getMenuSlug()) {
			
			if (!empty($_POST)) {
				
				// CSRF protection
		        if ((empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], self::getMenuSlug()))) {
		        	// Invalid nonce
		        } else {
			        Settings::processPostRequest($_POST);
			        Labels::processPostRequest();
			        $response = array('status' => 'ok', 'msg' => 'Settings have been updated.');
			        wp_redirect(self::createBackendUrl(self::getMenuSlug(), $response));
			        exit;
		        }
	            
			}
			else if (!empty($_GET['action']) AND !empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], $_GET['action'])) switch ($_GET['action']) {
				case self::ACTION_CLEAR_CACHE:
					delete_option('rewrite_rules');
					flush_rewrite_rules(true);
					wp_redirect(self::createBackendUrl(self::getMenuSlug(), array('status' => 'ok', 'msg' => 'Cache has been removed.')));
					exit;
					break;
			}
	        
		}
	}
	
	static function getSectionExperts() {
		return self::loadBackendView('experts');
	}
	
	static function cmmrm_admin_notice_dismiss() {
		AdminNotice::processAjaxDismiss();
	}
	
	static function cmmrm_display_supported_shortcodes() {
		echo self::loadBackendView('shortcodes');
	}
	
	static function fixPathesInSettings() {
		if (App::isPro()) {
			$val = get_option(Settings::OPTION_ROUTE_DEFAULT_IMAGE);
			if (!empty($val) AND strpos($val, '/cm-route-manager/asset/img/world-map-small.png') !== false) {
				// Still having image set to free version path - fix it:
				$val = App::url('asset/img/world-map-small.png');
				update_option(Settings::OPTION_ROUTE_DEFAULT_IMAGE, $val);
			}
		}
	}
	
	/**
	 * Resolve conflict with Geodirectory.
	 * 
	 * @param string $extra
	 * @return string
	 */
	static function geodir_googlemap_script_extra($extra) {
		$extra .= '&libraries=places,geometry,drawing';
		return $extra;
	}
	
	static function cmmrm_get_route_index_params_names($result) {
		return Settings::getRouteIndexPageParamsNames();
	}
	
	static function admin_head() {
		wp_enqueue_style('cmmrm-backend');
		wp_enqueue_script('cmmrm-backend');
	}
	
}