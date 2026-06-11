<?php
/**
 * Notifier on transcoding job state change.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Core;

use Narrativeatlas_AWS_Transcoder\Models\AWS_Transcoder_Log;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Notifier implementation.
 */
class AWS_Transcoder_Notifier {

	/**
	 * Returns and Initialize admin notifier.
	 *
	 * @return AWS_Transcoder_Notifier
	 */
	public static function boot() {
		$self = new self();

		$self->setup();

		return $self;
	}

	/**
	 * Callbacks to hooks.
	 */
	private function setup() {
		add_action( 'narrativeatlas_transcoding_log_created', array( $this, 'notify' ) );
		add_action( 'narrativeatlas_transcoding_log_updated', array( $this, 'notify' ) );
	}

	/**
	 * Sends notifications on different state change.
	 *
	 * @param AWS_Transcoder_Log $log Log object.
	 */
	public function notify( AWS_Transcoder_Log $log ) {
		$state = strtolower( $log->state );

		$notify  = false;
		$subject = '';
		$message = '';
		if ( 'submitted' === $state ) {

			$notify  = (bool) na_aws_transcoder_get_option( 'notify_on_submit', 1 );
			$subject = na_aws_transcoder_get_option( 'notification_submit_email_subject', '' );
			$message = na_aws_transcoder_get_option( 'notification_submit_email_message', '' );
		} elseif ( 'complete' === $state || 'completed' === $state ) {

			$notify  = (bool) na_aws_transcoder_get_option( 'notify_on_success', 1 );
			$subject = na_aws_transcoder_get_option( 'notification_success_email_subject', '' );
			$message = na_aws_transcoder_get_option( 'notification_success_email_message', '' );
		} elseif ( 'error' === $state ) {

			$notify  = (bool) na_aws_transcoder_get_option( 'notify_on_error', 1 );
			$subject = na_aws_transcoder_get_option( 'notification_error_email_subject', '' );
			$message = na_aws_transcoder_get_option( 'notification_error_email_message', '' );
		}

		if ( ! $notify || empty( $subject ) || empty( $message ) ) {
			return;
		}

		$view_url = add_query_arg(
			array(
				'job_id' => $log->job_id,
			),
			admin_url( 'tools.php?page=aws-transcoding-logs' )
		);

		$message = str_replace( '{{link}}', $view_url, $message );

		$this->notify_admin( $subject, $message );
	}

	/**
	 * Notifies admin.
	 *
	 * @param string $subject Subject for email.
	 * @param string $message Message for email.
	 */
	public function notify_admin( string $subject, string $message ) {
		$notification_email = na_aws_transcoder_get_option( 'notification_email', get_option( 'admin_email' ) );

		if ( ! is_email( $notification_email ) ) {
			return;
		}

		@wp_mail( $notification_email, $subject, $message );
	}
}
