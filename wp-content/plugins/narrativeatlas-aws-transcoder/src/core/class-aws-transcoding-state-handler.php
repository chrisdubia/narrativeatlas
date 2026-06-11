<?php
/**
 * AWS transcoding State handler
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Core;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * State handler class implementation.
 */
class AWS_Transcoding_State_Handler {

	/**
	 * Returns and Initialize state handler.
	 *
	 * @return AWS_Transcoding_State_Handler
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
		// Before remove local file.
		add_action( 'narrative_aws_transcoding_status_COMPLETED', array( $this, 'on_complete' ) );
		// For mediaconvert service.
		add_action( 'narrative_aws_transcoding_status_COMPLETE', array( $this, 'on_complete' ) );
		add_action( 'narrative_aws_transcoding_status_ERROR', array( $this, 'on_failed' ) );
	}

	/**
	 * On complete let transcoder service handle it.
	 *
	 * @param array $state Job state
	 */
	public function on_complete( $state ) {
		na_aws_get_transcoder_service()->on_complete( $state );
	}

	/**
	 * On failed state let transcoder service handle it.
	 *
	 * @param array $state State array.
	 */
	public function on_failed( $state ) {
		na_aws_get_transcoder_service()->on_error( $state );
	}
}
