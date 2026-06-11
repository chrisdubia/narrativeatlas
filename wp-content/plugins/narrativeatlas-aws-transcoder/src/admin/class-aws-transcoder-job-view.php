<?php
/**
 * Job View Implementation
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Admin
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Admin;

// Exit if accessed directly.
use Narrativeatlas_AWS_Transcoder\Models\AWS_Transcoder_Log;

defined('ABSPATH') || exit;

/**
 * Job view class
 */
class AWS_Transcoder_Job_View {

	/**
	 * Renders job view.
	 */
	public function render() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Invalid request', 'narrativeatlas-aws-transcoder' ) );
		}

		$job_id = empty( $_GET['job_id'] ) ? '' : wp_unslash( $_GET['job_id'] );

		$log = na_aws_get_transcoder_log( $job_id );

		if ( empty( $log ) ) {
			esc_html_e( 'No job found!', 'narrativeatlas-aws-transcoder' );

			return;
		}

		$state = strtolower( $log->state );

		echo '<div class="wrap">';

		echo sprintf( '<h3>%s</h3>', __( 'Transcoding Job Information', 'narrativeatlas-aws-transcoder' ) );

		if ( 'complete' === $state || 'completed' === $state ) {
			$this->render_complete_info( $log );
		} elseif ( 'error' === $state ) {
			$this->render_error_info( $log );
		} else {
			// Let transcoder service handle it.
			na_aws_get_transcoder_service()->status(
				array(
					'job_id'        => $log->job_id,
					'attachment_id' => $log->attachment_id,
					'source_type'   => $log->source_type,
				)
			);
		}

		echo '</div>';
	}

	/**
	 * Renders complete transcoding job info.
	 *
	 * @param AWS_Transcoder_Log $log
	 */
	private function render_complete_info( AWS_Transcoder_Log $log ) {
		?>
		<div class="na-aws-log-complete-info">
			<table class="wp-list-table widefat fixed striped table-view-list">
				<tr>
					<td><?php esc_html_e( 'Status', 'narrativeatlas-aws-transcoder' ); ?></td>
					<td><?php echo esc_html( $log->state ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Last Updated', 'narrativeatlas-aws-transcoder' ); ?></td>
					<td><?php echo mysql2date( 'g:i:s A, F j, Y', $log->updated_at ); ?></td>
				</tr>
			</table>
			<div class="na-aws-log-attachment" style="margin-top: 10px;">
				<?php
				echo wp_video_shortcode(
					array(
						'src'     => wp_get_attachment_url( $log->attachment_id ),
						'width'   => 640,
						'height'  => 360,
						'preload' => 'metadata',
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders error transcoding job info.
	 *
	 * @param AWS_Transcoder_Log $log
	 */
	private function render_error_info( AWS_Transcoder_Log $log ) {
		?>
		<div class="na-aws-log-error-info">
			<table class="wp-list-table widefat fixed striped table-view-list">
				<tr>
					<td><?php esc_html_e( 'Status', 'narrativeatlas-aws-transcoder' ); ?></td>
					<td><?php echo esc_html( $log->state ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Last Updated', 'narrativeatlas-aws-transcoder' ); ?></td>
					<td><?php echo mysql2date( 'g:i:s A, F j, Y', $log->updated_at ); ?></td>
				</tr>
			</table>
		</div>
		<?php
	}
}