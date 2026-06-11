<?php
/**
 * Plugin global functions.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

use Aws\Sts\StsClient;
use Aws\Exception\AwsException;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use Narrativeatlas_AWS_Transcoder\Contracts\Transcoder_Service;
use Narrativeatlas_AWS_Transcoder\Services\Elastic_Transcoder\Elastic_Transcoder_Service;
use Narrativeatlas_AWS_Transcoder\Services\Media_Convert\Media_Convert_Service;
use Narrativeatlas_AWS_Transcoder\Models\AWS_Transcoder_Log;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns value for setting.
 *
 * @param string $setting_key Setting key.
 * @param mixed  $default     Default value to return in case no value is saved.
 *
 * @return mixed|null
 */
function na_aws_transcoder_get_option( string $setting_key, $default = null ) {
	$settings = (array) get_option( 'na_aws_transcoder', array() );

	if ( isset( $settings[ $setting_key ] ) ) {
		return $settings[ $setting_key ];
	}

	return $default;
}

/**
 * Returns enabled resolutions.
 *
 * @return array
 */
function na_aws_transcoder_get_enabled_resolution(): array {
	$enabled_resolutions = na_aws_transcoder_get_option(
		'enabled_resolutions',
		array(
			'original' => 'original',
			'1080p'    => '1080p',
			'720p'     => '720p',
			'480p'     => '480p',
		)
	);

	return empty( $enabled_resolutions ) ? array() : $enabled_resolutions;
}

/**
 * Returns all video resolutions.
 * may in future we can add new admin section.
 *
 * @return array
 */
function na_aws_get_video_resolutions(): array {
	return array(
		'original' => array(
			'label'   => __( 'Original Video', 'narrativeatlas-aws-transcoder' ),
			'width'   => '',
			'height'  => '',
			'bitrate' => 5000000,
			'preset'  => 'Custom-Generic_Mp4_H264_AAC_5Mbps_QVBR_Vq9',
		),
		'1080p'    => array(
			'label'   => __( 'FHD ( Full High Definition )', 'narrativeatlas-aws-transcoder' ),
			'width'   => 1920,
			'height'  => 1080,
			'bitrate' => 5000000,
			'preset'  => 'Custom-Generic_FHD_Mp4_H264_AAC_16x9_Sdr_1920x1080p_30Hz_5Mbps_QVBR_Vq9',
		),
		'720p'     => array(
			'label'   => __( 'HD ( High Definition )', 'narrativeatlas-aws-transcoder' ),
			'width'   => 1280,
			'height'  => 720,
			'bitrate' => 3000000,
			'preset'  => 'Custom-Generic_HD_Mp4_H264_AAC_16x9_Sdr_1280x720p_30Hz_3Mbps_QVBR_Vq9',
		),
		'480p'     => array(
			'label'   => __( 'SD ( Standard Definition )', 'narrativeatlas-aws-transcoder' ),
			'width'   => 854,
			'height'  => 480,
			'bitrate' => 1500000,
			'preset'  => 'Custom-Generic_SD_Mp4_H264_AAC_16x9_Sdr_854x480p_30Hz_1.5Mbps_QVBR_Vq9',
		),
		'360p'     => array(
			'label'   => __( 'LD ( Low Definition )', 'narrativeatlas-aws-transcoder' ),
			'width'   => 640,
			'height'  => 360,
			'bitrate' => 800000,
			'preset'  => 'Custom-Generic_LD_Mp4_H264_AAC_16x9_Sdr_854x480p_30Hz_800Kbps_QVBR_Vq9',
		),
		'240p'     => array(
			'label'   => __( 'LLD ( Very Low Definition )', 'narrativeatlas-aws-transcoder' ),
			'width'   => 426,
			'height'  => 240,
			'bitrate' => 400000,
			'preset'  => 'Custom-Generic_LLD_Mp4_H264_AAC_16x9_Sdr_426x240p_30Hz_400kbps_QVBR_Vq9',
		),
	);
}

/**
 * Checks if resolution is enabled.
 *
 * @param string $resolution Resolution.
 *
 * @return bool
 */
function na_aws_is_resolution_enabled( string $resolution ): bool {

	if ( empty( $resolution ) ) {
		return false;
	}

	$enabled = false;
	if ( array_key_exists( $resolution, na_aws_get_video_resolutions() ) ) {
		$enabled = in_array( $resolution, na_aws_transcoder_get_enabled_resolution(), true );
	}

	return $enabled;
}

/**
 * Returns default resolution.
 *
 * @return string
 */
function na_aws_transcoder_get_default_resolution(): string {
	$default_resolution  = na_aws_transcoder_get_option( 'default_resolution', 'original' );
	$enabled_resolutions = na_aws_transcoder_get_enabled_resolution();

	// If default resolution not exists use current enabled resolution to be as default resolution.
	if ( ! $default_resolution ) {
		$default_resolution = current( $enabled_resolutions );
	}

	return $default_resolution ? $default_resolution : '';
}

/**
 * Returns file info.
 *
 * @param string $file  File path.
 * @param int    $flags Flags.
 *
 * @return array|string|string[]
 */
