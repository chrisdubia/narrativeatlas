<?php
/**
 * ElasticTranscoder Service Admin Settings
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Services\Elastic_Transcoder
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Services\Elastic_Transcoder;

use Press_Themes\PT_Settings\Page;

/**
 * Admin Settings.
 */
class Elastic_Transcoder_Admin_Settings {

	/**
	 * Registers settings.
	 *
	 * @param Page $page Page object.
	 */
	public function register_settings( Page $page ) {
		$panel = $page->add_panel(
			'elastic_transcoder',
			_x( 'Elastic Transcoder', 'Admin settings panel title', 'narrativeatlas-aws-transcoder' )
		);

		$section = $panel->add_section(
			'et_settings',
			_x( 'Elastic Transcoder Settings', 'Admin settings section title', 'narrativeatlas-aws-transcoder' )
		);

		$doc_link = 'https://docs.aws.amazon.com/elastictranscoder/latest/developerguide/creating-pipelines.html';

		$fields = array(
			array(
				'name'    => 'pipeline_id',
				'label'   => _x( 'Pipeline ID', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'desc'    => __( 'Provide the pipeline id.', 'narrativeatlas-aws-transcoder' ),
				'type'    => 'text',
				'default' => '',
			),
			array(
				'desc' => sprintf( 'If, You do not have a pipeline id. Please create a pipeline <a href="%s">%s</a>.', esc_url( $doc_link ), __( 'click here', 'narrativeatlas-aws-transcoder' ) ),
				'type' => 'html',
			),
		);

		$section->add_fields( $fields );
	}
}
