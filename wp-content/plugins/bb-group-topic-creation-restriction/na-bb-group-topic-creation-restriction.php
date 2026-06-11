<?php
/**
 * Plugin Name:       BB Group Topic Creation Restriction
 * Description:       It allows group admins to select who can create topics in their groups.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            BuddyDev
 * Author URI:        https://buddydev.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bb-group-topic-creation-restriction
 * Domain Path:       /languages
 */

use Narrativeatlas\BB_Prestrictions\Forum_Restrictions_Group_Extension;
use Narrativeatlas\BB_Prestrictions\Forum_Topic_Permissions_Checker;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class NA_BB_Group_Topic_Creation_Restriction {

	/**
	 * Holds class singleton instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->setup();
	}

	/**
	 * Creates and Returns singleton instance of the class.
	 *
	 * @return self
	 */
	private static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns class singleton instance
	 *
	 * @return self
	 */
	public static function boot() {
		return self::get_instance();
	}

	/**
	 * Hooks to action
	 */
	private function setup() {
		$this->path = plugin_dir_path( __FILE__ );

		add_action( 'bp_loaded', array( $this, 'load' ) );
		add_action( 'bp_init', array( $this, 'load_translations' ), 0 );
		add_action( 'bp_init', array( $this, 'register_extension' ) );
	}

	/**
	 * Loads plugin other files.
	 */
	public function load() {

		if ( ! $this->is_group_forum_enabled() ) {
			return;
		}

		require_once $this->path . 'class-forum-topic-permissions-checker.php';

		( new Forum_Topic_Permissions_Checker() )->setup();
	}

	/**
	 * Loads plugin translations
	 */
	public function load_translations() {

		if ( $this->is_group_forum_enabled() ) {
			load_plugin_textdomain(
				'bb-group-topic-creation-restriction',
				false,
				plugin_basename( __FILE__ )
			);
		}
	}

	/**
	 * Register group extension.
	 */
	public function register_extension() {

		if ( $this->is_group_forum_enabled() ) {
			require_once $this->path . 'class-forum-restrictions-group-extension.php';

		}
		bp_register_group_extension( Forum_Restrictions_Group_Extension::class );
	}

	/**
	 * Is group forum enabled?
	 *
	 * @return bool
	 */
	private function is_group_forum_enabled() {
		return bp_is_active( 'groups' ) && bbp_is_group_forums_active();
	}
}

NA_BB_Group_Topic_Creation_Restriction::boot();
