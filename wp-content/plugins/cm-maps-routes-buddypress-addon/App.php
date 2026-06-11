<?php
namespace com\cminds\mapsroutesmanager\addon\buddypress;

use com\cminds\mapsroutesmanager\addon\buddypress\core\Core;
use com\cminds\mapsroutesmanager\addon\buddypress\helper;

require_once dirname(__FILE__) . '/core/Core.php';

class App extends Core {

    const PREFIX = 'cmmrm_buddypress';
    const SLUG = 'cm-maps-routes-buddypress';
    const PLUGIN_NAME = 'CM Maps Routes Manager BuddyPress Integration Addon';
    const PARENT_PLUGIN_SETTINGS_MENU_SLUG = 'cmmrm-settings';
    const PARENT_PREFIX = 'cmmrm';
    const PARENT_SLUG = 'cm-maps-routes-manager';
    const PARENT_MENU = 'cmmrm';
    const PARENT_NAMESPACE = '\com\cminds\mapsroutesmanager';
    const BASE_PLUGIN_PURCHASE_URL = 'https://www.cminds.com/wordpress-plugins-library/google-maps-routes-manager-plugin-for-wordpress-by-creativeminds/';
    const BASE_PLUGIN_NAME = 'CM Map Routes Manager Pro';

    const PLUGIN_VERSION = '1.1.1';
    const PLUGIN_RELEASE = '';
    const PLUGIN_WEBSITE = '';

    static function bootstrap($pluginFile) {
        parent::bootstrap($pluginFile);

        new helper\BuddyPressAddonRequirements(static::getPluginFile(), static::getPluginName($full = false), __CLASS__, function() {
            return class_exists(App::PARENT_NAMESPACE . '\App');
        }, static::BASE_PLUGIN_NAME, static::BASE_PLUGIN_PURCHASE_URL);

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
        wp_register_style('cmmrm-buddypress-frontend', static::url('asset/css/frontend.css'), array(), static::VERSION);
        wp_register_script('cmmrm-buddypress-frontend', static::url('asset/js/frontend.js'), array('jquery'), static::VERSION, true);
    }

    static function admin_menu() {
        parent::admin_menu();
    }

    static function activatePlugin() {
        parent::activatePlugin();
    }

}