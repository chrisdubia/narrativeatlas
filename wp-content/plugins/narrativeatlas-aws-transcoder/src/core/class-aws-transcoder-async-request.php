<?php
/**
 * AWS transcoder async request
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Core;

use WP_Async_Request;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * AWS transcoder background process.
 */
class AWS_Transcoder_Async_Request extends WP_Async_Request {

	/**
	 * Queue action.
	 *
	 * @var string
	 */
	protected $action = 'aws_transcoding_request';

	/**
	 * Perform action on item here.
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	protected function handle() {
		$as3cf_item_id = empty( $_POST['id'] ) ? '' : $_POST['id'];
		$source_type   = empty( $_POST['source_type'] ) ? '' : $_POST['source_type'];

		if ( ! $as3cf_item_id || ! $source_type ) {
			return;
		}

		global $as3cfpro;

		if ( ! $as3cfpro || ! method_exists( $as3cfpro, 'get_source_type_class' ) ) {
			return;
		}

		$source_type_class = $as3cfpro->get_source_type_class( $source_type );

		if ( ! $source_type_class ) {
			return;
		}

		$as3cf_item = $source_type_class::get_by_id( $as3cf_item_id );

		if ( ! $as3cf_item ) {
			return;
		}

		na_aws_get_transcoder_service()->transcode( $as3cf_item );
	}
}
