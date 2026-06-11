<?php
/**
 * Log table synchronizer.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2025, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Core;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Log table synchronizer.
 */
class AWS_Log_Table_Synchronizer {

	/**
	 * Returns and Initialize log table synchronizer.
	 *
	 * @return AWS_Log_Table_Synchronizer
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
		add_action( 'delete_attachment', array( $this, 'on_attachment_delete' ), 999 );
	}

	/**
	 * On attachment delete.
	 *
	 * @param int $attachment_id Attachment id.
	 */
	public function on_attachment_delete( $attachment_id ) {
		na_aws_delete_transcoder_log( array( 'attachment_id' => $attachment_id ) );
	}
}
