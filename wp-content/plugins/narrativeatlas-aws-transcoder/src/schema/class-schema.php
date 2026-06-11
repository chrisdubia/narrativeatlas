<?php
/**
 * Schema Implementation.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Schema
 * @copyright  Copyright (c) 2022, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh, Ravi Sharma(raviousprime)
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Schema;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * AWS transcoder schema manager.
 */
class Schema {

	/**
	 * Returns table name.
	 *
	 * @param string $name table identifier.
	 *
	 * @return null|string full table name or null.
	 */
	public static function table( $name ) {
		$tables = array( 'aws_transcoder_log' => 'na_aws_transcoder_log' );

		global $wpdb;

		return isset( $tables[ $name ] ) ? $wpdb->prefix . $tables[ $name ] : null;
	}

	/**
	 * Creates Tables.
	 */
	public static function create() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		$table_log = self::table( 'aws_transcoder_log' );

		$sql = array();

		// Commented `response` LONGTEXT(255) NOT NULL,

		if ( ! self::exists( $table_log ) ) {
			$sql[] = "CREATE TABLE `{$table_log}` (
  				`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  				`attachment_id` BIGINT(20) NOT NULL,
  				`job_id` VARCHAR(255) NOT NULL,
  				`pipeline_id` VARCHAR(100) NOT NULL,
  				`state` VARCHAR(20) DEFAULT NULL,
  				`created_at` datetime NOT NULL,
  				`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  				 KEY `id` (`id`)
			){$charset_collate};";
		}

		$alteration_count = get_option( 'na_aws_transcoder_db_alteration_count', 0 );

		if ( empty( $alteration_count ) && empty( $wpdb->get_results( "SHOW COLUMNS FROM {$table_log} LIKE 'source_type'" ) ) ) {

			$wpdb->query( "ALTER TABLE {$table_log} ADD COLUMN `source_type` VARCHAR(50) NOT NULL AFTER `attachment_id`;" );

			update_option( 'na_aws_transcoder_db_alteration_count', '1' );
		}

		if ( ! $sql ) {
			return;
		}

		dbDelta( $sql );
	}

	/**
	 * Checks if table exists.
	 *
	 * @param string $table_name table name.
	 *
	 * @return bool
	 */
	public static function exists( $table_name ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;
	}
}
