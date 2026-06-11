<?php
/**
 * Media Convert Service implementation
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Services\Media_Convert
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Services\Media_Convert;

use Exception;
use Aws\MediaConvert\MediaConvertClient;
use Aws\Exception\AwsException;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Narrativeatlas_AWS_Transcoder\Contracts\Transcoder_Service;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Media Convert Service implementation.
 */
class Media_Convert_Service implements Transcoder_Service {

	/**
	 * Returns MediaConvert Client.
	 *
	 * @param Item $item
	 *
	 * @return MediaConvertClient|null
	 */
	public function get_client( Item $item ): ?MediaConvertClient {

		static $client = null;

		if ( ! is_null( $client ) ) {
			return $client;
		}

		$credentials   = na_aws_get_offload_credentials();
		$bucket_region = na_aws_get_offload_bucket_region( $item );

		if ( empty( $credentials['access_key'] ) || empty( $credentials['secret_key'] ) || empty( $bucket_region ) ) {
			return null;
		}

		try {
			$client = new MediaConvertClient(
				array(
					'version'     => 'latest',
					'region'      => $bucket_region,
					'credentials' => array(
						'key'    => $credentials['access_key'],
						'secret' => $credentials['secret_key'],
					),
					'retries'     => absint( na_aws_transcoder_get_option( 'media_convert_retries', 3 ) ),
				)
			);
		} catch ( AwsException $e ) {
			error_log( 'Error creating MediaConvertClient: ' . $e->getMessage() );
		}

		return $client;
	}

