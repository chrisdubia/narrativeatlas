<?php

/**
 * Class SayWhatProDbManager
 *
 * Handles database changes between versions.
 */
class SayWhatProDbManager {

	public function run() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			add_action( 'admin_init', array( $this, 'check_db_version' ) );
		}
	}

	/**
	 * Check for pending upgrades, and run them if required.
	 */
	public function check_db_version() {
		$current_db_version = (int) get_option( 'say_what_pro_db_version', 1 );
		// Bail if we're already up to date.
		if ( $current_db_version >= SAY_WHAT_PRO_DB_VERSION ) {
			return;
		}
		// Otherwise, check for, and run updates.
		foreach ( range( $current_db_version + 1, SAY_WHAT_PRO_DB_VERSION ) as $version ) {
			if ( is_callable( array( $this, 'upgrade_db_to_' . $version ) ) ) {
				$this->{'upgrade_db_to_' . $version}();
				update_option( 'say_what_pro_db_version', $version );
			} else {
				update_option( 'say_what_pro_db_version', $version );
			}
		}
	}

	/**
	 * Create available_strings table if missing, or remove (broken) unique indexes if the
	 * table does exist.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_3() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'say_what_available_strings';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
			// If the available_strings table exists, remove the (broken) index.
			$sql = "ALTER TABLE $table_name DROP INDEX replacement";
			$wpdb->query( $sql );
		} else {
			// If the table is missing, create it.
			$sql = "CREATE TABLE $table_name (
								 orig_string text NOT NULL,
								 domain varchar(255),
								 context text
								 ) DEFAULT CHARACTER SET utf8";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

	}

	/**
	 * Add language field.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_4() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'say_what_strings';
		$sql        = "CREATE TABLE $table_name (
					string_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					orig_string text NOT NULL,
					domain varchar(255),
					replacement_string text,
					context text,
					lang varchar(10)
				) DEFAULT CHARACTER SET utf8";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Add wildcards table.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_5() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'say_what_wildcards';
		$sql        = "CREATE TABLE $table_name (
					wildcard_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					original text NOT NULL,
					replacement text,
					lang varchar(10)
				) DEFAULT CHARACTER SET utf8";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Add translated_string column to available strings
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_6() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'say_what_available_strings';
		$sql        = "CREATE TABLE $table_name (
					orig_string text NOT NULL,
					domain varchar(255),
					context text,
					translated_string text
				) DEFAULT CHARACTER SET utf8";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Change charset / collation and add _lc columns to available strings table.
	 */
	public function upgrade_db_to_7() {
		global $wpdb;

		if ( $wpdb->has_cap( 'utf8mb4' ) ) {
			$charset   = 'utf8mb4';
			$collation = 'utf8mb4_bin';
		} else {
			$charset   = 'utf8';
			$collation = 'utf8_bin';
		}

		$table_name = $wpdb->prefix . 'say_what_available_strings';
		$wpdb->query( 'DROP TABLE ' . $table_name );

		$sql = "CREATE TABLE $table_name (
					orig_string text NOT NULL,
					domain varchar(255) NOT NULL,
					context text NOT NULL,
					translated_string text NOT NULL,
					orig_string_lc text NOT NULL,
					translated_string_lc text NOT NULL,
					UNIQUE KEY `arg_index` (`orig_string`(110),`domain`(24),`context`(32),`translated_string`(25))
				) DEFAULT CHARACTER SET=$charset COLLATE=$collation";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$admin_notices                           = get_option( 'swp_admin_notices', [] );
		$admin_notices['swp_suggestion_refresh'] = 1;
		update_option( 'swp_admin_notices', $admin_notices );
	}

	/**
	 * Add disabled field.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_8() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'say_what_strings';
		$sql        = "CREATE TABLE $table_name (
					string_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					orig_string text NOT NULL,
					domain varchar(255),
					replacement_string text,
					context text,
					lang varchar(10),
					disabled boolean DEFAULT 0,
				) DEFAULT CHARACTER SET utf8";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Add disabled field if not present
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_9() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'say_what_strings';
		$sql        = "CREATE TABLE $table_name (
					string_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					orig_string text NOT NULL,
					domain varchar(255),
					replacement_string text,
					context text,
					lang varchar(10),
					disabled boolean DEFAULT 0,
				) DEFAULT CHARACTER SET utf8";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Ensure strings table created.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_10() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'say_what_strings';
		$sql        = "CREATE TABLE $table_name (
					string_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					orig_string text NOT NULL,
					domain varchar(255),
					replacement_string text,
					context text,
					lang varchar(10),
					disabled boolean DEFAULT 0
				) DEFAULT CHARACTER SET utf8";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Expand lang field to ensure it can handle languages such as de_CH_informal.
	 *
	 * Sigh. https://core.trac.wordpress.org/ticket/49364
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_11() {
		global $wpdb;
		$db_version = $wpdb->db_version();
		if ( version_compare( $db_version, '8.0.17', '<' ) ) {
			$key_def = 'string_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';
		} else {
			$key_def = 'string_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,';
		}
		$table_name = $wpdb->prefix . 'say_what_strings';
		$sql        = "CREATE TABLE $table_name (
					" . $key_def . '
					orig_string text NOT NULL,
					domain varchar(255),
					replacement_string text,
					context text,
					lang varchar(20),
					disabled boolean DEFAULT 0
				) DEFAULT CHARACTER SET utf8';
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Expand lang field to ensure it can handle languages such as de_CH_informal.
	 *
	 * Sigh. https://core.trac.wordpress.org/ticket/49364
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function upgrade_db_to_12() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$db_version = $wpdb->db_version();

		$table_name = $wpdb->prefix . 'say_what_strings';
		if ( version_compare( $db_version, '8.0.17', '<' ) ) {
			$key_def = 'string_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';
		} else {
			$key_def = 'string_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,';
		}
		$sql = "CREATE TABLE $table_name (
					" . $key_def . '
					orig_string text NOT NULL,
					domain varchar(255),
					replacement_string text,
					context text,
					lang varchar(20),
					disabled boolean DEFAULT 0
				) DEFAULT CHARACTER SET utf8';

		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'say_what_wildcards';
		if ( version_compare( $db_version, '8.0.17', '<' ) ) {
			$key_def = 'wildcard_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,';
		} else {
			$key_def = 'wildcard_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,';
		}
		$sql = "CREATE TABLE $table_name (
						 " . $key_def . '
						 original text NOT NULL,
						 replacement text,
						 lang varchar(20)
						 ) DEFAULT CHARACTER SET utf8';
		dbDelta( $sql );
	}
}
