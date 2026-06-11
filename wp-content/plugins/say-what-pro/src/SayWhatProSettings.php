<?php

/**
 * Settings class.
 */
class SayWhatProSettings implements SayWhatProSettingsInterface {

	/**
	 * The path to the main plugin directory.
	 * @var string
	 */
	private $plugin_path;

	/**
	 * Replacements in a form optimised for checking and replacing.
	 * @var array
	 */
	private $optimised_replacements = [];

	/**
	 * @var array
	 */
	public $replacements = null;

	/**
	 * @var array
	 */
	public $wildcards = null;

	/**
	 * @var array
	 */
	private $available_strings = [];

	/**
	 * @var array
	 */
	private $flattened_replacements = [];

	/**
	 * Constructor.
	 *
	 * Loads the settings from the database.
	 *
	 * @param string $plugin_path The plugin path.
	 */
	public function __construct( $plugin_path ) {
		$this->plugin_path = $plugin_path;
	}

	/**
	 * Allow read-only access to selected private properties.
	 *
	 * @param string $key The property name being requested.
	 *
	 * @return mixed         The property value.
	 *
	 * @throws Exception
	 */
	public function __get( $key ) {
		if ( 'optimised_replacements' === $key ) {
			return $this->optimised_replacements;
		} elseif ( 'plugin_path' === $key ) {
			return $this->plugin_path;
		} elseif ( 'available_strings' === $key ) {
			return $this->available_strings;
		}
		throw new Exception( 'Invalid property access to ' . $key . ' on ' . __CLASS__ );
	}

	/**
	 * Run the class functionality.
	 */
	public function run() {
		global $wpdb, $table_prefix;
		// Do not do anything if we haven't created our custom tables yet.
		$current_db_version = get_option( 'say_what_pro_db_version' );
		if ( false === $current_db_version ) {
			return;
		}

		// Load the replacements & wildcards (from cache if available, else database).
		$this->load_replacements();

		// If string discovery is active, create a keyed list of available strings we already know about.
		if ( isset( $_COOKIE['say-what-pro-discovery-active'] ) && $_COOKIE['say-what-pro-discovery-active'] ) {
			$sql                     = "SELECT CONCAT_WS('|', orig_string, domain, context, translated_string) AS `unique_key` FROM {$table_prefix}say_what_available_strings";
			$this->available_strings = $wpdb->get_col( $sql, 0 );
		}

		// Generate the optimised lookup array for frontend use.
		$this->generate_optimised_replacements();
	}


	/**
	 * Whether to show multi-language options in the UI.
	 *
	 * @return bool True to show multi-lingual options.
	 */
	public function show_multi_lingual() {
		$enabled = class_exists( 'SitePress' ) ||
				   class_exists( 'Babble_Plugin' ) ||
				   class_exists( 'WPGlobus' ) ||
				   defined( 'POLYLANG_VERSION' ) ||
				   defined( 'WEGLOT_VERSION' );

		return apply_filters(
			'say_what_multilingual_enabled',
			$enabled
		);

	}

	/**
	 * Insert a new replacement into the database.
	 *
	 * @param string $orig_string
	 * @param string $domain
	 * @param string $context
	 * @param string $replacement_string
	 * @param int $disabled
	 * @param string $lang
	 */
	public function insert_replacement( $orig_string, $domain, $context, $replacement_string, $disabled, $lang = '' ) {
		global $wpdb, $table_prefix;
		$sql = "INSERT INTO {$table_prefix}say_what_strings
					 ( orig_string, domain, replacement_string, context, lang, disabled )
	                 VALUES ( %s, %s, %s, %s, %s, %d )";

		$wpdb->query(
			$wpdb->prepare(
				$sql,
				$orig_string,
				$domain,
				$replacement_string,
				$context,
				$lang,
				$disabled
			)
		);
		$this->invalidate_caches();
	}


	/**
	 * Update an existing replacement.
	 *
	 * @param string $replacement_id
	 * @param string $orig_string
	 * @param string $domain
	 * @param string $context
	 * @param string $replacement_string
	 * @param int $disabled
	 * @param string $lang
	 *
	 * @return bool|int
	 */
	public function update_replacement(
		$replacement_id,
		$orig_string,
		$domain,
		$context,
		$replacement_string,
		$disabled,
		$lang = ''
	) {
		global $wpdb, $table_prefix;
		$sql = "UPDATE {$table_prefix}say_what_strings
					   SET orig_string = %s,
						   domain = %s,
						   context = %s,
						   replacement_string = %s,
						   lang = %s,
					       disabled = %d
					 WHERE string_id = %d";

		$result = $wpdb->query(
			$wpdb->prepare(
				$sql,
				$orig_string,
				$domain,
				$context,
				$replacement_string,
				$lang,
				$disabled,
				$replacement_id
			)
		);

		$this->invalidate_caches();

		return $result;
	}

	/**
	 * Return if a given ID exists in the configured replacements.
	 *
	 * @param $replacement_id
	 *
	 * @return bool
	 */
	public function has_id( $replacement_id ) {
		global $wpdb, $table_prefix;
		$sql = "SELECT string_id FROM {$table_prefix}say_what_strings WHERE string_id = %d";

		return $wpdb->get_var( $wpdb->prepare( $sql, $replacement_id ) ) === (string) $replacement_id;
	}

	/**
	 * Check if an "available string" record exists in the database.
	 *
	 * Works by checking the extracted array to avoid excessive small DB queries during discovery.
	 *
	 * @param string $key The composite key for the string.
	 *
	 * @return boolean        True if the string is in the table already.
	 */
	public function has_available_string( $key ) {
		return in_array( $key, $this->available_strings, true );
	}

