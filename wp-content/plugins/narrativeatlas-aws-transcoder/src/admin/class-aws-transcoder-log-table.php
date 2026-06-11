<?php
/**
 * Transcoding Log Table Implementation.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Admin
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Admin;

use WP_List_Table;
use Narrativeatlas_AWS_Transcoder\Models\AWS_Transcoder_Log;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Transcoder Log Table Class.
 */
class AWS_Transcoder_Log_Table extends WP_List_Table {

	/**
	 * Flag vars.
	 *
	 * @var array
	 */
	private $args = array();

	/**
	 * Class constructor.
	 *
	 * @param array $args Array of values.
	 */
	public function __construct( $args = array() ) {
		$this->args = $args;

		$parent_args = array(
			'plural'   => 'na_aws_transcoder_logs',
			'singular' => 'na_aws_transcoder_log',
			'ajax'     => false,
			'screen'   => 'aws_transcoder_logs',
		);

		parent::__construct( $parent_args );
	}

	/**
	 * Checks user permissions.
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Prepares transcoding log items for the table.
	 */
	public function prepare_items() {
		$current_page = $this->get_pagenum();
		$per_page     = 20;

		$args = array(
			'per_page' => $per_page,
			'page'     => $current_page,
		);

		$args['orderby'] = 'updated_at';
		$args['order']   = 'DESC';

		$this->items = AWS_Transcoder_Log::get( $args );

		unset( $args['per_page'] );
		unset( $args['offset'] );

		$this->set_pagination_args(
			array(
				'total_items' => AWS_Transcoder_Log::count( $args ),
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Processes bulk actions on table.
	 */
	public function process_bulk_actions() {
		$user_id = get_current_user_id();

		if ( ! isset( $_REQUEST['_wpnonce_na_aws_log_item_bulk_delete'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_na_aws_log_item_bulk_delete'], "na-aws-log-item-bulk-delete-{$user_id}" ) ) {
			return;
		}


		if ( $this->current_action() === 'delete' && ! empty( $_REQUEST['item_id'] ) ) {
			$ids = array_map( 'absint', wp_unslash( $_REQUEST['item_id'] ) );

			AWS_Transcoder_Log::destroy(
				array(
					'id' => array(
						'value' => $ids,
						'op'    => 'IN',
					)
				)
			);

			wp_admin_notice(
				__( 'Log items deleted successfully.', 'narrativeatlas-aws-transcoder' ),
				array(
					'type'        => 'warning',
					'dismissible' => true,
				)
			);
		}
	}

	/**
	 * Renders message when no item found.
	 */
	public function no_items() {
		_e( 'The AWS Transcoding log is empty.', 'narrativeatlas-aws-transcoder' );
	}

	/**
	 * Generates and displays row action links.
	 *
	 * @param AWS_Transcoder_Log $log         Log item.
	 * @param string             $column_name Current column name.
	 * @param string             $primary     Primary column name.
	 *
	 * @return string Row actions output for logs, or an empty string
	 *                if the current column is not the primary column.
	 */
	public function handle_row_actions( $log, $column_name, $primary ) {

		if ( $primary !== $column_name ) {
			return '';
		}

		$view_url = add_query_arg(
			array(
				'job_id'   => $log->job_id,
				'_wpnonce' => wp_create_nonce( "na-aws-transcoder-view-job-{$log->job_id}" ),
			),
			admin_url( 'tools.php?page=aws-transcoding-logs' )
		);

		$actions['view'] = sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), esc_html__( 'View', 'narrativeatlas-aws-transcoder' ) );

		return $this->row_actions( $actions );
	}

	/**
	 * Returns bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		$bulk_actions = array( 'delete' => __( 'Delete', 'narrativeatlas-aws-transcoder' ) );

		return $bulk_actions;
	}

	/**
	 * Returns column info.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Title', 'narrativeatlas-aws-transcoder' ),
			'job_id'      => __( 'Job Id', 'narrativeatlas-aws-transcoder' ),
			'pipeline_id' => __( 'Pipeline Id', 'narrativeatlas-aws-transcoder' ),
			'state'       => __( 'State', 'narrativeatlas-aws-transcoder' ),
			/*'response'    => __( 'Raw Response', 'narrativeatlas-aws-transcoder' ),*/
			'updated_at'  => __( 'Last Updated', 'narrativeatlas-aws-transcoder' ),
		);
	}

	/**
	 * Returns sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		$sortable_columns = array(
			'updated_at' => array( 'updated_at', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Mask action
	 *
	 * @param AWS_Transcoder_Log $item Current log item.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="item_id[]" value="%d" />', $item->id
		);
	}

	/**
	 * Renders column data.
	 *
	 * @param AWS_Transcoder_Log $log_item Log item.
	 * @param string             $col      Column name.
	 *
	 * @return string
	 */
	public function column_default( $log_item, $col ) {

		switch ( $col ) {
			case 'id':
				return $log_item->id;
			case 'title':
				$title = sprintf( '<a href=%s>%s</a>', esc_url( get_edit_post_link( $log_item->attachment_id ) ), get_the_title( $log_item->attachment_id ) );

				return $title;
			case 'job_id':
				return esc_html( $log_item->job_id );
			case 'pipeline_id':
				return esc_html( $log_item->pipeline_id );
			case 'state':
				return esc_html( $log_item->state );
			/*case 'response':
				return esc_html( $log_item->response );*/
			case 'updated_at':
				return mysql2date( 'g:i:s A, F j, Y', $log_item->updated_at );
		}
	}
}