function na_aws_transcoder_get_file_info( string $file = '', int $flags = PATHINFO_ALL ) {

	if ( $file ) {
		return pathinfo( $file, $flags );
	}

	return array();
}

/**
 * Checks if media is under processing state.
 *
 * @param int $attachment_id Attachment id.
 *
 * @return bool
 */
function na_aws_transcoder_is_media_processing( int $attachment_id ): bool {
	$state = get_post_meta( $attachment_id, '__aws_transcoding_state', true );

	return 'processing' === $state;
}

/**
 * Returns offload credentials.
 *
 * @return array
 */
function na_aws_get_offload_credentials(): array {
	global $as3cf;

	$credentials = array();
	if ( $as3cf && method_exists( $as3cf, 'get_defined_setting' ) ) {

		$access_key = $as3cf->get_defined_setting( 'access-key-id', false );
		$access_key = $access_key ? $access_key : $as3cf->get_setting( 'access-key-id', false );
		$secret_key = $as3cf->get_defined_setting( 'secret-access-key', false );
		$secret_key = $secret_key ? $secret_key : $as3cf->get_setting( 'secret-access-key', false );

		$credentials['access_key'] = $access_key;
		$credentials['secret_key'] = $secret_key;
	}

	return $credentials;
}

/**
 * Returns aws account id.
 *
 * @param Item $item Item object.
 *
 * @return mixed|null
 */
function na_aws_get_aws_account_id( Item $item ) {
	static $account_id = null;

	if ( ! is_null( $account_id ) ) {
		return $account_id;
	}

	$credentials = na_aws_get_offload_credentials();

	$sts_client = new StsClient(
		array(
			'version'     => 'latest',
			'region'      => na_aws_get_offload_bucket_region( $item ),
			'credentials' => array(
				'key'    => $credentials['access_key'],
				'secret' => $credentials['secret_key'],
			),
		)
	);

	try {
		$result = $sts_client->getCallerIdentity();

		$account_id = $result->get( 'Account' );
	} catch ( AwsException $e ) {
		error_log( "Error retrieving AWS Account ID: " . $e->getMessage() . "\n" );
	}

	return $account_id;
}

/**
 * Returns offload bucket region.
 *
 * @param Item $item Item object.
 *
 * @global Amazon_S3_And_CloudFront $as3cf Object.
 *
 * @return string
 */
function na_aws_get_offload_bucket_region( Item $item ): string {
	global $as3cf;

	$bucket_region = '';
	if ( $as3cf && method_exists( $as3cf, 'get_bucket_region' ) ) {
		$bucket_region = $as3cf->get_bucket_region( $item->bucket(), true );
	}

	return is_wp_error( $bucket_region ) ? '' : $bucket_region;
}

/**
 * Returns offload item from state.
 *
 * @param array $state State array.
 *
 * @return null|Item
 */
function na_aws_get_offload_item( array $state ): ?Item {
	global $as3cf;

	if ( empty( $state['userMetadata'] ) || empty( $state['userMetadata']['as3cf_item_id'] ) || empty( $state['userMetadata']['as3cf_source_type'] ) ) {
		return null;
	}

	if ( ! $as3cf || ! method_exists( $as3cf, 'get_source_type_class' ) ) {
		return null;
	}

	$item_id     = absint( $state['userMetadata']['as3cf_item_id'] );
	$source_type = $state['userMetadata']['as3cf_source_type'];

	$class = $as3cf->get_source_type_class( $source_type );

	if ( ! $class ) {
		return null;
	}

	return $class::get_by_id( $item_id );
}

/**
 * Returns transcoder service.
 *
 * @return Transcoder_Service
 */
function na_aws_get_transcoder_service(): Transcoder_Service {
	$service = null;

	if ( ! is_null( $service ) ) {
		return $service;
	}

	//$service = new Elastic_Transcoder_Service();

	if ( 'media_convert' === na_aws_transcoder_get_option( 'enabled_transcoder', 'media_convert' ) ) {
		$service = new Media_Convert_Service();
	}

	// Just to make sure always return service object default will be media convert service.
	return $service ? $service : new Media_Convert_Service();
}

/**
 * Inserts and Updates log item.
 *
 * @param array $args {
 *      @type int    $attachment_id Attachment id.
 *      @type string $job_id        Job id.
 *      @type string $pipeline_id   Pipeline id in case of MediaConvert it will be queue id.
 *      @type string $state         Job transcoding state.
 * }.
 *
 * @return bool|int
 */
function na_aws_insert_log( array $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'attachment_id' => '',
			'source_type'   => '',
			'job_id'        => '',
			'pipeline_id'   => '',
			'state'         => '',
			'created_at'    => '',
		)
	);

	$log_item = na_aws_get_transcoder_log( $args['job_id'] );

	// Only update if log item exists.
	if ( $log_item && ! empty( $log_item->id ) ) {
		// Remove empty values.
		$args = array_filter( $args );

		$updated = AWS_Transcoder_Log::update( $args, array( 'job_id' => $log_item->job_id ) );

		if ( $updated ) {
			// Refresh log item.
			$log_item = na_aws_get_transcoder_log( $args['job_id'] );

			do_action( 'narrativeatlas_transcoding_log_updated', $log_item );
		}

		return $updated;
	}

	$log_item = AWS_Transcoder_Log::create( $args );
	$created  = $log_item->save();

	if ( $created ) {
		$log_item = AWS_Transcoder_Log::first( array( 'job_id' => $log_item->job_id ) );

		do_action( 'narrativeatlas_transcoding_log_created', $log_item );
	}

	return $created;
}