	/**
	 * Add an "available string" to the extracted array.
	 *
	 * @param string $key The composite key for the string.
	 */
	public function add_available_string( $key ) {
		$this->available_strings[] = $key;
	}

	/**
	 * Get a flattened array of the currently configured replacements.
	 *
	 * @return array
	 */
	public function get_flattened_replacements() {
		if ( [] !== $this->flattened_replacements ) {
			return $this->flattened_replacements;
		}
		// Try and retrieve from the cache.
		if ( wp_using_ext_object_cache() ) {
			$flattened_replacements = wp_cache_get( 'say_what_flattened_replacements', 'swp' );
			if ( is_array( $flattened_replacements ) ) {
				$this->flattened_replacements = $flattened_replacements;
				return $this->flattened_replacements;
			}
		}
		// Otherwise, generate and store.
		array_walk(
			$this->replacements,
			function ( $replacement ) {
				$key                                  = $replacement['domain'] . '|' .
														$replacement['orig_string'] . '|' .
														$replacement['context'] . '|' .
														$replacement['lang'];
				$this->flattened_replacements[ $key ] = $replacement['replacement_string'];
			}
		);

		if ( wp_using_ext_object_cache() ) {
			wp_cache_set( 'say_what_flattened_replacements', $this->flattened_replacements, 'swp', 3600 );
		}
		return $this->flattened_replacements;
	}

	/**
	 * Get a flattened array of hashes of known available strings
	 *
	 * @return array
	 */
	public function get_available_string_hashes() {
		$result = [];
		array_walk(
			$this->available_strings,
			function ( $available_string ) use ( &$result ) {
				$result[] = substr( md5( $available_string ), 0, 16 );
			}
		);

		return $result;
	}

	/**
	 * @return void
	 */
	public function invalidate_caches() {
		wp_cache_delete_multiple(
			[
				'say_what_strings',
				'say_what_wildcards',
				'say_what_optimised_replacements',
				'say_what_flattened_replacements',
			],
			'swp'
		);
	}

	/**
	 * Load the replacements from external cache, or database if no external cache in use.
	 *
	 * @return void
	 */
	private function load_replacements() {
		// Try and load them from the cache.
		if ( wp_using_ext_object_cache() ) {
			$this->load_replacements_from_cache();
		}
		// If they didn't exist in the cache, load from the DB (and cache them if we're using an external object cache).
		if ( null === $this->replacements || null === $this->wildcards ) {
			$this->load_replacements_from_database();
		}
	}

	/**
	 * Load the replacements from the database.
	 * @return void
	 */
	private function load_replacements_from_database() {
		global $wpdb, $table_prefix;

		if ( null === $this->replacements ) {
			// Read the raw replacement data.
			$sql                = "SELECT * FROM {$table_prefix}say_what_strings WHERE disabled = 0";
			$this->replacements = $wpdb->get_results( $sql, ARRAY_A );
		}

		if ( null === $this->wildcards ) {
			// Read the raw wildcard data.
			$sql       = "SELECT original, replacement, lang FROM {$table_prefix}say_what_wildcards";
			$wildcards = $wpdb->get_results( $sql, ARRAY_A );
			foreach ( $wildcards as $wildcard ) {
				$lang = ! empty( $wildcard['lang'] ) ? $wildcard['lang'] : 'default';
				$this->wildcards[ $lang ][ $wildcard['original'] ] = $wildcard['replacement'];
			}
		}

		if ( wp_using_ext_object_cache() ) {
			wp_cache_set( 'say_what_strings', $this->replacements, 'swp', 3600 );
			wp_cache_set( 'say_what_wildcards', $this->wildcards, 'swp', 3600 );
		}
	}

	/**
	 * Load the replacements from the cache.
	 *
	 * @return void
	 */
	private function load_replacements_from_cache() {
		$replacements = wp_cache_get( 'say_what_strings', 'swp' );
		if ( is_array( $replacements ) ) {
			$this->replacements = $replacements;
		}

		$wildcards = wp_cache_get( 'say_what_wildcards', 'swp' );
		if ( is_array( $wildcards ) ) {
			$this->wildcards = $wildcards;
		}
	}

	/**
	 * Return the configured replacements in a format optimised for looking up.
	 *
	 * The return array will be hierarchically keyed by domain, original string,
	 * context, and language, with the value (if any) the replacement string.
	 */
	private function generate_optimised_replacements() {
		if ( ! empty( $this->optimised_replacements ) ) {
			return;
		}
		// Try and retrieve from the cache.
		if ( wp_using_ext_object_cache() ) {
			$optimised_replacements = wp_cache_get( 'say_what_optimised_replacements', 'swp' );
			if ( is_array( $optimised_replacements ) ) {
				$this->optimised_replacements = $optimised_replacements;
				return;
			}
		}
		// Generate, and store in the cache.
		$this->optimised_replacements = [];
		foreach ( $this->replacements as $value ) {
			if ( empty( $value['domain'] ) ) {
				$value['domain'] = 'default';
			}
			if ( empty( $value['context'] ) ) {
				$value['context'] = 'sw-default-context';
			}
			if ( empty( $value['lang'] ) ) {
				$value['lang'] = 'default';
			}
			$this->optimised_replacements[ $value['domain'] ][ $value['orig_string'] ][ $value['context'] ][ $value['lang'] ] = $value['replacement_string'];
		}
		if ( wp_using_ext_object_cache() ) {
			wp_cache_set( 'say_what_optimised_replacements', $this->optimised_replacements, 'swp', 3600 );
		}
	}
}
