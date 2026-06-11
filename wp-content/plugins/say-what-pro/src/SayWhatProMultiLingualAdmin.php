<?php
/**
 * Created by PhpStorm.
 * User: leewillis
 * Date: 02/10/2017
 * Time: 13:31
 */

class SayWhatProMultiLingualAdmin {

	/**
	 * Run the class features.
	 */
	public function run() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Runs on plugins_loaded.
	 *
	 * Determine if relevant translation plugins are loaded. If so, set up
	 * filters so that we use their settings to optimise the display of the
	 * language selector based on "active" languages.
	 */
	public function plugins_loaded() {
		if ( class_exists( 'SitePress' ) ) {
			add_filter( 'say_what_pro_language_list', array( $this, 'sitepress_language_list' ) );
		}
		if ( class_exists( 'PLL_Model' ) ) {
			add_filter( 'say_what_pro_language_list', array( $this, 'polylang_language_list' ) );
		}
		if ( class_exists( 'WPGlobus' ) ) {
			add_filter( 'say_what_pro_language_list', array( $this, 'wpglobus_language_list' ) );
		}
	}

	/**
	 * Re-order the language list taking into account active languages in WPML.
	 *
	 * @param array $languages Array of languages.
	 *
	 * @return array             Re-ordered array of languages.
	 */
	public function sitepress_language_list( $languages ) {
		global $sitepress;
		if ( is_callable( array( $sitepress, 'get_active_languages' ) ) ) {
			$this->active_languages = wp_list_pluck( $sitepress->get_active_languages(), 'default_locale' );
			usort( $languages, array( $this, 'language_list_sort_cb' ) );
		}

		return $languages;
	}

	/**
	 * Re-order language list taking into account active languages in Polylang.
	 *
	 * @param array $languages Array of languages.
	 *
	 * @return array             Re-ordered array of languages.
	 */
	public function polylang_language_list( $languages ) {
		$options                = get_option( 'polylang' );
		$model                  = new PLL_Model( $options );
		$this->active_languages = wp_list_pluck( $model->get_languages_list(), 'locale' );
		usort( $languages, array( $this, 'language_list_sort_cb' ) );

		return $languages;
	}

	/**
	 * Re-order language list taking into account active languages in WPGlobus.
	 *
	 * @param array $languages Array of languages.
	 *
	 * @return array             Re-ordered array of languages.
	 */
	public function wpglobus_language_list( $languages ) {
		if ( is_callable( 'WPGlobus', 'Config' ) ) {
			$this->active_languages = WPGlobus::Config()->enabled_locale;
			usort( $languages, array( $this, 'language_list_sort_cb' ) );
		}

		return $languages;
	}

	/**
	 * usort callback to compare two locales based on their appearance in the
	 * active language list.
	 *
	 * @param string $string_a Locale to compare.
	 * @param string $string_b Locale to compare.
	 *
	 * @return int       Ordering guidance as per usort().
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function language_list_sort_cb( $string_a, $string_b ) {
		// If A is in the active list, and B isn't, sort A higher.
		if ( in_array( $string_a['language'], $this->active_languages, true ) &&
			 ! in_array( $string_b['language'], $this->active_languages, true ) ) {
			return - 1;
		}
		// If B is in the active list, and A isn't, sort B higher.
		if ( in_array( $string_b['language'], $this->active_languages, true ) &&
			 ! in_array( $string_a['language'], $this->active_languages, true ) ) {
			return 1;
		}

		// They're either both IN, or both OUT. Sort alphabetically.
		return $string_a['english_name'] > $string_b['english_name'] ? 1 : -1;
	}
}