/**
 * Deletes log items for given args.
 *
 * @param array $args Log item supported args.
 *
 * @return bool|int
 */
function na_aws_delete_transcoder_log( array $args ) {
	$deleted = null;

	$args = wp_parse_args(
		$args,
		array(
			'attachment_id' => '',
			'job_id'        => '',
			'state'         => '',
		)
	);

	if ( $args['attachment_id'] || $args['job_id'] || $args['state'] ) {
		$args = array_filter( $args );

		$deleted = AWS_Transcoder_Log::destroy( $args );
	}

	return $deleted;
}

/**
 * Returns transcoder log item.
 *
 * @param string $job_id Job id.
 *
 * @return AWS_Transcoder_Log|null
 */
function na_aws_get_transcoder_log( string $job_id ): ?AWS_Transcoder_Log {

	if ( ! $job_id ) {
		return null;
	}

	return AWS_Transcoder_Log::first( array( 'job_id' => $job_id ) );
}

/**
 * Synchronizes offload media items.
 *
 * @param string $transcoded_video Transcoded file.
 * @param array  $extra_info       Transcoded file extra info.
 * @param Item   $item             Offload media item.
 *
 * @return Item|null
 */
function na_aws_sync_offload_media_items_table( string $transcoded_video, array $extra_info, Item $item ): ?Item {

	if ( ! $transcoded_video || ! $item->id() ) {
		return null;
	}

	$existing_basename    = wp_basename( $item->path() );
	$new_filename         = wp_basename( $transcoded_video );
	$path                 = str_replace( $existing_basename, $new_filename, $item->path() );
	$source_path          = str_replace( $existing_basename, $new_filename, $item->source_path() );
	$original_path        = str_replace( $existing_basename, $new_filename, $item->original_path() );
	$original_source_path = str_replace( $existing_basename, $new_filename, $item->original_source_path() );

	$item->set_path( $path );
	$item->set_source_path( $source_path );
	$item->set_original_path( $original_path );
	$item->set_original_source_path( $original_source_path );
	$item->set_extra_info( $extra_info );
	$item->set_is_private( $item->is_private() );

	$save = $item->save();

	if ( is_wp_error( $save ) ) {
		error_log( 'Message: ' . $save->get_error_message() );
		return null;
	}

	return $item;
}

/**
 * Synchronizes attachment info.
 *
 * @param string $transcoded_video Transcoded video.
 * @param array  $meta_data        Meta data info.
 * @param int    $post_id          Post id.
 */
function na_aws_sync_attachment_info( string $transcoded_video, array $meta_data, int $post_id ) {

	if ( ! wp_attachment_is( 'video', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_wp_attached_file', $transcoded_video );

	update_post_meta(
		$post_id,
		'_wp_attachment_metadata',
		$meta_data
	);

	if ( isset( $meta_data['mime_type'] ) && get_post_mime_type( $post_id ) !== $meta_data['mime_type'] ) {
		// update post table columns.
		$post = get_post( $post_id );
		$guid = str_replace( wp_basename( $post->guid ), wp_basename( $transcoded_video ), $post->guid );

		wp_update_post(
			array(
				'ID'             => $post_id,
				'post_mime_type' => $meta_data['mime_type'],
				'guid'           => $guid,
			)
		);
	}
}

/**
 * Returns meta info.
 *
 * @param array $video_detail Video detail.
 * @param Item  $item         Offload media item.
 *
 * @return array
 */
function na_aws_get_meta_data( array $video_detail, Item $item ): array {
	$meta_data = array(
		'file'   => $item->source_path(),
		'width'  => $video_detail['width'],
		'height' => $video_detail['height'],
	);

	$meta_data = array_merge( $meta_data, na_aws_get_video_length_metadata_from_ms( $video_detail['duration'] ) );

	$type = wp_check_filetype( $item->source_path() );
	if ( ! empty( $type['type'] ) ) {
		$meta_data['mime_type'] = $type['type'];
	}

	return $meta_data;
}

/**
 * Returns video length metadata.
 *
 * @param int $duration_ms Duration.
 *
 * @return array
 */
function na_aws_get_video_length_metadata_from_ms( $duration_ms ) {
	$length = round( $duration_ms / 1000, 2 ); // in seconds

	$hours   = floor( $length / 3600 );
	$minutes = floor( ( $length % 3600 ) / 60 );
	$seconds = floor( $length % 60 );

	if ( $hours > 0 ) {
		$length_formatted = sprintf( '%d:%02d:%02d', $hours, $minutes, $seconds );
	} else {
		$length_formatted = sprintf( '%d:%02d', $minutes, $seconds );
	}

	return array(
		'length'           => $length,
		'length_formatted' => $length_formatted,
	);
}
