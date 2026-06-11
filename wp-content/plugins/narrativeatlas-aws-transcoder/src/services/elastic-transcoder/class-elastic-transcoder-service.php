<?php
/**
 * Elastic Transcoder Service Implementation
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Services
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Services\Elastic_Transcoder;

use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Aws\Exception\AwsException;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Narrativeatlas_AWS_Transcoder\Contracts\Transcoder_Service;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Elastic Transcoder Service implementation.
 *
 * @todo should we have ElasticTranscoder_Service instead of Elastic_Transcoder_Service?
 */
class Elastic_Transcoder_Service implements Transcoder_Service {

	/**
	 * Returns client.
	 *
	 * @param Item $item Item object.
	 *
	 * @return ElasticTranscoderClient|null
	 */
	public function get_client( Item $item ): ?ElasticTranscoderClient {

		static $client = null;

		if ( ! is_null( $client ) ) {
			return $client;
		}

		$credentials   = na_aws_get_offload_credentials();
		$bucket_region = na_aws_get_offload_bucket_region( $item );

		if ( empty( $credentials['access_key'] ) || empty( $credentials['secret_key'] ) || empty( $bucket_region ) ) {
			return null;
		}

		$client = new ElasticTranscoderClient(
			array(
				'credentials' => array(
					'key'    => $credentials['access_key'],
					'secret' => $credentials['secret_key'],
				),
				'region'      => $bucket_region,
				'version'     => 'latest',
			)
		);

		return $client;
	}

	/**
	 * Handles transcode request.
	 *
	 * @param Item $item Item object.
	 */
	public function transcode( Item $item ) {
		$client      = $this->get_client( $item );
		$pipeline_id = na_aws_transcoder_get_option( 'pipeline_id', '' );

		if ( ! $client || ! $pipeline_id ) {
			return false;
		}

		$aws_bucket_file_path = $item->path();

		if ( $item->private_prefix() ) {
			$aws_bucket_file_path = $item->private_prefix() . $aws_bucket_file_path;
		}

		$file_info = na_aws_transcoder_get_file_info( $aws_bucket_file_path );

		if ( empty( $file_info) || empty( $file_info['extension'] )) {
			return false;
		}

		// @todo should we need try catch need to confirm?.
		try {
			$job = $client->createJob(
				array(
					'PipelineId'   => $pipeline_id,
					'Inputs'       => array(
						array(
							'Key'         => $aws_bucket_file_path,
							'FrameRate'   => 'auto',
							'Resolution'  => 'auto',
							'AspectRatio' => 'auto',
							'Interlaced'  => 'auto',
							'Container'   => 'mp4',
						),
					),
					'Outputs'      => array(
						array(
							'Key'              => trailingslashit( $file_info['dirname'] ) . 'v-' . $file_info['filename'] . '.mp4',
							'ThumbnailPattern' => trailingslashit( $file_info['dirname'] ) . "{$file_info['filename']}-{count}",
							'Rotate'           => 'auto',
							'PresetId'         => '1351620000001-100070', // Web.
						),
					),
					'UserMetadata' => array(
						'as3cf_item_id'     => "{$item->id()}",
						'as3cf_source_type' => "{$item->source_type()}",
					),
				)
			)->get( 'Job' );

			if ( empty( $job['Id'] ) ) {
				error_log( 'AWS Transcoder Job Failed: ' . maybe_serialize( $job ) );
			} else {
				$pipeline_id = na_aws_transcoder_get_option( 'pipeline_id', '' );

				na_aws_insert_log(
					array(
						'attachment_id' => $item->source_id(),
						'source_type'   => $item->source_type(),
						'job_id'        => $job['Id'],
						'pipeline_id'   => $pipeline_id,
						'state'         => 'Submitted',
						'created_at'    => current_time( 'mysql' ),
					)
				);
			}
		} catch ( AwsException $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * Renders status.
	 *
	 * @param array $args Array of args.
	 *
	 * @return mixed
	 */
	public function status( array $args ) {
		// for status retrieval.
	}

	/**
	 * Handles transcoder complete event.
	 *
	 * @param array $response Response.
	 */
	public function on_complete( array $response ) {
		$as3cf_item = na_aws_get_offload_item( $response );

		// If attachment id not found or id is not video return.
		if ( ! $as3cf_item || empty( $response['outputs'] ) ) {
			return;
		}

		// We are transcoding only for mp4.
		$transcoded_video = current( $response['outputs'] );

		// Return if status not set or not complete.
		if ( ! isset( $transcoded_video['status'] ) || 'Complete' != $transcoded_video['status'] ) {
			return;
		}

		// update s3 row + meta
		// update attachment mime type+guid
		// update attachment metadata.
		$extra_info = array_merge(
			$as3cf_item->extra_info(),
			array(
				'objects' => array(
					$as3cf_item::primary_object_key() => array(
						'source_file' => wp_basename( $transcoded_video['key'] ),
					),
				),
			)
		);

		$item = na_aws_sync_offload_media_items_table(
			$transcoded_video['key'],
			$extra_info,
			$as3cf_item
		);

		// If not synchronized return.
		if ( ! $item ) {
			return;
		}

		$source_id = $as3cf_item->source_id();

		delete_post_meta( $source_id, '__aws_transcoding_state' );

		na_aws_sync_attachment_info(
			$item->source_path(),
			na_aws_get_meta_data( $transcoded_video, $as3cf_item ),
			$source_id
		);

		na_aws_insert_log(
			array(
				'job_id' => $response['jobId'],
				'state'  => 'Complete',
			)
		);

		$region = $as3cf_item->region();
		$bucket = $as3cf_item->bucket();

		global $as3cf;

		// Delete original file.
		$as3cf->get_provider_client( $region )->delete_objects(
			array(
				'Bucket' => $bucket,
				'Delete' => array(
					'Objects' => array(
						array( 'Key' => $response['input']['key'] )
					),
				),
			)
		);
	}

	/**
	 * Handles transcoder error event.
	 *
	 * @param array $response Response.
	 */
	public function on_error( array $response ) {
		$as3cf_item = na_aws_get_offload_item( $response );

		if ( ! $as3cf_item ) {
			return;
		}

		$attachment_id = $as3cf_item->source_id();

		$attempt = get_post_meta( $attachment_id, '__aws_transcoding_attempt', true );
		$attempt = $attempt ? $attempt : 1;

		if ( 2 >= $attempt ) {
			$this->transcode( $as3cf_item );

			update_post_meta( $as3cf_item->source_id(), '__aws_transcoding_attempt', $attempt + 1 );
		} else {
			delete_post_meta( $attachment_id, '__aws_transcoding_state' );

			na_aws_insert_log(
				array(
					'job_id' => $response['jobId'],
					'state'  => 'Error',
				)
			);
		}
	}
}
