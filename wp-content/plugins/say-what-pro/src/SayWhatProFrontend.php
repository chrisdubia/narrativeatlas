<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The frontend class, responsible for performing the actual replacements
 */
class SayWhatProFrontend implements SayWhatProFrontendInterface {

	/**
	 * Instance of the plugin settings class.
	 * @var SayWhatProSettingsInterface
	 */
	private $settings;

	/**
	 * The current locale.
	 * @var string
	 */
	private $lang;

	/**
	 * @var SayWhatProStringDiscoveryInterface
	 */
	private $string_discovery;

	/**
	 * @var bool
	 */
	private $use_domain_specific_filters = false;

	/**
	 * @var bool
	 */
	private $discovery_active;

	/**
	 * Constructor.
	 *
	 * Store our dependencies.
	 *
	 * @param SayWhatProSettingsInterface $settings The settings instance dependency.
	 * @param SayWhatProStringDiscoveryInterface $string_discovery The string discovery instance.
	 */
	public function __construct( SayWhatProSettingsInterface $settings, SayWhatProStringDiscoveryInterface $string_discovery ) {
		global $wp_version;

		$this->settings                    = $settings;
		$this->string_discovery            = $string_discovery;
		$this->use_domain_specific_filters = version_compare(
			$wp_version,
			'5.5',
			'>='
		);
	}

	/**
	 * Run the plugin functionality.
	 *
	 * Sets up all filters.
	 */
	public function run() {

		// Grab the locale as-set currently.
		$this->update_lang();

		// Most translation plugins filter the locale, so queue up a request to
		// update it when the alternative is available.
		add_action( 'plugins_loaded', array( $this, 'update_lang' ) );
		add_action( 'init', array( $this, 'update_lang' ) );
		add_action( 'template_redirect', array( $this, 'update_lang' ) );

		// Update locale when WordPress' locale switching is invoked.
		add_action( 'change_locale', [ $this, 'change_locale' ] );

		$this->discovery_active = $this->string_discovery->is_active();
		$this->set_up_filters();

		// Deal with Javascript string capture & replacements.
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
	}

	private function set_up_filters() {

		$textdomains = array_keys( $this->settings->optimised_replacements );

		if ( $this->use_domain_specific_filters && ! $this->discovery_active && empty( $this->settings->wildcards ) ) {
			// Register domain specific filters as long as discovery isn't active, in which case we just
			// use the domain-independent versions.
			foreach ( $textdomains as $textdomain ) {
				add_filter( 'gettext_' . $textdomain, array( $this, 'gettext' ), 10, 3 );
				add_filter( 'ngettext_' . $textdomain, array( $this, 'ngettext' ), 10, 5 );
				add_filter( 'gettext_with_context_' . $textdomain, array( $this, 'gettext_with_context' ), 10, 4 );
				add_filter( 'ngettext_with_context_' . $textdomain, array( $this, 'ngettext_with_context' ), 10, 6 );
			}

			return;
		}

		// Add domain-independent filters to handle carrying out our replacements on older WordPress (<5.5).
		add_filter( 'gettext', array( $this, 'gettext' ), 10, 3 );
		add_filter( 'ngettext', array( $this, 'ngettext' ), 10, 5 );
		add_filter( 'gettext_with_context', array( $this, 'gettext_with_context' ), 10, 4 );
		add_filter( 'ngettext_with_context', array( $this, 'ngettext_with_context' ), 10, 6 );
	}

	/**
	 * Change the locale when WordPress' locale switching is invoked.
	 *
	 * @param string $locale
	 */
	public function change_locale( $locale ) {
		$this->lang = $locale;
	}

	/**
	 * Update the internal locale selection from WordPress' current selection.
	 */
	public function update_lang() {
		// Find the site locale
		$site_locale = get_locale();
		$user_locale = $site_locale;

		// Find the user's locale (if set)
		if ( function_exists( 'get_user_locale' ) ) {
			$user_locale = get_user_locale();
		}
		$this->lang = $user_locale;

		// We're done if they match.
		if ( $user_locale === $site_locale ) {
			return;
		}

		// If different use the site_locale for frontend pages, and user-locale for admin pages.
		if ( ! $this->is_admin_request() ) {
			$this->lang = $site_locale;
		}
	}

	/**
	 * Perform a string replacement without context.
	 */
	public function gettext( $translated, $original, $domain ) {
		return $this->ngettext_with_context( $translated, $original, '', null, 'sw-default-context', $domain );
	}

	/**
	 * Perform a string replacement with context.
	 */
	public function gettext_with_context( $translated, $original, $context, $domain ) {
		return $this->ngettext_with_context( $translated, $original, '', null, $context, $domain );
	}

	/**
	 * Perform a (possibly) pluralised translation without context.
	 */
	public function ngettext( $translated, $single, $plural, $number, $domain ) {
		return $this->ngettext_with_context( $translated, $single, $plural, $number, 'sw-default-context', $domain );
	}

