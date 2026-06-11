<?php
/**
 * Plugin Name:  Narrativeatlas AWS Transcoder
 * Version:      1.0.0
 * Plugin URI:   https://buddydev.com/plugins/narrativeatlas-aws-transcoder
 * Description:  This plugin is an addon for WP Offload Media. It uses AWS services to transcode uploaded video files.
 * Author:       BuddyDev
 * Author URI:   https://buddydev.com/
 * Requires PHP: 5.3
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  narrativeatlas-aws-transcoder
 * Domain Path:  /languages
 *
 * @package narrativeatlas-aws-transcoder
 **/

use WP_Async_Request;
use Narrativeatlas_AWS_Transcoder\Bootstrap\Autoloader;
use Narrativeatlas_AWS_Transcoder\Bootstrap\Bootstrapper;
use Narrativeatlas_AWS_Transcoder\Schema\Schema;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Class Narrativeatlas_AWS_Transcoder
 *
 * @property-read string $path     Absolute path to the plugin directory.
 * @property-read string $url      Absolute url to the plugin directory.
 * @property-read string $basename Plugin base name.
 * @property-read string $version  Plugin version.
 */
class Narrativeatlas_AWS_Transcoder {

	/**
	 * Plugin Version.
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Class instance.
	 *
	 * @var Narrativeatlas_AWS_Transcoder
	 */
	private static $instance = null;

	/**
	 * Plugin absolute directory path.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Plugin absolute directory url.
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
	 * Async request object.
	 *
	 * @var null|WP_Async_Request
	 */
	public $async_request = null;

	/**
	 * Protected properties. These properties are inaccessible via magic method.
	 *
	 * @var array
	 */
	private $guarded = array( 'instance' );

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->bootstrap();
	}

	/**
	 * Returns Class Singleton Instance.
	 *
	 * @return Narrativeatlas_AWS_Transcoder
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

		// Load vendor autoloader.
		require_once $this->path . 'vendor/autoload.php';

		// Load autoloader.
		require_once $this->path . 'src/bootstrap/class-autoloader.php';

		$autoloader = new Autoloader( 'Narrativeatlas_AWS_Transcoder\\', __DIR__ . '/src/' );

		spl_autoload_register( $autoloader );

		register_activation_hook( __FILE__, array( $this, 'on_activation' ) );

		Bootstrapper::boot();
	}

	/**
	 * Creates table on activation.
	 */
	public function on_activation() {
		Schema::create();
	}

	/**
	 * Magic method for accessing property as readonly(It's a lie, references can be updated).
	 *
	 * @param string $name property name.
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {

		if ( ! in_array( $name, $this->guarded, true ) && property_exists( $this, $name ) ) {
			return $this->{$name};
		}

		return null;
	}
}

/**
 * Helper to access singleton instance.
 *
 * @return Narrativeatlas_AWS_Transcoder
 */
function narrative_aws_transcoder() {
	return Narrativeatlas_AWS_Transcoder::get_instance();
}

narrative_aws_transcoder();

