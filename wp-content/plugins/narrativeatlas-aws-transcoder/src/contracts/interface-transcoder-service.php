<?php
/**
 * Contract for transcoder service.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Contracts
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Contracts;

use DeliciousBrains\WP_Offload_Media\Items\Item;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Transcoder Service Interface.
 */
interface Transcoder_Service {

	/**
	 * Returns transcoder service client.
	 *
	 * @param Item $item Item object.
	 *
	 * @return mixed
	 */
	public function get_client( Item $item );

	/**
	 * Handles media for sending it to transcoding service e.q. ElasticTranscoder, MediaConvert.
	 *
	 * @param Item $item Item object.
	 */
	public function transcode( Item $item );

	/**
	 * Renders status
	 *
	 * @param array $args Args to get status.
	 *
	 * @return mixed
	 */
	public function status( array $args );

	/**
	 * Handles complete state by transcoding service.
	 *
	 * @param array $response Response from transcoding service.
	 */
	public function on_complete( array $response );

	/**
	 * Handles error state by transcoding service.
	 *
	 * @param array $response Response from transcoding service.
	 */
	public function on_error( array $response );
}
