<?php
/**
 * Bootstrapper. Initializes the plugin.
 *
 * @package    User_Insights_Forum_Participation
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2024, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace User_Insights_Forum_Participation\Bootstrap;

use User_Insights_Forum_Participation\Core\Filters_Helper;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Bootstrapper.
 */
class Bootstrapper {

	/**
	 * Setup the bootstrapper.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Bind hooks
	 */
	private function setup() {
		add_action( 'usin_loaded', array( $this, 'load' ) );
		add_action( 'bp_init', array( $this, 'load_translations' ) );
	}

	/**
	 * Load core functions/template tags.
	 * These are non auto loadable constructs.
	 */
	public function load() {
		Filters_Helper::boot();
	}

	/**
	 * Load translations.
	 */
	public function load_translations() {

		if ( ! function_exists( 'usin_modules' ) ) {
			return;
		}

		load_plugin_textdomain(
			'user-insights-forum-participation',
			false,
			basename( user_insights_forum_participation()->path ) . '/languages'
		);
	}
}
