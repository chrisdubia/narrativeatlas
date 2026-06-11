<?php
/**
 * Bootstrapper. Initializes the plugin.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Bootstrap;

use Narrativeatlas_AWS_Transcoder\Admin\Admin_Settings;
use Narrativeatlas_AWS_Transcoder\Admin\AWS_Log_UI_Helper;
use Narrativeatlas_AWS_Transcoder\Core\AWS_Transcoder_Notifier;
use Narrativeatlas_AWS_Transcoder\Core\AWS_Log_Table_Synchronizer;
use Narrativeatlas_AWS_Transcoder\Core\AWS_Transcoder_Async_Request;
use Narrativeatlas_AWS_Transcoder\Core\AWS_Transcode_Job_Creator;
use Narrativeatlas_AWS_Transcoder\Core\AWS_Transcoding_State_Handler;
use Narrativeatlas_AWS_Transcoder\Core\BBP_Actions_Handler;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Bootstrapper.
 */
class Bootstrapper {

	/**
	 * Setup the bootstrapper.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Binds hooks.
	 */
	private function setup() {
		add_action( 'plugins_loaded', array( $this, 'setup_async_request' ) );
		add_action( 'plugins_loaded', array( $this, 'load_admin' ), 9996 );
		add_action( 'as3cf_pro_init', array( $this, 'on_init' ), 15 );
		add_action( 'bp_init', array( $this, 'load_translations' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Initializes async request.
	 */
	public function setup_async_request() {
		narrative_aws_transcoder()->async_request = new AWS_Transcoder_Async_Request();
	}

	/**
	 * Loads plugin admin section.
	 */
	public function load_admin() {

		if ( ! function_exists( 'as3cf_pro_init' ) ) {
			return;
		}

		if ( is_admin() && ! wp_doing_ajax() ) {
			require_once narrative_aws_transcoder()->path . 'src/admin/pt-settings/pt-settings-loader.php';

			Admin_Settings::boot();
			AWS_Log_UI_Helper::boot();
		}
	}

	/**
	 * Initializes classes and load plugin functions file.
	 */
	public function on_init() {
		$path = narrative_aws_transcoder()->path;

		require_once $path . 'src/core/aws-transcoder-functions.php';

		AWS_Transcoder_Notifier::boot();
		AWS_Transcode_Job_Creator::boot();
		AWS_Transcoding_State_Handler::boot();
		AWS_Log_Table_Synchronizer::boot();
		// @todo thought on it BuddyBoss does not handle media deletion on topic and reply delete so I implemented by own.
		BBP_Actions_Handler::boot();
	}

	/**
	 * Load translations.
	 */
	public function load_translations() {
		load_plugin_textdomain(
			'narrativeatlas-aws-transcoder',
			false,
			basename( narrative_aws_transcoder()->path ) . '/languages'
		);
	}

	/**
	 * Adds admin notices
	 */
	public function admin_notices() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow && ! function_exists( 'as3cf_pro_init' ) ) {
			wp_admin_notice(
				esc_html__( 'Narrativeatlas AWS Transcoder Plugin needs WP Offload Media plugin by Delicious Brains. Please install it.', 'narrativeatlas-aws-transcoder' ),
				array(
					'type'        => 'warning',
					'dismissible' => false,
				)
			);
		}
	}
}

