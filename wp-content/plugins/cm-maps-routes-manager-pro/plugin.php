<?php
/*
Plugin Name: CM Maps Routes Manager Pro
Plugin URI: https://www.cminds.com/wordpress-plugins-library/google-maps-routes-manager-plugin-for-wordpress-by-creativeminds
Description: Allow users to draw routes and to generate a catalog of map routes and trails
Author: CreativeMindsSolutions
Version: 5.1.2
*/

if (version_compare('5.3', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

require_once dirname(__FILE__) . '/App.php';
com\cminds\mapsroutesmanager\App::bootstrap(__FILE__);

/* GDPR */
include_once plugin_dir_path(__FILE__).'gdpr/cm-gdpr.php';
include_once plugin_dir_path(__FILE__).'gdpr/cm-gdpr-config.php';
$GLOBALS['CMMRM_Gdpr'] = new CMMRMGdpr($cmmrmgdpr_config);