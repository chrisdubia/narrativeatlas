<?php
/**
 * MediaConvert Admin Settings Implementation.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Services\Media_Convert
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Services\Media_Convert;

use WP_Screen;
use Press_Themes\PT_Settings\Page;

/**
 * MediaConvert Admin Settings.
 */
class Media_Convert_Admin_Settings {

	/**
	 * Registers settings.
	 *
	 * @param Page $page Page object.
	 */
	public function register_settings( Page $page ) {
		$panel = $page->add_panel(
			'media_convert',
			_x( 'MediaConvert', 'Admin settings panel title', 'narrativeatlas-aws-transcoder' )
		);

		$section = $panel->add_section(
			'mc_settings',
			_x( 'Media Convert Settings', 'Admin settings section title', 'narrativeatlas-aws-transcoder' )
		);

		$resolution_desc = sprintf( '%s <a href="#">%s</a>.', __( 'Please select the resolutions video will be converted to. for more info ', 'narrativeatlas-aws-transcoder' ), __( 'click here', 'narrativeatlas-aws-transcoder' ) );

		$fields = array(
			array(
				'name'    => 'enabled_resolutions',
				'label'   => _x( 'Video Resolutions', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'desc'    => $resolution_desc,
				'type'    => 'multicheck',
				'options' => $this->get_resolution_options(),
				'default' => array(
					'original' => 'original',
					'1080p'    => '1080p',
					'720p'     => '720p',
					'480p'     => '480p',
				),
			),
			array(
				'name'    => 'default_resolution',
				'label'   => _x( 'Default Resolution', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'desc'    => __( 'Please select the default resolution which will be set by default. Make sure resolution is enabled in previous setting. If changed only apply on newly uploaded videos.', 'narrativeatlas-aws-transcoder' ),
				'type'    => 'select',
				'options' => $this->get_resolution_options(),
				'default' => 'original',
			),
			array(
				'name'    => 'media_convert_retries',
				'label'   => _x( 'No. Of Retry', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'desc'    => __( 'Provide a retry count MediaConvert service will retry in case of error.', 'narrativeatlas-aws-transcoder' ),
				'type'    => 'number',
				'default' => 3,
			),
			array(
				'name'    => 'delete_uploaded_file',
				'label'   => _x( 'Delete Uploaded File', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'desc'    => __( 'Uploaded file will be deleted when transcoding finish successfully!.', 'narrativeatlas-aws-transcoder' ),
				'type'    => 'radio',
				'options' => array(
					1 => __( 'Yes', 'narrativeatlas-aws-transcoder' ),
					0 => __( 'No', 'narrativeatlas-aws-transcoder' ),
				),
				'default' => 1,
			),
		);

		$section->add_fields( $fields );
	}

	/**
	 * Adds help tabs.
	 *
	 * @param WP_Screen $screen Screen object.
	 */
	public function add_help_tab( WP_Screen $screen ) {
		$tab_content = sprintf( '<p>%s</p>', __( 'This page helps video transcoding settings.', 'narrativeatlas-aws-transcoder' ) );

		$table = '<table>';
		$table .= '<tr>';
		$table .= sprintf( '<th>%s</th>', _x( 'Resolution', 'Admin settings', 'narrativeatlas-aws-transcoder' ) );
		$table .= sprintf( '<th>%s</th>', _x( 'Detail', 'Admin settings', 'narrativeatlas-aws-transcoder' ) );
		$table .= '</tr>';
		foreach ( na_aws_get_video_resolutions() as $resolution => $details ) {
			$table_columns = sprintf( '<td>%s</td>', esc_html( $details['label'] ) );

			if ( 'original' === $resolution ) {
				$res_detail = __( 'Same as original video', 'narrativeatlas-aws-transcoder' );
			} else {
				$res_detail = sprintf( '%dx%dpx', absint( $details['width'] ), absint( $details['height'] ) );
				$res_detail .= ' | ' . sprintf( 'Bit Rate: %s', esc_html( $this->get_formated_bitrate( $details['bitrate'] ) ) );
			}

			$table_columns .= sprintf( '<td>%s</td>', $res_detail );

			$table .= sprintf( '<tr>%s</tr>', $table_columns );
		}
		$table .= '</table>';

		$tab_content .= $table;

		$screen->add_help_tab(
			array(
				'id'      => 'narrativeatlas_settings_media_convert',
				'title'   => _x( 'Media Convert', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'content' => $tab_content,
			)
		);
	}

	/**
	 * Returns resolution options.
	 *
	 * @return array
	 */
	private function get_resolution_options() {
		$options = array();

		foreach ( na_aws_get_video_resolutions() as $key => $details ) {
			$options[ $key ] = $details['label'];
		}

		return $options;
	}

	/**
	 * Returns formated bitrate.
	 *
	 * @param int $bitrate Bitrate value.
	 *
	 * @return string
	 */
	public function get_formated_bitrate( $bitrate ) {
		if ( ! is_numeric( $bitrate ) || $bitrate <= 0 ) {
			return 'N/A';
		}

		if ( $bitrate >= 1000000 ) {
			return round( $bitrate / 1000000, 2 ) . ' Mbps';
		} elseif ( $bitrate >= 1000 ) {
			return round( $bitrate / 1000, 0 ) . ' kbps';
		} else {
			return $bitrate . ' bps';
		}
	}
}
