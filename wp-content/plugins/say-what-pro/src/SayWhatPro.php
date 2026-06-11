<?php

/**
 * Main plugin class, responsible for triggering everything
 */
class SayWhatPro {

	/**
	 * DI Container.
	 * @var Pimple\Container
	 */
	private $container;

	/**
	 * Holds the status of the free plugin.
	 * @var bool
	 */
	private $free_plugin_status;

	/**
	 * Constructor
	 */
	public function __construct( Pimple\Container $container ) {
		// Store the DI container.
		$this->container = $container;
	}

	/**
	 * Run the plugin functionality.
	 */
	public function run() {
		// Run the admin class if needed.
		if ( is_admin() ) {
			$this->container['SayWhatProAdmin']->run();
			$this->container['SayWhatProDbManager']->run();
			$this->container['SayWhatProMultiLingualAdmin']->run();
		}

		// Run the WP-CLI integration if required.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'say-what', 'SayWhatProCli' );
		}

		// Run the string discovery class.
		$this->container['SayWhatProStringDiscovery']->run();

		// Run the frontend class.
		$this->container['SayWhatProFrontend']->run();

		// Try and deactivate the free plugin if present.
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		// Set up i18n for the plugin.
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Fires on plugins_loaded().
	 *
	 * Check the status of the free plugin, and queue deactivation if required.
	 */
	public function plugins_loaded() {
		$this->free_plugin_status = null;
		if ( class_exists( 'SayWhat' ) ) {
			$this->free_plugin_status = true;
			add_action( 'admin_init', array( $this, 'deactivate_say_what_free' ) );
			add_action( 'admin_notices', array( $this, 'admin_notice_say_what_free' ) );
		}
	}

	/**
	 * Fires on init().
	 *
	 * Set up translation for the plugin itself.
	 */
	public function init() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'say_what' );
		load_textdomain( 'say_what', WP_LANG_DIR . '/say_what/say_what-' . $locale . '.mo' );
		load_plugin_textdomain( 'say_what', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Try and deactivate the free plugin.
	 */
	public function deactivate_say_what_free() {
		// Try and find the active name in case the install folder has a
		// non-standard name.
		$all_plugins = get_option( 'active_plugins', array() );
		foreach ( $all_plugins as $plugin ) {
			if ( false !== stripos( $plugin, 'say-what.php' ) ) {
				deactivate_plugins( $plugin );
				$this->free_plugin_status = false;
			}
		}
	}

	/**
	 * Show an admin notice about PHP requirements.
	 */
	public function admin_notice_say_what_free() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		// We have found the plugin, and deactivated it.
		if ( false === $this->free_plugin_status ) {
			echo '<div class="error"><p>' . sprintf( esc_html__( '%1$sSay What? Pro%2$s replaces the free version, so we have deactivated the free version. You can remove the free plugin when convenient.', 'say_what' ), '<strong>', '</strong>' ) . '</p></div>';
		} elseif ( true === $this->free_plugin_status ) {
			// We think the free plugin is installed, but cannot find it to deactivate.
			echo '<div class="error"><p>';
			esc_html_e( 'Say What? Pro replaces the free version plugin.', 'say_what' );
			echo '</p><p><strong>';
			esc_html_e( 'Please deactivate Say What?', 'say_what' );
			echo '</strong></p></div>';
		}
	}

}
