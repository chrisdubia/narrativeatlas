<?php
/**
 * AWS transcoding service message listener.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\SnsClient;

if ( ! file_exists( '../../../../../wp-load.php' ) ) {
	error_log( 'Loader file not found' );
	die();
}

require_once '../../../../../wp-load.php';

try {
	$message   = Message::fromRawPostData();
	$validator = new MessageValidator();
} catch ( Exception $e ) {
	error_log( 'Invalid Response: ' . $e->getMessage() );

	die();
}

// Validate the message and log errors if invalid.
try {
	$validator->validate( $message );
} catch ( InvalidSnsMessageException $e ) {
	// Pretend we're not here if the message is invalid.
	http_response_code( 404 );
	error_log( 'SNS Message Validation Error: ' . $e->getMessage() );
	die();
}

// Check the message type
if ( isset( $message['Type'] ) && $message['Type'] === 'SubscriptionConfirmation' ) {

	global $as3cf;

	$credentials = na_aws_get_offload_credentials();

	// Create SNS client
	$sns = new SnsClient(
		array(
			'version'     => 'latest',
			'region'      => $as3cf->get_setting( 'region', false ), // replace with your region
			'credentials' => array(
				'key'    => $credentials['access_key'],
				'secret' => $credentials['secret_key'],
			),
		)
	);

	try {
		// Confirm the subscription
		$sns->confirmSubscription(
			array(
				'Token'    => $message['Token'],
				'TopicArn' => $message['TopicArn'],
			)
		);

	} catch (Exception $e) {
		error_log( "Error confirming subscription: " . $e->getMessage() );
		die();
	}
} elseif ( isset( $message['Type'] ) && $message['Type'] === 'Notification' ) {
	// Handle Transcoder notifications.  In this example, we write them to
	$resp_message = json_decode( $message['Message'], true );

	// @todo improve logic in future for different responses currently handling elastictranscoder and mediaconvert service.
	$status = '';

	if ( isset( $resp_message['detail']['status'] ) ) {
		// MediaConvert service response.
		$status = $resp_message['detail']['status'];
	} elseif ( isset( $resp_message['state'] ) ) {
		// ElasticTranscoder service response.
		$status = $resp_message['state'];
	}

	if ( $status ) {
		do_action( "narrative_aws_transcoding_status_{$status}", $resp_message );
	}
}
