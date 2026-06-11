<?php

use com\cminds\mapsroutesmanager\addon\buddypress\controller\SettingsController;

use com\cminds\mapsroutesmanager\addon\buddypress\App;

$cminds_plugin_config = array(
	'plugin-is-pro'				 => App::isPro(),
	'plugin-is-addon'			 => TRUE,
	'plugin-version'			 => App::VERSION,
	'plugin-abbrev'				 => App::PREFIX,
    'plugin-affiliate'               => '',
    'plugin-redirect-after-install'  => admin_url( 'admin.php?page=' . App::PARENT_PLUGIN_SETTINGS_MENU_SLUG ),
	'plugin-settings-url'		 => admin_url( 'admin.php?page=' . App::PARENT_PLUGIN_SETTINGS_MENU_SLUG ),
    'plugin-show-guide'              => FALSE,
    'plugin-guide-text'              => '',
    'plugin-guide-video-height'      => 240,
    'plugin-guide-videos'            => array(
//         array( 'title' => 'Installation tutorial', 'video_id' => '158514902' ),
    ),
	'plugin-parent-abbrev'		 => App::PARENT_PREFIX,
	'plugin-file'				 => App::getPluginFile(),
	'plugin-dir-path'			 => plugin_dir_path( App::getPluginFile() ),
	'plugin-dir-url'			 => plugin_dir_url( App::getPluginFile() ),
	'plugin-basename'			 => plugin_basename( App::getPluginFile() ),
	'plugin-icon'				 => '',
	'plugin-name'				 => App::getPluginName(true),
	'plugin-license-name'		 => App::getPluginName(true),
	'plugin-slug'				 => App::SLUG,
	'plugin-short-slug'			 => App::PREFIX,
	'plugin-parent-short-slug'	 => App::PARENT_PREFIX,
	'plugin-menu-item'			 => App::PARENT_MENU,
	'plugin-textdomain'			 => '',
	'plugin-userguide-key'		 => '',
	'plugin-store-url'			 => '',
	'plugin-support-url'		 => '',
	'plugin-review-url'			 => '',
	'plugin-changelog-url'		 => '',
	'plugin-licensing-aliases'	 => App::getLicenseAdditionalNames(),
   'plugin-compare-table'       => '',
);