	/**
	 * Creates transcoding job on mediaconvert.
	 *
	 * @param Item $item Item object.
	 */
	public function transcode( Item $item ) {
		$client     = $this->get_client( $item );
		$account_id = na_aws_get_aws_account_id( $item );

		if ( ! $client || ! $account_id || empty( na_aws_transcoder_get_enabled_resolution() ) ) {
			error_log( 'Problem with making Client, Account id or enabled resolution' );
			return false;
		}

		try {
			$aws_bucket_file_path = $item->path();

			if ( $item->private_prefix() ) {
				$aws_bucket_file_path = $item->private_prefix() . $aws_bucket_file_path;
			}

			// Map to s3 bucket path.
			$aws_bucket_file_path = "s3://{$item->bucket()}/{$aws_bucket_file_path}";

			$file_group_settings = array( 'Destination' => trailingslashit( dirname( $aws_bucket_file_path ) ) );

			// @todo should we remove it?
			/*if ( $item->is_private() ) {
				$file_group_settings['DestinationSettings']['S3Settings']['AccessControl']['CannedAcl'] = 'PRIVATE';
			} else {
				$file_group_settings['DestinationSettings']['S3Settings']['AccessControl']['CannedAcl'] = 'PUBLIC_READ';
			}*/

			$job_args = array(
				'Role'         => "arn:aws:iam::{$account_id}:role/service-role/MediaConvert_Default_Role",
				'Settings'     => array(
					'OutputGroups' => array(
						array(
							'Name'                => 'File Group',
							'OutputGroupSettings' => array(
								'Type'              => 'FILE_GROUP_SETTINGS',
								'FileGroupSettings' => $file_group_settings,
							),
							'Outputs'             => $this->get_outputs(),
						)
					),
					'Inputs'       => array(
						array(
							'FileInput'      => $aws_bucket_file_path,
							'AudioSelectors' => array(
								'Audio Selector 1' => array(
									'DefaultSelection' => 'DEFAULT'
								)
							),
							'VideoSelector'  => array(),
							'TimecodeSource' => 'ZEROBASED',
						)
					),
				),
				'UserMetadata' => array(
					'as3cf_item_id'     => $item->id(),
					'as3cf_source_type' => $item->source_type(),
					'file_input'        => $item->path(), // to delete based on setting.
				),
			);

			$job = $client->createJob( $job_args );

			// Only execute if create job executed successfully.
			na_aws_insert_log(
				array(
					'attachment_id' => $item->source_id(),
					'source_type'   => $item->source_type(),
					'job_id'        => $job['Job']['Id'],
					'pipeline_id'   => $job['Job']['Queue'],
					'state'         => 'SUBMITTED',
					'created_at'    => current_time( 'mysql' ),
				)
			);
		} catch ( AwsException $e ) {
			error_log( "Error creating MediaConvert job: " . $e->getMessage() );
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

		if ( empty( $args['job_id'] || empty( $args['attachment_id'] ) || empty( $args['source_type'] ) ) ) {
			esc_html_e( 'Invalid request!', 'narrativeatlas-aws-transcoder' );

			return;
		}

		global $as3cf;

		if ( ! $as3cf || ! method_exists( $as3cf, 'get_source_type_class' ) ) {
			echo sprintf( '<p>%s</p>', __( 'Invalid request!', 'narrativeatlas-aws-transcoder' ) );

			return;
		}

		$class = $as3cf->get_source_type_class( $args['source_type'] );

		if ( ! $class ) {
			return null;
		}

		$item = $class::get_by_source_id( $args['attachment_id'] );

		if ( empty( $item ) ) {
			echo sprintf( '<p>%s</p>', __( 'Item not found!', 'narrativeatlas-aws-transcoder' ) );

			return;
		}

		try {
			$client = $this->get_client( $item );

			$result = $client->getJob(
				array(
					'Id' => $args['job_id']
				)
			);

			?>
            <div class="na-aws-job-status-info">
                <table class="wp-list-table widefat fixed striped table-view-list">
					<?php if ( isset( $result['Job']['CreatedAt'] ) ) : ?>
                        <tr>
                            <td><?php esc_html_e( 'Created At', 'narrativeatlas-aws-transcoder' ); ?></td>
                            <td><?php echo mysql2date( 'g:i:s A, F j, Y', $result['Job']['CreatedAt'] ); ?></td>
                        </tr>
					<?php endif; ?>
					<?php if ( isset( $result['Job']['Queue'] ) ) : ?>
                        <tr>
                            <td><?php esc_html_e( 'Queue', 'narrativeatlas-aws-transcoder' ); ?></td>
                            <td><?php echo esc_html( $result['Job']['Queue'] ); ?></td>
                        </tr>
					<?php endif; ?>
					<?php if ( isset( $result['Job']['Status'] ) ) : ?>
                        <tr>
                            <td><?php esc_html_e( 'Status', 'narrativeatlas-aws-transcoder' ); ?></td>
                            <td><?php echo esc_html( $result['Job']['Status'] ); ?></td>
                        </tr>
					<?php endif; ?>
					<?php if ( isset( $result['Job']['CurrentPhase'] ) ) : ?>
                        <tr>
                            <td><?php esc_html_e( 'CurrentPhase', 'narrativeatlas-aws-transcoder' ); ?></td>
                            <td><?php echo esc_html( $result['Job']['CurrentPhase'] ); ?></td>
                        </tr>
					<?php endif; ?>
					<?php if ( isset( $result['Job']['RetryCount'] ) ) : ?>
                        <tr>
                            <td><?php esc_html_e( 'No. Of Retries', 'narrativeatlas-aws-transcoder' ); ?></td>
                            <td><?php echo esc_html( $result['Job']['RetryCount'] ); ?></td>
                        </tr>
					<?php endif; ?>
                </table>
            </div>
			<?php

		} catch ( AwsException $e ) {
			echo sprintf( '<p>%s</p>', esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Handles complete event by transcoding service.
	 *
	 * @param array $response Response by transcoding service.
	 */
	public function on_complete( array $response ) {

		if ( ! isset( $response['detail']['userMetadata']['as3cf_item_id'] )
		     || ! isset( $response['detail']['userMetadata']['as3cf_source_type'] )
		) {
			error_log( 'Item id and source type missing' );

			return;
		}

		if ( empty( $response['detail']['outputGroupDetails'] )	) {
			error_log( 'Output group detail are empty' );

			return;
		}

		$output_group_details = current( $response['detail']['outputGroupDetails'] );

		if ( empty( $output_group_details['outputDetails'] ) ) {
			error_log( 'Output details are empty' );

			return;
		}

		// sync offload table info.
		// sync transcoder log info.
		// flush clout front cache of offload media.
		$item = na_aws_get_offload_item(
			array(
				'userMetadata' => array(
					'as3cf_item_id'     => $response['detail']['userMetadata']['as3cf_item_id'],
					'as3cf_source_type' => $response['detail']['userMetadata']['as3cf_source_type'],
				),
			)
		);

		if ( ! $item ) {
			error_log( 'Item not found' );
			return;
		}

		$parsed_info = $this->get_parsed_info( $output_group_details['outputDetails'], $item );

		if ( empty( $parsed_info['video_to_sync'] ) ) {
			error_log( 'File for synchronization not found' );

			return;
		}

		$item = na_aws_sync_offload_media_items_table(
			$parsed_info['video_to_sync'],
			array( 'objects' => $parsed_info['extra_info'] ),
			$item
		);

		if ( ! $item ) {
			error_log( 'Error file in video synchronization' );

			return;
		}

        $source_id = $item->source_id();

        delete_post_meta( $source_id, '__aws_transcoding_state' );

		$meta_data = na_aws_get_meta_data( $parsed_info['video_metadata'], $item );

		if ( empty( $meta_data['custom_sizes'] ) ) {
			$meta_data['custom_sizes'] = $parsed_info['extra_info'];
		}

		na_aws_sync_attachment_info( $item->source_path(), $meta_data, $source_id );

		na_aws_insert_log(
			array(
				'job_id' => $response['detail']['jobId'],
				'state'  => 'COMPLETE',
			)
		);

		if ( ! empty( $response['detail']['userMetadata']['file_input'] )
		     && na_aws_transcoder_get_option( 'delete_uploaded_file', 1 )
		) {
			$this->delete_original_file( $response['detail']['userMetadata']['file_input'], $item );
		}
	}

	/**
	 * Handles error event by transcoding service.
	 *
	 * @param array $response Response by transcoding service.
	 */
	public function on_error( array $response ) {

		if ( ! isset( $response['detail']['userMetadata']['as3cf_item_id'] )
		     || ! isset( $response['detail']['userMetadata']['as3cf_source_type'] )
		) {
			error_log( 'Item id and source type missing' );

			return;
		}

		$item = na_aws_get_offload_item(
			array(
				'userMetadata' => array(
					'as3cf_item_id'     => $response['detail']['userMetadata']['as3cf_item_id'],
					'as3cf_source_type' => $response['detail']['userMetadata']['as3cf_source_type'],
				),
			)
		);

		if ( ! $item ) {
			error_log( 'Item not found' );

			return;
		}

        delete_post_meta( $item->source_id(), '__aws_transcoding_state' );

		na_aws_insert_log(
			array(
				'job_id' => $response['detail']['jobId'],
				'state'  => 'ERROR',
			)
		);
	}

	/**
	 * Returns video resolutions.
	 *
	 * @return array[]
	 */
	private function get_outputs(): array {
		$container_settings = array(
			'Container'   => 'MP4',
			'Mp4Settings' => array(),
		);

		$audio_settings = array(
			array(
				'CodecSettings' => array(
					'Codec'       => 'AAC',
					'AacSettings' => array(
						'Bitrate'    => 96000,
						'CodingMode' => 'CODING_MODE_2_0',
						'SampleRate' => 48000,
					),
				),
			)
		);

		$outputs = array();
		foreach ( na_aws_get_video_resolutions() as $resolution => $detail ) {

			if ( ! na_aws_is_resolution_enabled( $resolution ) ) {
				continue;
			}

			$video_description = array(
				'CodecSettings' => array(
					'Codec'        => 'H_264',
					'H264Settings' => array(
						'MaxBitrate'        => $detail['bitrate'],
						'RateControlMode'   => 'QVBR',
						'SceneChangeDetect' => 'TRANSITION_DETECTION',
					),
				),
			);

			if ( ! empty( $detail['width'] ) && ! empty( $detail['height'] ) ) {
				$video_description['Width']  = $detail['width'];
				$video_description['Height'] = $detail['height'];
			}

			$outputs[] = array(
				'ContainerSettings' => $container_settings,
				'VideoDescription'  => $video_description,
				'AudioDescriptions' => $audio_settings,
				'NameModifier'      => "-{$resolution}",
			);
		}

		return $outputs;
	}

	/**
	 * Returns extra info for offload media.
	 *
	 * @param array $output_details Output details.
	 * @param Item  $item Item object.
	 *
	 * @return array {
	 *      @type string $video_to_sync  File to be synchronized.
	 *      @type array  $video_metadata Video metadata.
	 *      @type array  $extra_info     Extra info to be saved with offload item.
	 * }
	 */
	private function get_parsed_info( array $output_details, Item $item ): array {
		$resolutions = na_aws_get_video_resolutions();

		$video_to_sync  = '';
		$video_metadata = array(); // will be saved in postmeta table.
		$extra_info     = array(); // will be saved in offload media items table.
		foreach ( $resolutions as $resolution => $detail ) {

			// Continue if resolution is not enabled.
			if ( ! na_aws_is_resolution_enabled( $resolution ) ) {
				continue;
			}

			foreach ( $output_details as $output_detail ) {
				$file_path     = current( $output_detail['outputFilePaths'] );
				$file_basename = $file_path ? wp_basename( $file_path ) : '';

				// Skip if empty file basename of file basename do not have current resolution as suffix.
				if ( ! $file_basename || ! $this->has_suffix( $file_basename, $resolution ) ) {
					continue;
				}

				$file_type = wp_check_filetype( $file_basename );

				$extra_info[ $resolution ] = array(
					'source_file' => $file_basename,
					'is_private'  => $item->is_private(),
					'width'       => $output_detail['videoDetails']['widthInPx'],
					'height'      => $output_detail['videoDetails']['heightInPx'],
					'duration'    => $output_detail['durationInMs'],
					'mime_type'   => $file_type['type'],
				);

				if ( $resolution === na_aws_transcoder_get_default_resolution() ) {
					$video_to_sync              = $file_path;
					$video_metadata['width']    = $output_detail['videoDetails']['widthInPx'];
					$video_metadata['height']   = $output_detail['videoDetails']['heightInPx'];
					$video_metadata['duration'] = $output_detail['durationInMs'];

					// Set the primary object as default resolution.
					$extra_info[ $item::primary_object_key() ] = $extra_info[ $resolution ];
				}
			}
		}

		return array(
			'video_to_sync'  => $video_to_sync,
			'video_metadata' => $video_metadata,
			'extra_info'     => $extra_info,
		);
	}

	/**
	 * Checks if file has suffix.
	 *
	 * @param string $file_basename File basename.
	 * @param string $suffix        Suffix to check.
	 *
	 * @return bool
	 */
	private function has_suffix( string $file_basename, string $suffix ): bool {
		// Build a regex to match the suffix before the extension
		$pattern = '/' . preg_quote( $suffix, '/' ) . '\.(mp4)$/i';

		return preg_match( $pattern, $file_basename ) === 1;
	}

	/**
	 * Deletes original file based on setting.
	 *
	 * @param string $file File path to delete.
	 * @param Item   $item Item object.
	 */
	private function delete_original_file( string $file, Item $item ) {
		global $as3cf;

		try {
			// Delete original file.
			$as3cf->get_provider_client( $item->region() )->delete_objects(
				array(
					'Bucket' => $item->bucket(),
					'Delete' => array(
						'Objects' => array(
							array( 'Key' => $file )
						),
					),
				)
			);
		} catch ( Exception $e ) {
			error_log( 'Error while deleting object.' . $e->getMessage() );
		}
	}
}
