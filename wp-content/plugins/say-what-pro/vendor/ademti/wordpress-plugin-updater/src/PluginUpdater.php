<?php

namespace Ademti\WordPress;

class PluginUpdater {

	// Instance of the EDD SL updater.
	private $edd_sl_plugin_updater;

	// The main plugin filename that we're performing updates for.
	private $file;

	// The internal slug for this plugin (Used to generate option names for saving licences to the DB).
	private $slug;

	// The current plugin version.
	private $version;

	// The current plugin's name.
	private $item_name;

	// The current plugin's item ID.
	private $item_id;

	// The URL of the update server.
	private $update_server = '';

	// The prefix to be used for options in the DB.
	private $option_prefix = '';

	// The support URL to show for problems.
	private $support_url = '';

	// The purchase link to show in the case of an expired license.
	private $purchase_link = '';

	// The encoded purchase code to show & discount percentage in the case of an expired license.
	private $purchase_code = '';

	/**
	 * Constructor.
	 * Add actions to hook in at the appropriate places
	 *
	 * @param string $file The main plugin file.
	 * @param string $slug A unique slug for this plugin.
	 * @param string $update_server The update server base URL
	 * @param string $option_prefix Prefix for options saved in the DB
	 * @param string $support_url Support URL to link to for problems
	 */
	public function __construct( $file, $slug, $update_server, $option_prefix, $support_url ) {
		// Assign properties.
		$this->file          = $file;
		$this->slug          = $slug;
		$this->update_server = $update_server;
		$this->option_prefix = $option_prefix;
		$this->support_url   = $support_url;

		// Add hooks to trigger behaviours.
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_menu', [ $this, 'add_licence_admin_menu' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( $file ), [ $this, 'add_licence_admin_link' ] );
		add_filter( 'extra_plugin_headers', [ $this, 'extra_plugin_headers' ], 10, 1 );

		// Extract info about the plugin from the plugin file.
		// Note: The calls to get_plugin_info() have to be *after* the 'extra_plugin_headers' filter has been added
		$this->version       = $this->get_plugin_info( $this->file, 'Version' );
		$this->item_name     = $this->get_plugin_info( $this->file, 'Name' );
		$this->item_id       = $this->get_plugin_info( $this->file, 'Ademti Update ID' );
		$this->purchase_link = $this->get_plugin_info( $this->file, 'Ademti Purchase Link' );
		$this->purchase_code = $this->get_plugin_info( $this->file, 'Ademti Purchase Code' );
	}

	/**
	 * Register the header holding the update ID.
	 *
	 * @param $headers
	 *
	 * @return mixed
	 */
	public function extra_plugin_headers( $headers ) {
		$headers['Ademti Update ID']     = 'Ademti Update ID';
		$headers['Ademti Purchase Link'] = 'Ademti Purchase Link';
		$headers['Ademti Purchase Code'] = 'Ademti Purchase Code';

		return $headers;
	}

	/**
	 * Run on init.
	 *
	 * Loads licence details, and instantiates the main EDD SL plugin class.
	 */
	public function init() {
		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		$licence = trim( get_option( $this->option_prefix . '_licence_' . $this->slug ) );

		$this->edd_sl_plugin_updater = new EDD_SL_Plugin_Updater(
			$this->update_server,
			$this->file,
			[
				'version'   => $this->version,    // Current version number.
				'license'   => $licence,          // Licence key.
				'item_name' => $this->item_name,  // Name of this plugin.
				'item_id'   => $this->item_id,    // Item ID of the plugin
				'author'    => 'Ademti Software', // Author of this plugin.
				'beta'      => false,
			]
		);

	}


	/**
	 * Add an "Enter Licence Key" link next to the plugin on the Plugins page.
	 *
	 * @param array $links The existing plugin links.
	 *
	 * @return  array          The revised list of plugin links.
	 */
	public function add_licence_admin_link( $links ) {
		$settings_link = '<a href="options-general.php?page=ademti_licence_' . $this->slug . '">Enter licence key</a>';
		$links[]       = $settings_link;

		return $links;
	}


