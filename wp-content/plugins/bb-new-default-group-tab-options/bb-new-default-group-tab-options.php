<?php
/*
 * Plugin Name:  BB New Default Group Tab Options
 * Description:  Currently provide discussions as a new default tab options in customizer group navigation.
 * Version:      1.0.0
 * Requires PHP: 7.4
 * Author:       BuddyDev
 * Author URI:   https://buddydev.com/
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

add_filter( 'group_default_tab_options_list', function ( $options ) {

	if ( ! is_array( $options ) || isset( $options['forums'] ) ) {
		return $options;
	}

	if ( bp_is_active( 'forums' ) ) {
		$options['forums'] = __( 'Discussions', 'nifty-new-default-group-tab-options' );
	}

	return $options;
} );

add_filter( 'bp_groups_default_extension', function ( $default_extension ) {

	if ( ! bp_is_group() ) {
		return $default_extension;
	}

	$default_tab = bp_nouveau_get_appearance_settings( 'group_default_tab' );

	if ( 'forums' !== $default_tab ) {
		return $default_extension;
	}

	if ( ! bp_is_active( 'forums' ) || ! bp_group_is_forum_enabled( groups_get_current_group() ) ) {
		// Set default tab to members. Because default extension to forums by BuddyBoss and will cause 404 page.
		return 'forums' === $default_extension ? 'members' : $default_extension;
	}

	return urlencode( get_option( '_bbp_forum_slug', 'forum' ) );
} );
