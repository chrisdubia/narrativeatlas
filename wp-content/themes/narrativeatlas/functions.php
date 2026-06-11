<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */
function buddyboss_theme_child_languages()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss-theme' instances in all child theme files to 'buddyboss-theme-child'.
  // load_theme_textdomain( 'buddyboss-theme-child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  // Styles
  wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri().'/assets/css/custom.css', '', '1.0.0' );

  // Javascript
  wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri().'/assets/js/custom.js', array( 'bp-nouveau-activity' ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here
/*function twf_remove_jquery_migrate( $scripts ) {
    if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
        $script = $scripts->registered['jquery'];

        if ( $script->deps ) { // Check whether the script has any dependencies
            $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
        }
    }
}

add_action( 'wp_default_scripts', 'twf_remove_jquery_migrate' );*/

add_action( 'init', 'blockusers_init' );
function blockusers_init() {
	if ( is_admin() && ! current_user_can( 'administrator' ) &&
	     ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		wp_redirect( home_url() );
		exit;
	}
}

/**
 * bbPress fix
 * Do not hide topics/replies from the user on their own profile.
 */
function buddydev_fix_profile_forums_show_hidden() {

	if ( has_action( 'pre_get_posts', 'bbp_pre_get_posts_normalize_forum_visibility' ) && bp_is_my_profile() && bp_is_current_component( 'forums' ) ) {
		remove_action( 'pre_get_posts', 'bbp_pre_get_posts_normalize_forum_visibility', 4 );
	}
}

add_action( 'bp_template_redirect', 'buddydev_fix_profile_forums_show_hidden' );

function buddydev_redirect_to_my_groups( $redirect_to, $requested_redirect_to, $user ) {
	return site_url( '/members/me/groups/' );
}

add_filter( 'login_redirect', 'buddydev_redirect_to_my_groups', 151, 3 );

// on logout, redirect to home.
/*
add_action( 'wp_logout', function () {
	wp_safe_redirect( home_url() );
	exit( 0 );
} );*/

/**
 * Modify Menu title
 *
 * @param string $title Title string.
 *
 * @return string
 */
function buddydev_modify_menu_title( $title ) {
	if ( strpos( $title, 'Choose your language' ) !== false ) {
		$title = '<img src="' . get_stylesheet_directory_uri() . '/images/change-language.svg" style="width: 2em;vertical-align: middle" />';
	}

	return $title;
}

add_filter('weglot_menu_parent_menu_item_title', 'buddydev_modify_menu_title' );

// Do not allow wegloat on home page for non logged and the login page.
add_filter( 'weglot_is_eligible_url', function ( $is ) {
	global $pagenow;

	if ( 'wp-login.php' == $pagenow || ( ! is_user_logged_in() && is_front_page() ) ) {
		$is = false;
	}

	return $is;
} );

// customize login message.
add_filter( 'bp_wp_login_error', function ( $mesasge ) {
	return __( 'Please login', 'buddyboss-theme-child' );
} );

add_action( 'login_enqueue_scripts', function (){
	wp_enqueue_style( 'lh-term-fix-bb', get_stylesheet_directory_uri() .'/assets/css/lh-terms-fix.css' );
});

// Change forum replies to descending order.
add_filter('bbp_before_has_replies_parse_args', 'buddyboss__change_replies_order');
function buddyboss__change_replies_order($r) {
		$r['order'] = 'DESC';

		return $r;
}