	/**
	 * Make sure the enter licence key page is accessible even though it's not in the menu.
	 */
	public function add_licence_admin_menu() {
		global $_registered_pages;
		$hookname = get_plugin_page_hookname( 'ademti_licence_' . $this->slug, 'options-general.php' );
		if ( ! empty ( $hookname ) ) {
			add_action( $hookname, [ $this, 'licence_admin_page' ] );
		}
		$_registered_pages[ $hookname ] = true;
	}


	/**
	 * Check the licence, register it and save it.
	 */
	public function save_licence() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ademti_licence_code_entry' ) ) {
			wp_die( 'Could not validate your request. Please go back and try again.', 'ademti_updater' );
		}

		$is_valid = $this->validate_licence( trim( $_POST['licence_code'] ) );

		if ( $is_valid ) {
			echo "<div id='message' class='updated'><p>Thank you, your licence has been validated and saved.</p></div>";
		} else {
			echo "<div id='message' class='error'><p>Sorry, but your licence cannot be validated.</p>";
			if ( ! empty( $this->last_validation_message ) ) {
				echo '<p>' . $this->last_validation_message . '</p>';
			}
			echo "<p>If you believe this is an error please <a target='_blank' href='" . $this->support_url . "'>contact support</a>.</p></div>";
		}
	}


	/**
	 * Register the licence with the licensing server.
	 *
	 * @param string $licence The licence code to be validated.
	 *
	 * @return bool              True if the licence is valid, false if not.
	 */
	public function validate_licence( $licence ) {

		// Call the custom API.
		$response = wp_remote_post(
			$this->update_server,
			[
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => [
					'edd_action'  => 'activate_license',
					'license'     => $licence,
					'item_id'     => $this->item_id,
					'item_name'   => rawurlencode( $this->item_name ),
					'url'         => home_url(),
					'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
				],
			]
		);

		// Make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			$this->last_validation_message = $response->get_error_message();

			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->last_validation_message = __( 'Either the licence is invalid, or is already in use on another site.', 'ademti_updater' );

			return false;
		}

		// Decode and save the licence data.
		$licence_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $licence_data->success == true && $licence_data->license == 'valid' ) {
			update_option( $this->option_prefix . '_licence_status_' . $this->slug, $licence_data->license );
			update_option( $this->option_prefix . '_licence_' . $this->slug, $licence );

			return true;
		}

		$this->last_validation_message = $this->get_validation_message( $licence_data );

		return false;
	}

	/**
	 * @return string
	 */
	private function get_validation_message( $licence_data ) {
		switch ( $licence_data->error ) {
			case 'expired':
				$expired_at = date_i18n(
					get_option( 'date_format' ),
					strtotime( $licence_data->expires, current_time( 'timestamp' ) )
				);
				// Vary the message depending on whether we have a purchase_link / purchase_code defined.
				if ( ! empty( $this->purchase_link ) && ! empty( $this->purchase_code ) ) {
					// Purchase link & discount code.
					list( $discount_amt, $discount_code ) = explode( ':', $this->purchase_code );
					$discount_code  = base64_decode( $discount_code );
					$msg            = __( 'Your license key expired on %1$s, and was not renewed.', 'ademti_updater' );
					$msg           .= __( '<br>You need to <a target="_blank" rel="noopener noreferrer" href="%2$s">purchase a fresh license</a> to access updates &amp; support.', 'ademti_updater' );
					$msg           .= __( '<br>You can use the code <strong>%3$s</strong> for a %4$s%% discount.', 'ademti_updater' );

					return sprintf( $msg, $expired_at, $this->purchase_link, $discount_code, $discount_amt );
				} elseif ( ! empty( $this->purchase_link ) ) {
					// Purchase link, but no discount code.
					/* translators: 1: license key expiration date, 2: URL to purchase a license */
					$msg  = __( 'Your license key expired on %1$s, and was not renewed.', 'ademti_updater' );
					$msg .= __( '<br>You need to <a target="_blank" rel="noopener noreferrer" href="%2$s">purchase a fresh license</a> to access updates &amp; support.', 'ademti_updater' );

					return sprintf( $msg, $expired_at, $this->purchase_link );
				} else {
					// No purchase link, so just state that plugin has expired.

					/* translators: 1: license key expiration date */
					$msg = __( 'Your license key expired on %1$s, and was not renewed.', 'ademti_updater' );

					return sprintf( $msg, $expired_at );
				}
				break;
			case 'disabled':
			case 'revoked':
				return __( 'Your license key is disabled.', 'ademti_updater' );
				break;
			case 'invalid':
			case 'site_inactive':
				return __( 'Your license is not active for this URL.', 'ademti_updater' );
				break;
			case 'missing':
			case 'item_name_mismatch':
				/* translators: the plugin name */
				return sprintf( __( 'This appears to be an invalid license key for %s.', 'ademti_updater' ), $this->item_name );
				break;
			case 'no_activations_left':
				return __( 'Your license key has reached its activation limit.', 'ademti_updater' );
				break;
		}

		return '';
	}

	/**
	 * De-register a licence from a site.
	 */
	public function remove_licence() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ademti_licence_code_removal' ) ) {
			wp_die( 'Could not validate your request. Please go back and try again.', 'ademti_updater' );
		}

		$licence     = trim( get_option( $this->option_prefix . '_licence_' . $this->slug ) );
		$environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';

		// Call the custom API.
		$response = wp_remote_post(
			$this->update_server,
			[
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => [
					'edd_action'  => 'deactivate_license',
					'license'     => $licence,
					'item_id'     => $this->item_id,
					'item_name'   => rawurlencode( $this->item_name ),
					'url'         => home_url(),
					'environment' => $environment,
				],
			]
		);

		if ( ! is_wp_error( $response ) &&
			 200 === wp_remote_retrieve_response_code( $response ) ) {

			$licence_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( 'deactivated' == $licence_data->license ) {
				delete_option( $this->option_prefix . '_licence_status_' . $this->slug );
				delete_option( $this->option_prefix . '_licence_' . $this->slug );
				echo "<div id='message' class='updated'><p>Thank you, your licence has been removed from this site.</p></div>";

				return;
			}
		}
		echo "<div id='message' class='error'><p>Sorry, your licence cannot be removed at this time. Please try again later.</p></div>";
	}


	/**
	 * Show an admin page where the user can activate / deactivate their licence.
	 */
	public function licence_admin_page() {

		// Deal with form submissions.
		if ( isset( $_POST['licence_code'] ) ) {
			$this->save_licence();
		}

		// Deal with licence removals.
		if ( isset( $_POST['Remove_licence'] ) ) {
			$this->remove_licence();
		}

		$licence_code = trim( get_option( $this->option_prefix . '_licence_' . $this->slug ) );
		$licence_code = ! empty( $licence_code ) ? $licence_code : '';

		?>
        <div class="wrap">
        <h2>Licence Code Management</h2>
        <h4><?php echo esc_html( $this->get_plugin_info( $this->file, 'Name' ) ); ?></h4>
        <form method="post">
			<?php wp_nonce_field( 'ademti_licence_code_entry' ); ?>
            <p><label for="licence_code">Licence Code: </label><input type="text" size="40" name="licence_code"
                                                                      placeholder="Enter your licence code"
                                                                      value="<?php esc_attr_e( $licence_code ); ?>"></p>
            <p><input type="submit" class="button-primary" name="Save" value="Save"></p>
        </form>
		<?php if ( ! empty( $licence_code ) ) : ?>
            <form method="post">
				<?php wp_nonce_field( 'ademti_licence_code_removal' ); ?>
                <p><input type="submit" class="button-secondary" name="Remove licence" value="Remove licence"></p>
            </form>
		<?php endif; ?>
		<?php

	}

	/**
	 * Get the information about a plugin given its main file, and header key.
	 *
	 * @param string $file The main plugin file
	 *
	 * @return string         The version number of the plugin
	 */
	private function get_plugin_info( $file, $key ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( $file ) ) );
		$plugin_file   = basename( $file );

		return $plugin_folder[ $plugin_file ][ $key ];
	}
}
