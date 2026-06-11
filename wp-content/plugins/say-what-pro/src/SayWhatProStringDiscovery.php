<?php

/**
 * Class SayWhatProStringDiscovery
 */
class SayWhatProStringDiscovery implements SayWhatProStringDiscoveryInterface {

	/**
	 * @var SayWhatProSettingsInterface
	 */
	private $settings;

	/**
	 * Temporary storage during String Discovery to buffer writes to the DB.
	 *
	 * @var array
	 */
	private $available_replacements = array();


	/**
	 * SayWhatProStringDiscovery constructor.
	 *
	 * @param SayWhatProSettingsInterface $settings
	 */
	public function __construct( SayWhatProSettingsInterface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Turn on the features.
	 */
	public function run() {
		if ( $this->is_active() ) {
			// Write available replacements to the DB during string discovery.
			add_action( 'shutdown', array( $this, 'write_available_replacements' ) );
			// Handle AJAX callback for JS translations.
			add_action( 'wp_ajax_nopriv_swp_save_discoveries', [ $this, 'maybe_log_ajax_available_replacements' ] );
			add_action( 'wp_ajax_swp_save_discoveries', [ $this, 'maybe_log_ajax_available_replacements' ] );
		}
	}

	/**
	 * Enables string discovery.
	 */
	public function enable() {
		setcookie( 'say-what-pro-discovery-active', true, 0, '/' );
		$_COOKIE['say-what-pro-discovery-active'] = true;
	}

	/**
	 * Disables string discovery.
	 */
	public function disable() {
		setcookie( 'say-what-pro-discovery-active', false, 0, '/' );
		$_COOKIE['say-what-pro-discovery-active'] = false;
	}

	/**
	 * Whether string discovery is active or not.
	 * @return bool
	 */
	public function is_active() {
		return isset( $_COOKIE['say-what-pro-discovery-active'] ) && $_COOKIE['say-what-pro-discovery-active'];
	}

	/**
	 * Callback for JS string discovery
	 */
	public function maybe_log_ajax_available_replacements() {
		// Validate nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'swp_save_discoveries' ) ) {
			wp_die( 'Could not validate request.' );
		}
		// Record replacements
		foreach ( $_POST['payload'] as $replacement ) {
			$this->maybe_log_available_replacement(
				stripslashes( $replacement[0] ),
				stripslashes( $replacement[1] ),
				stripslashes( $replacement[2] ),
				stripslashes( $replacement[3] )
			);
		}
	}

	/**
	 * Decide whether this possible replacement should be logged, and if it should - log it.
	 */
	public function maybe_log_available_replacement( $original, $domain, $context, $translated_string ) {
		if ( $this->is_active() ) {
			$this->log_available_replacement(
				$original,
				$domain,
				$context,
				$translated_string,
				mb_strtolower( $original ),
				mb_strtolower( $translated_string )
			);
		}
	}

	/**
	 * Write out a block of available replacements into the database.
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function write_available_replacements() {
		global $wpdb, $table_prefix;
		if ( ! count( $this->available_replacements ) ) {
			return;
		}
		$cnt = count( $this->available_replacements ) / 6;
		// Store items in the DB
		$sql = "INSERT LOW_PRIORITY IGNORE INTO ${table_prefix}say_what_available_strings" .
			   '( orig_string, domain, context, translated_string, orig_string_lc, translated_string_lc ) VALUES ' .
			   str_repeat( '(%s,%s,%s,%s,%s,%s),', $cnt - 1 ) .
			   '(%s,%s,%s,%s,%s,%s)';
		$wpdb->query(
			$wpdb->prepare( $sql, $this->available_replacements )
		);
		// Empty the array
		$this->available_replacements = array();
	}

	/**
	 * Log an available replacement.
	 *
	 * @param string $original
	 * @param string $domain
	 * @param string $context
	 * @param string $translated_string
	 * @param string $original_lc
	 * @param string $translated_string_lc
	 */
	private function log_available_replacement(
		$original,
		$domain,
		$context,
		$translated_string,
		$original_lc,
		$translated_string_lc
	) {

		$key = implode( '|', array( $original, $domain, $context, $translated_string ) );

		// Bail if we already know about it.
		if ( $this->settings->has_available_string( $key ) ) {
			return;
		}

		// Add it to the settings objects list of keys
		$this->settings->add_available_string( $key );

		// Queue it for writing to the DB.
		$this->available_replacements[] = $original;
		$this->available_replacements[] = $domain;
		$this->available_replacements[] = $context;
		$this->available_replacements[] = $translated_string;
		$this->available_replacements[] = $original_lc;
		$this->available_replacements[] = $translated_string_lc;

		// Trigger a write now if we have a chunk of 60.
		if ( count( $this->available_replacements ) > 60 ) {
			$this->write_available_replacements();
		}
	}
}
