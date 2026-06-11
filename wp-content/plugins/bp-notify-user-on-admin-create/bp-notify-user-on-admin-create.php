<?php
/**
 * Plugin Name: BuddyPress Notify User On Admin Create
 * Version: 1.0.0
 * Plugin URI: https://buddydev.com/plugins/bp-notify-user-on-admin-create
 * Description: Allows site admin to modify new user email content send to user when created from backend.
 * Author: BuddyDev
 * Author URI: https://buddydev.com/
 * Requires PHP: 5.3
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  bp-notify-user-on-admin-create
 * Domain Path:  /languages
 **/

// No direct access over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BP_Notify_User_On_Admin_Create
 */
class BP_Notify_User_On_Admin_Create {

	/**
	 * Class instance
	 *
	 * @var BP_Notify_User_On_Admin_Create
	 */
	private static $instance = null;


	/**
	 * BP_Notify_User_On_Admin_Create constructor.
	 */
	private function __construct() {
		$this->setup();
	}

	/**
	 * Get Singleton Instance
	 *
	 * @return BP_Notify_User_On_Admin_Create
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Callbacks to hooks
	 */
	private function setup() {
		register_activation_hook( __FILE__, array( $this, 'on_activation' ) );

		add_action( 'user_register', array( $this, 'on_register' ) );
	}

	/**
	 * On register
	 */
	public function on_register() {

		if ( ! function_exists( 'buddypress' ) || ! isset( $_POST['send_user_notification'] ) ) {
			return;
		}

		remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications' );
		add_action( 'edit_user_created_user', array( $this, 'notify_user' ) );
	}

	/**
	 * Notify user
	 *
	 * @param int $user_id User id.
	 */
	public function notify_user( $user_id ) {
		global $wpdb, $wp_hasher;

		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );

		/** This action is documented in wp-login.php */
		do_action( 'retrieve_password_key', $user->user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

		$switched_locale = switch_to_locale( get_user_locale( $user ) );

		$activation_url = '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";

		$email_args = array(
			'tokens' => array(
				'site.name'       => $blogname,
				'user.user_login' => $user->user_login,
				'user.name'       => $user->display_name ? $user->display_name : $user->first_name,
				'activation.url'  => $activation_url,
				'login.url'       => wp_login_url(),
			),
		);

		bp_send_email( 'admin_user_register', $user_id, $email_args );

		if ( $switched_locale ) {
			restore_previous_locale();
		}
	}

	/**
	 * On activation create table
	 */
	public function on_activation() {
		$this->register_email_type();
	}

	/**
	 * On deactivation. Do cleanup if needed.{{visitor.username}} visited your profile
	 */
	public function on_deactivation() {
		// do cleanup.
	}

	/**{{visitor.username}} visited your profile
	 * Register email type when profile visited
	 */
	private function register_email_type() {
		$post_title = __( '{{user.name}} Your username and password info', 'bp-notify-user-on-admin-create' );

		$post_exists = post_exists( $post_title );

		if ( $post_exists != 0 && get_post_status( $post_exists ) == 'publish' ) {
			return;
		}

		$post_content = __( 'Hi {{user.name}}
		Username: {{user.user_login}} 
       To set your password, visit the following address: 
       {{activation.url}}
       Login url: {{login.url}}
    ', 'bp-notify-user-on-admin-create' );

		$my_post = array(
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_excerpt' => $post_content,
			'post_status'  => 'publish',
			'post_type'    => bp_get_email_post_type(),
		);

		$post_id = wp_insert_post( $my_post );

		if ( $post_id ) {
			$tt_ids = wp_set_object_terms( $post_id, 'admin_user_register', bp_get_email_tax_type() );

			foreach ( $tt_ids as $tt_id ) {
				$term = get_term_by( 'term_taxonomy_id', (int) $tt_id, bp_get_email_tax_type() );
				wp_update_term(
					(int) $term->term_id,
					bp_get_email_tax_type(),
					array(
						'description' => __( 'User notification email when a user is created in admin dashboard(from Users->Add New)', 'bp-notify-user-on-admin-create' ),
					)
				);
			}
		}
	}
}

BP_Notify_User_On_Admin_Create::get_instance();
