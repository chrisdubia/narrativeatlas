<?php
/**
 * AWS Transcoder Log UI Helper Class
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Admin
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Log UI Helper Class.
 */
class AWS_Log_UI_Helper {

	/**
	 * Returns and Initialize job creator
	 *
	 * @return AWS_Log_UI_Helper
	 */
	public static function boot() {
		$self = new self();

		$self->setup();

		return $self;
	}

	/**
	 * Setup callbacks to hooks.
	 */
	private function setup() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * Add menu for listing aws logs.
	 */
	public function add_menu() {
		add_submenu_page(
			'tools.php',
			__( 'AWS Transcoding Logs', 'narrativeatlas-aws-transcoder' ),
			__( 'AWS Logs', 'narrativeatlas-aws-transcoder' ),
			'manage_options',
			'aws-transcoding-logs',
			array( $this, 'render' )
		);
	}

	/**
	 * Renders log table.
	 */
	public function render() {
		// Render job view if job id is set in url.
		if ( isset( $_GET['job_id'] ) ) {
			( new AWS_Transcoder_Job_View() )->render();

			return;
		}

		$log_table = new AWS_Transcoder_Log_Table();

		echo '<div class="wrap"><h2>' . esc_html__( 'AWS Transcoding Logs', 'narrativeatlas-aws-transcoder' ) . '</h2>';

		$log_table->process_bulk_actions();
		$log_table->prepare_items();

		$user_id = get_current_user_id();

		echo '<form id="aws-transcoding-logs" method="get">';

		$log_table->display();

		echo '<input type="hidden" name="page" value="aws-transcoding-logs">';
		wp_nonce_field( "na-aws-log-item-bulk-delete-{$user_id}", '_wpnonce_na_aws_log_item_bulk_delete' );
		echo '</form>';

		echo '</div>';
	}
}

