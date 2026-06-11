<?php
/*
Plugin Name: CM Maps Routes Custom Fields Addon
Description: Allow users to add custom fields to the route page while using the CM Routes Manager plugin.
Author: CreativeMindsSolutions
Version: 1.2.4
*/

if (version_compare('5.3', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

require_once dirname(__FILE__) . '/App.php';
com\cminds\mapsroutesmanager\addon\customfields\App::bootstrap(__FILE__);