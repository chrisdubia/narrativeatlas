<?php
/**
 * AWS transcode job creator
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Core;

use DeliciousBrains\WP_Offload_Media\Items\Item;
use WP_Error;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Transcoding job creator.
 */
class AWS_Transcode_Job_Creator {

	/**
	 * Class instance.
	 *
	 * @var null|AWS_Transcode_Job_Creator
	 */
	private static $instance = null;

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->setup();
	}

	/**
	 * Returns and Initialize job creator.
	 *
	 * @return AWS_Transcode_Job_Creator
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns and Initialize job creator.
	 *
	 * @return AWS_Transcode_Job_Creator
	 */
	public static function boot() {
		return self::get_instance();
	}

	/**
	 * Callbacks to actions.
	 */
	private function setup() {
		// Before remove local file.
		add_action( 'as3cf_post_handle_item_upload', array( $this, 'on_upload' ), 9, 3 );
	}

	/**
	 * On item upload.
	 *
	 * @param bool|WP_Error $result     Result.
	 * @param Item          $as3cf_item Item which is uploaded.
	 * @param array         $options    Options
	 */
	public function on_upload( $result, $as3cf_item, $options ) {

		if ( is_wp_error( $result ) || empty( $as3cf_item ) || ! method_exists( $as3cf_item, 'provider' ) || 'aws' != $as3cf_item->provider() ) {
			return;
		}

		$aws_file_url  = $as3cf_item->get_provider_url();
		$attachment_id = $as3cf_item->source_id();

		if ( ! $aws_file_url || is_wp_error( $aws_file_url ) || ! wp_attachment_is( 'video', $attachment_id ) ) {
			return;
		}

		// Old implementation code.
		update_post_meta( $attachment_id, '__aws_transcoding_state', 'processing' );

		$data = array(
			'id'          => $as3cf_item->id(),
			'source_type' => $as3cf_item->source_type(),
		);

		//@todo use ids to make sure the data is serializable.
		narrative_aws_transcoder()->async_request->data( $data )->dispatch();
	}
}