	/**
	 * Perform a (possibly) pluralised translation with context.
	 *
	 * Note: This also handles the main logic for all other replacements.
	 *
	 * @param string $translated The current string.
	 * @param string $single The original (singular) string.
	 * @param string $plural The original (pluralised) string.
	 *                            [May be NULL for non _n()-type calls]
	 * @param int $number The number used to determine if singular or pluralised should be used.
	 *                            [May be NULL for non _n()-type calls]
	 * @param  [type] $context    The context, may be null for non _x()-type calls.
	 * @param  [type] $domain     The domain.
	 *
	 * @return [type]             The replaced string.
	 */
	public function ngettext_with_context( $translated, $single, $plural, $number, $context, $domain ) {
		/*
		 * Plugins can use the say_what_domain_aliases filter to return an alias for their domain
		 * if for any reason they change their text domain and want existing replacements to continue
		 * working. The filter should return an array keyed on the current text domain with the value
		 * set to an array of alternative domains to search for replacements. E.g
		 *   $aliases['easy-digital-downloads'][] = 'edd';
		 *   return $aliases;
		 */
		global $disable_say_what_replacements;
		static $domain_aliases = null;

		if ( $disable_say_what_replacements ) {
			return $translated;
		}

		if ( null === $domain_aliases ) {
			$domain_aliases = apply_filters( 'say_what_domain_aliases', array() );
		}
		$original = $single;
		if ( ! is_null( $number ) && 1 !== $number ) {
			$original = $plural;
		}
		$this->string_discovery->maybe_log_available_replacement(
			$original,
			$domain,
			$context,
			$translated
		);

		// Check the given domain.
		if ( isset( $this->settings->optimised_replacements[ $domain ][ $original ][ $context ][ $this->lang ] ) ) {
			// We have a replacement in the provided domain, for this language.
			return $this->settings->optimised_replacements[ $domain ][ $original ][ $context ][ $this->lang ];
		} elseif ( isset( $this->settings->optimised_replacements[ $domain ][ $original ][ $context ]['default'] ) ) {
			// We have a replacement in the provided domain, for the no-language variant.
			return $this->settings->optimised_replacements[ $domain ][ $original ][ $context ]['default'];
		}
		// Check any domain aliases.
		if ( isset( $domain_aliases[ $domain ] ) ) {
			foreach ( $domain_aliases[ $domain ] as $domain ) {
				if ( isset( $this->settings->optimised_replacements[ $domain ][ $original ][ $context ][ $this->lang ] ) ) {
					return $this->settings->optimised_replacements[ $domain ][ $original ][ $context ][ $this->lang ];
				} elseif ( isset( $this->settings->optimised_replacements[ $domain ][ $original ][ $context ]['default'] ) ) {
					return $this->settings->optimised_replacements[ $domain ][ $original ][ $context ]['default'];
				}
			}
		}

		// If we get here there was no replacement - check for any wildcard swaps.

		// Apply any language-specific wildcards.
		if ( ! empty( $this->settings->wildcards[ $this->lang ] ) ) {
			foreach ( $this->settings->wildcards[ $this->lang ] as $original => $swap ) {
				$translated = str_replace( $original, $swap, $translated );
			}
		}
		// Apply any generic language replacements.
		if ( ! empty( $this->settings->wildcards['default'] ) ) {
			foreach ( $this->settings->wildcards['default'] as $original => $swap ) {
				$translated = str_replace( $original, $swap, $translated );
			}
		}

		return $translated;
	}

	/**
	 * Check if this is a request at the backend.
	 *
	 * @return bool true if is admin request, otherwise false.
	 */
	private function is_admin_request() {
		$current_url = home_url( add_query_arg( null, null ) );
		$admin_url   = strtolower( admin_url() );
		$referrer    = strtolower( wp_get_referer() );

		/**
		 * If not an admin request things are straightforward.
		 */
		if ( 0 !== strpos( $current_url, $admin_url ) ) {
			return false;
		}

		// If we get here it's an admin request, but may be AJAX from a frontend page.
		if ( $this->doing_ajax() ) {
			if ( 0 === strpos( $referrer, $admin_url ) ) {
				// AJAX request, and referrer was an admin page.
				return true;
			} else {
				// AJAX request, and referrer was not an admin page.
				return false;
			}
		}

		// Admin request, but not AJAX.
		return true;
	}

	private function doing_ajax() {
		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		} else {
			return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		}
	}

	public function wp_enqueue_scripts() {

		$asset_file = include dirname( plugin_dir_path( __FILE__ ) ) . '/assets/build/frontend.asset.php';
		wp_register_script(
			'swp-js',
			plugins_url( '/say-what-pro/assets/build/frontend.js' ),
			$asset_file['dependencies'],
			$asset_file['version'],
			false
		);
		$discovery_endpoint = admin_url(
			'admin-ajax.php?action=swp_save_discoveries',
			'swp_save_discoveries'
		);
		wp_localize_script(
			'swp-js',
			'swp_data',
			[
				'replacements'       => $this->settings->get_flattened_replacements(),
				'lang'               => $this->lang,
				'discovery'          => $this->string_discovery->is_active(),
				'available'          => $this->string_discovery->is_active() ?
					$this->settings->get_available_string_hashes() :
					[],
				'discovery_endpoint' => $this->string_discovery->is_active() ?
					$discovery_endpoint :
					'',
				'discovery_nonce'    => $this->string_discovery->is_active() ?
					wp_create_nonce( 'swp_save_discoveries' ) :
					'',
				'domains'            => array_keys( $this->settings->optimised_replacements ),
			]
		);
		wp_enqueue_script( 'swp-js' );
	}
}
