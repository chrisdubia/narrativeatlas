<?php

/*
 * Plugin Name: Say What? Pro
 * Plugin URI: https://plugins.leewillis.co.uk/downloads/say-what-pro
 * Description: An easy-to-use plugin that allows you to alter strings on your site without editing WordPress core, or plugin code.
 * Version: 5.7.4
 * Requires PHP: 7.3.0
 * Author: Ademti Software Ltd.
 * Author URI: https://www.ademti-software.co.uk/
 * Text Domain: say_what
 * Ademti Update ID: 9256
 * Ademti Purchase Link: https://plugins.leewillis.co.uk/downloads/say-what-pro/
 * Ademti Purchase Code: 15:V1BTQVlXSEFU
*/

/**
 * Copyright (c) 2015-2023 Ademti Software Ltd. // www.ademti-software.co.uk
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Version number for asset versioning
define( 'SAY_WHAT_PRO_VERSON', '5.7.4' );

// Database version number for tracking / making DB upgrades.
define( 'SAY_WHAT_PRO_DB_VERSION', 12 );

/**
 * Deactivate the plugin cleanly.
 */
function swp_plugin_deactivate() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	deactivate_plugins( plugin_basename( __FILE__ ) );
}

/**
 * Show an admin notice about PHP requirements.
 */
function swp_plugin_admin_notice_php() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="error"><p><strong>Say What? Pro</strong> requires PHP version 5.6 or above.</p></div>';
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

/**
 * Install function. Create the table to store the replacements
 */
function say_what_pro_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table_name = $wpdb->prefix . 'say_what_strings';
	$db_version = $wpdb->db_version();

	if ( version_compare( $db_version, '8.0.17', '<' ) ) {
		$key_def = 'string_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';
	} else {
		$key_def = 'string_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,';
	}
	$sql = "CREATE TABLE $table_name (
						 " . $key_def . '
						 orig_string text NOT NULL,
						 domain varchar(255),
						 replacement_string text,
						 context text,
						 lang varchar(20),
						 disabled boolean DEFAULT 0
						 ) DEFAULT CHARACTER SET utf8';
	dbDelta( $sql );

	$table_name = $wpdb->prefix . 'say_what_available_strings';
	if ( $wpdb->has_cap( 'utf8mb4' ) ) {
		$charset   = 'utf8mb4';
		$collation = 'utf8mb4_bin';
	} else {
		$charset   = 'utf8';
		$collation = 'utf8_bin';
	}
	$sql = "CREATE TABLE $table_name (
						orig_string text NOT NULL,
						domain varchar(255) NOT NULL,
						context text NOT NULL,
						translated_string text NOT NULL,
						orig_string_lc text NOT NULL,
						translated_string_lc text NOT NULL,
						UNIQUE KEY `arg_index` (`orig_string`(110),`domain`(24),`context`(32),`translated_string`(25))
						 ) DEFAULT CHARACTER SET=$charset COLLATE=$collation";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	$table_name = $wpdb->prefix . 'say_what_wildcards';
	if ( version_compare( $db_version, '8.0.17', '<' ) ) {
		$key_def = 'wildcard_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';
	} else {
		$key_def = 'wildcard_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,';
	}
	$sql = "CREATE TABLE $table_name (
						 " . $key_def . '
						 original text NOT NULL,
						 replacement text,
						 lang varchar(20)
						 ) DEFAULT CHARACTER SET utf8';
	dbDelta( $sql );
	update_option( 'say_what_pro_db_version', SAY_WHAT_PRO_DB_VERSION );
}

global $disable_say_what_replacements;
$disable_say_what_replacements = false;

if ( version_compare( phpversion(), '5.6', '<' ) ) {
	add_action( 'admin_init', 'swp_plugin_deactivate' );
	add_action( 'admin_notices', 'swp_plugin_admin_notice_php' );
} else {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
	register_activation_hook( __FILE__, 'say_what_pro_install' );
	require_once plugin_dir_path( __FILE__ ) . 'say-what-pro-bootstrap.php';
	new Ademti\WordPress\PluginUpdater(
		__FILE__,
		'say_what_pro',
		'https://plugins.leewillis.co.uk',
		'pblw',
		'https://plugins.leewillis.co.uk/support'
	);
}
