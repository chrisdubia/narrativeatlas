<?php
/**
 * Log Item Model Implementation.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Models
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Models;

use Narrativeatlas_AWS_Transcoder\Schema\Schema;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Log model class.
 *
 * @property-read int    $id            Row id.
 * @property-read int    $attachment_id Attachment id.
 * @property-read string $source_type   Source type.
 * @property-read string $job_id        Job id.
 * @property-read string $pipeline_id   Pipeline id.
 * @property-read string $state         Job state.
 * @property-read string $created_at    Created date
 * @property-read string $updated_at    Updated date
 */
class AWS_Transcoder_Log extends Model {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function table() {
		return Schema::table( 'aws_transcoder_log' );
	}

	/**
	 * Get table schema.
	 *
	 * @return array
	 */
	public static function schema() {

		return array(
			'id'            => 'integer',
			'attachment_id' => 'integer',
			'source_type'   => 'string',
			'job_id'        => 'string',
			'pipeline_id'   => 'string',
			'state'         => 'string',
			//'response'      => 'string',
			'created_at'    => 'datetime',
			'updated_at'    => 'datetime',
		);
	}
}
