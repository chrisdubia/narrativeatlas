<?php
/**
 * Plugin Name:  User Insights Forum Participation
 * Version:      1.0.0
 * Description:  It is an addon for User Insights plugin allows site admin to filter users based on BuddyPress group forum participation.
 * Author:       BuddyDev
 * Author URI:   https://buddydev.com/
 * Requires PHP: 7.4
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  user-insights-forum-participation
 * Domain Path:  /languages
 *
 * @package user-insights-forum-participation
 **/

use User_Insights_Forum_Participation\Bootstrap\Autoloader;
use User_Insights_Forum_Participation\Bootstrap\Bootstrapper;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Class User_Insights_Forum_Participation
 *
 * @property-read string $path     Absolute path to the plugin directory.
 * @property-read string $url      Absolute url to the plugin directory.
 * @property-read string $basename Plugin base name.
 * @property-read string $version  Plugin version.
 */
class User_Insights_Forum_Participation {

	/**
	 * Plugin Version.
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Class instance
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Plugin absolute directory path
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Plugin absolute directory url
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin Basename.
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Protected properties. These properties are inaccessible via magic method.
	 *
	 * @var array
	 */
	private $guarded = array( 'instance' );

	/**
	 * User_Insights_Forum_Participation constructor.
	 */
	private function __construct() {
		$this->bootstrap();
	}

	/**
	 * Retrieves Singleton Instance
	 *
	 * @return self
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Bootstrap the core.
	 */
	private function bootstrap() {
		$this->path     = plugin_dir_path( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );

		// Load autoloader.
		require_once $this->path . 'src/bootstrap/class-autoloader.php';

		$autoloader = new Autoloader( 'User_Insights_Forum_Participation\\', __DIR__ . '/src/' );

		spl_autoload_register( $autoloader );

		// Drop tables on uninstall.
		// register_uninstall_hook( __FILE__, array( 'Schema', 'drop' ) );.

		Bootstrapper::boot();
	}

	/**
	 * Magic method for accessing property as readonly(It's a lie, references can be updated).
	 *
	 * @param string $name property name.
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {

		if ( property_exists( $this, $name ) && ! in_array( $name, $this->guarded, true ) ) {
			return $this->{$name};
		}

		return null;
	}
}

/**
 * Helper to access singleton instance
 *
 * @return User_Insights_Forum_Participation
 */
function user_insights_forum_participation() {
	return User_Insights_Forum_Participation::get_instance();
}

user_insights_forum_participation();

