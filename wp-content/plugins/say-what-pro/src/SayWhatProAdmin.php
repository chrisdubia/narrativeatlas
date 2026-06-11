<?php

require_once ABSPATH . 'wp-admin/includes/translation-install.php';



/**
 * Say What admin class - controller for all of the admin pages
 *
 * @TODO - Refactor into multiple classes (one main & one per admin screen?)
 *         then re-enable TooManyPublicMethods in phpmd
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SayWhatProAdmin implements SayWhatProAdminInterface {
	/**
	 * @var SayWhatProTemplateLoader
	 */
	protected $template_loader;

	/**
	 * Instance of the settings class.
	 * @var SayWhatProSettingsInterface
	 */
	private $settings;

	/**
	 * @var SayWhatProStringDiscoveryInterface
	 */
	private $string_discovery;

	/**
	 * @var SayWhatProAutocompleteMatcherInterface
	 */
	private $autocomplete_matcher;

	/**
	 * @var SayWhatProListTableFactoryInterface
	 */
	private $list_table_factory;

	/**
	 * @var SayWhatProImporterInterface
	 */
	private $importer;

	/**
	 * Constructor.
	 *
	 * Store the settings instance and other dependencies for later use.
	 *
	 * @param SayWhatProSettingsInterface $settings
	 * @param SayWhatProStringDiscoveryInterface $string_discovery
	 * @param SayWhatProAutocompleteMatcherInterface $autocomplete_matcher
	 * @param SayWhatProListTableFactoryInterface $list_table_factory
	 * @param SayWhatProImporterInterface $importer
	 * @param SayWhatProTemplateLoader $template_loader
	 */
	public function __construct(
		SayWhatProSettingsInterface $settings,
		SayWhatProStringDiscoveryInterface $string_discovery,
		SayWhatProAutocompleteMatcherInterface $autocomplete_matcher,
		SayWhatProListTableFactoryInterface $list_table_factory,
		SayWhatProImporterInterface $importer,
		SayWhatProTemplateLoader $template_loader
	) {
		$this->settings             = $settings;
		$this->string_discovery     = $string_discovery;
		$this->autocomplete_matcher = $autocomplete_matcher;
		$this->list_table_factory   = $list_table_factory;
		$this->importer             = $importer;
		$this->template_loader      = $template_loader;
	}

	/**
	 * Run the admin features.
	 */
	public function run() {
		// Take care of saving stuff before redirects.
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		// Add our admin page.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		// Add a Settings link to the plugin page.
		$plugin_file = basename( dirname( dirname( __FILE__ ) ) ) . '/say-what-pro.php';
		add_filter( 'plugin_action_links_' . $plugin_file, array( $this, 'add_settings_link' ), 11 );
		// Bootstrap the WPListTableExportable class for output buffering if we
		// are on a Say What admin page. e.g. ?page=say_what_admin
		if ( ! empty( $_GET['page'] ) && 'say_what_admin' === $_GET['page'] ) {
			require_once $this->settings->plugin_path . '/vendor/leewillis77/WpListTableExportable/bootstrap.php';
		}
	}

	/**
	 * Add a "Settings" link next to the plugin on the Plugins page.
	 *
	 * @param array $links The existing plugin links.
	 *
	 * @return  array          The revised list of plugin links.
	 */
	public function add_settings_link( $links ) {
		$settings_url  = add_query_arg(
			array( 'page' => 'say_what_admin' ),
			admin_url( 'tools.php' )
		);
		$settings_link = sprintf( '<a href="%s">%s</a>', $settings_url, esc_html__( 'Settings', 'say_what' ) );
		$links[]       = $settings_link;

		return $links;
	}

	/**
	 * Admin init actions.
	 *
	 * Takes care of database upgrades, and saving stuff before redirects.
	 */
	public function admin_init() {

		if ( isset( $_POST['say_what_save'] ) ) {
			$this->save();
		}
		if ( isset( $_POST['say_what_save_wildcard'] ) ) {
			$this->save_wildcard();
		}
		if ( isset( $_GET['say_what_action'] ) && ( 'delete-confirmed' === $_GET['say_what_action'] ) ) {
			$this->admin_delete_confirmed();
		}
		if ( isset( $_GET['say_what_action'] ) && ( 'delete-wildcard-confirmed' === $_GET['say_what_action'] ) ) {
			$this->admin_delete_wildcard_confirmed();
		}
		// Discovery enabling.
		if ( isset( $_GET['say_what_action'] ) && 'discovery' === $_GET['say_what_action'] && ! empty( $_POST['enable'] ) ) {
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'say_what_pro_discovery_toggle' ) ) {
				wp_die( 'Could not validate request.' );
			} else {
				setcookie( 'say-what-pro-discovery-active', true, 0, '/' );
				$_COOKIE['say-what-pro-discovery-active'] = true;
			}
		}
		// Discovery disabling.
		if ( isset( $_GET['say_what_action'] ) && 'discovery' === $_GET['say_what_action'] && ! empty( $_POST['disable'] ) ) {
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'say_what_pro_discovery_toggle' ) ) {
				wp_die( 'Could not validate request.' );
			} else {
				setcookie( 'say-what-pro-discovery-active', false, 0, '/' );
				$_COOKIE['say-what-pro-discovery-active'] = false;
			}
		}
		// Import
		if ( isset( $_GET['say_what_action'] ) && 'import' === $_GET['say_what_action'] && ! empty( $_FILES['say_what_import_file'] ) ) {
			$file = $_FILES['say_what_import_file'];
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'say-what-import' ) || ! current_user_can( 'manage_options' ) ) {
				echo '<div class="error"><p>' . esc_html__( 'Error, you do not have permissions to import replacements.', 'say_what' ) . '</p></div>';
			} elseif ( ! in_array(
				$file['type'],
				[
					'text/csv',
					'application/vnd.ms-excel',
					'application/octet-stream',
				],
				true
			) ) {
				echo '<div class="error"><p>';
				echo sprintf(
				// Translators: %s is the MIME type of the provided file.
					esc_html__( 'Incorrect file type (%s), import request ignored.', 'say_what' ),
					esc_html( $file['type'] )
				);
				echo '</p></div>';
			} else {
				$response = $this->importer->import_file( $file['tmp_name'] );
				if ( ! $response['success'] ) {
					foreach ( $response['errors'] as $error ) {
						echo '<div class="error"><p>' . esc_html( $error ) . '</p></div>';
					}
				} else {
					echo '<div class="updated"><p>' . esc_html( $response['success_message'] ) . '</p></div>';
				}
			}
		}
		add_action( 'wp_ajax_say_what_autocomplete', array( $this, 'autocomplete' ) );
		add_action( 'wp_ajax_say_what_dismiss_notice', array( $this, 'dismiss_notice' ) );
		// Buffer output as we might need to redirect from actions below.
		ob_start();
	}

	/**
	 * Register the menu item for the admin pages
	 */
	public function admin_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			$page = add_management_page(
				esc_html__( 'Text changes', 'say_what' ),
				esc_html__( 'Text changes', 'say_what' ),
				'manage_options',
				'say_what_admin',
				array( $this, 'admin' )
			);
			if ( isset( $_GET['page'] ) && 'say_what_admin' === $_GET['page'] ) {
				add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_scripts' ) );
			}
		}
	}

	/**
	 * Add CSS / javascript to admin pages
	 */
	public function enqueue_scripts() {

		$asset_file = include dirname( plugin_dir_path( __FILE__ ) ) . '/assets/build/admin.asset.php';
		wp_register_script(
			'say_what_admin_js',
			plugins_url( '/say-what-pro/assets/build/admin.js' ),
			[ 'jquery' ],
			$asset_file['version'],
			true
		);
		wp_register_style(
			'say_what_admin_css',
			plugins_url( '/say-what-pro/assets/build/admin.css' ),
			array(),
			$asset_file['version']
		);
		wp_enqueue_style( 'say_what_admin_css' );

		$args = array(
			'autocomplete_url'     => wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'say_what_autocomplete',
					),
					admin_url( 'admin-ajax.php' )
				),
				'say_what_autocomplete'
			),
			'dismiss_notice_url'   => wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'say_what_dismiss_notice',
					),
					admin_url( 'admin-ajax.php' )
				),
				'say_what_dismiss_notice'
			),
			'string_discovery_url' => add_query_arg(
				[
					'page'            => 'say_what_admin',
					'say_what_action' => 'discovery',
				],
				admin_url( 'tools.php' )
			),
		);
		wp_localize_script( 'say_what_admin_js', 'say_what', $args );
		wp_enqueue_script( 'say_what_admin_js' );

		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_register_style(
			'jquery-ui-styles',
			'//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/black-tie/jquery-ui.css',
			array(),
			'1.11.2'
		);
		wp_enqueue_style( 'jquery-ui-styles' );
	}

	/**
	 * The main admin page controller
	 */
	public function admin() {
		$action           = isset( $_GET['say_what_action'] ) ? $_GET['say_what_action'] : 'list';
		$default_active   = '';
		$wildcards_active = '';
		$discovery_active = '';
		$import_active    = '';
		if ( 'list' === $action ) {
			$default_active = ' nav-tab-active';
		} elseif ( 'discovery' === $action ) {
			$discovery_active = ' nav-tab-active';
		} elseif ( 'import' === $action ) {
			$import_active = ' nav-tab-active';
		} elseif ( 'wildcards' === $action ) {
			$wildcards_active = ' nav-tab-active';
		}
		if ( $this->settings->show_multi_lingual() ) {
			$swp_additional_wrap_classes = 'swp-has-multi-lingual';
		} else {
			$swp_additional_wrap_classes = '';
		}

		$notices       = '';
		$admin_notices = get_option( 'swp_admin_notices', [] );
		if ( ! empty( $admin_notices ) ) {

			foreach ( array_keys( $admin_notices ) as $admin_notice ) {
				$notices .= $this->template_loader->get_with_vars(
					'admin',
					'notice',
					$this->get_notice_template_vars( $admin_notice )
				);
			}
		}
		$this->template_loader->output_with_vars(
			'admin',
			'header',
			[
				'default_active'              => $default_active,
				'discovery_active'            => $discovery_active,
				'import_active'               => $import_active,
				'wildcards_active'            => $wildcards_active,
				'swp_additional_wrap_classes' => $swp_additional_wrap_classes,
				'notices'                     => $notices,
			]
		);
		switch ( $action ) {
			case 'addedit':
				$this->admin_addedit();
				break;
			case 'delete':
				$this->admin_delete();
				break;
			case 'deletewildcard':
				$this->admin_delete_wildcard();
				break;
			case 'discovery':
				$this->admin_discovery();
				break;
			case 'import':
				$this->admin_import();
				break;
			case 'wildcards':
				$this->wildcards_list();
				break;
			case 'addeditwildcards':
				$this->admin_addedit_wildcards();
				break;
			case 'list':
			default:
				$this->admin_list();
				break;
		}
		ob_end_flush();
		$this->template_loader->output( 'admin', 'footer' );
	}

	/**
	 * Render the list of currently configured wildcard strings
	 */
	public function wildcards_list() {
		$this->template_loader->output( 'admin', 'wildcard-intro' );
		$this->template_loader->output( 'admin', 'list-table-start' );
		$this->list_table_factory->get_wildcard_list_table()->prepare_items();
		$this->list_table_factory->get_wildcard_list_table()->display();
		$this->template_loader->output( 'admin', 'list-table-end' );
	}

	/**
	 * Render the list of currently configured replacement strings
	 */
	public function admin_list() {
		$list_table = $this->list_table_factory->get_replacement_list_table();

		$list_table->prepare_items();

		$list_table->search_box( esc_html__( 'Search replacements', 'say_what' ), 'swp-replacement' );

		$this->template_loader->output( 'admin', 'list-table-start' );
		$list_table->display();
		$this->template_loader->output( 'admin', 'list-table-end' );
	}

	/**
	 * Render the discovery page.
	 */
	public function admin_discovery() {
		// Do stuff
		$action      = 'enable';
		$action_text = esc_html__( 'Enable', 'say_what' );
		if ( $this->string_discovery->is_active() ) {
			$action      = 'disable';
			$action_text = esc_html__( 'Disable', 'say_what' );
		}
		$this->template_loader->output_with_vars(
			'admin',
			'discovery',
			[
				'action'      => $action,
				'action_text' => $action_text,
			]
		);
	}

	/**
	 * Render the import page.
	 */
	public function admin_import() {
		$multilingual_column = '';
		if ( $this->settings->show_multi_lingual() ) {
			$multilingual_column = $this->template_loader->get( 'admin', 'import-multilingual-column' );
		}
		$this->template_loader->output_with_vars( 'admin', 'import', [ 'multilingual_column' => $multilingual_column ] );
	}

	/**
	 * Show the page asking the user to confirm deletion
	 */
	public function admin_delete() {
		global $wpdb, $table_prefix;
		if ( ! wp_verify_nonce( $_GET['nonce'], 'swdelete' ) ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}
		if ( isset( $_GET['id'] ) ) {
			$sql         = "SELECT * FROM {$table_prefix}say_what_strings WHERE string_id = %d";
			$replacement = $wpdb->get_row( $wpdb->prepare( $sql, $_GET['id'] ) );
		}
		if ( ! $replacement ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}
		$this->template_loader->output_with_vars(
			'admin',
			'delete',
			[
				'id'          => rawurlencode( $_GET['id'] ),
				'nonce'       => rawurlencode( $_GET['nonce'] ),
				'orig_string' => esc_html( $replacement->orig_string ),
			]
		);
	}

	/**
	 * Show the page asking the user to confirm deletion
	 */
	public function admin_delete_wildcard() {
		global $wpdb, $table_prefix;
		if ( ! wp_verify_nonce( $_GET['nonce'], 'swdelete' ) ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}
		if ( isset( $_GET['id'] ) ) {
			$sql      = "SELECT * FROM {$table_prefix}say_what_wildcards WHERE wildcard_id = %d";
			$wildcard = $wpdb->get_row( $wpdb->prepare( $sql, $_GET['id'] ) );
		}
		if ( ! $wildcard ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}

		$this->template_loader->output_with_vars(
			'admin',
			'delete-wildcard',
			[
				'id'       => rawurlencode( $_GET['id'] ),
				'nonce'    => rawurlencode( $_GET['nonce'] ),
				'original' => esc_html( $wildcard->original ),
			]
		);
	}

	/**
	 * Delete the replacement.
	 */
	public function admin_delete_confirmed() {
		global $wpdb, $table_prefix;
		if ( ! wp_verify_nonce( $_GET['nonce'], 'swdelete' ) ||
			 empty( $_GET['id'] ) ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}
		$sql = "DELETE FROM {$table_prefix}say_what_strings WHERE string_id = %d";
		$wpdb->query( $wpdb->prepare( $sql, $_GET['id'] ) );
		$this->settings->invalidate_caches();
		wp_safe_redirect( 'tools.php?page=say_what_admin', '303' );
		exit;
	}

	/**
	 * Delete the wildcard.
	 */
	public function admin_delete_wildcard_confirmed() {
		global $wpdb, $table_prefix;
		if ( ! wp_verify_nonce( $_GET['nonce'], 'swdelete' ) ||
			 empty( $_GET['id'] ) ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}
		$sql = "DELETE FROM {$table_prefix}say_what_wildcards WHERE wildcard_id = %d";
		$wpdb->query( $wpdb->prepare( $sql, $_GET['id'] ) );
		$this->settings->invalidate_caches();
		wp_safe_redirect( 'tools.php?page=say_what_admin&say_what_action=wildcards', '303' );
		exit;
	}

	/**
	 * Render the add/edit page for a replacement
	 */
	public function admin_addedit() {
		global $wpdb, $table_prefix;
		$replacement = false;
		if ( isset( $_GET['id'] ) ) {
			$sql         = "SELECT *
			                  FROM {$table_prefix}say_what_strings
							 WHERE string_id = %d";
			$replacement = $wpdb->get_row( $wpdb->prepare( $sql, $_GET['id'] ) );
		}
		if ( ! $replacement ) {
			$replacement                     = new stdClass();
			$replacement->string_id          = '';
			$replacement->orig_string        = '';
			$replacement->replacement_string = '';
			$replacement->domain             = '';
			$replacement->context            = '';
			$replacement->lang               = '';
			$replacement->disabled           = 0;
		}

		$string_id_field = '';
		if ( ! empty( $replacement->string_id ) ) {
			$string_id_field = $this->template_loader->get_with_vars(
				'admin',
				'add-edit-string-id',
				[ 'string_id' => esc_attr( $replacement->string_id ) ]
			);
		}

		if ( $this->settings->show_multi_lingual() ) {
			$language_options = '';
			$languages        = $this->generate_language_dropdown_list();
			foreach ( $languages as $lang ) {
				$language_text = '';
				if ( ! empty( $lang['language'] ) && ' separator' !== $lang['language'] ) {
					$language_text = '(' . esc_html( $lang['language'] ) . ')';
				}
				$language_options .= $this->template_loader->get_with_vars(
					'admin',
					'add-edit-multilingual-language-option',
					[
						'language_attr' => esc_attr( $lang['language'] ),
						'language_text' => $language_text,
						'selected'      => selected( $replacement->lang, $lang['language'], false ),
						'disabled'      => ' separator' === $lang['language'] ? 'disabled' : '',
						'english_name'  => esc_html( $lang['english_name'] ),
					]
				);

			}

			$multilingual_section = $this->template_loader->get_with_vars(
				'admin',
				'add-edit-multilingual-section',
				[
					'language_options' => $language_options,
				]
			);
		} else {
			$multilingual_section = $this->template_loader->get_with_vars(
				'admin',
				'add-edit-multilingual-hidden',
				[
					'lang' => esc_attr( $replacement->lang ),
				]
			);
		}
		$button_text = esc_html__( 'Add', 'say_what' );
		if ( ! empty( $replacement->string_id ) ) {
			$button_text = esc_html__( 'Update', 'say_what' );
		}
		$disabled_0_selected = ( 0 === (int) $replacement->disabled ) ? 'selected' : '';
		$disabled_1_selected = ( 1 === (int) $replacement->disabled ) ? 'selected' : '';

		$this->template_loader->output_with_vars(
			'admin',
			'add-edit',
			[
				'orig_string'          => esc_textarea( $replacement->orig_string ),
				'domain'               => esc_attr( htmlspecialchars( $replacement->domain ) ),
				'context'              => esc_attr( htmlspecialchars( $replacement->context ) ),
				'replacement_string'   => esc_textarea( $replacement->replacement_string ),
				'button_text'          => esc_attr( $button_text ),
				'string_id_field'      => $string_id_field,
				'multilingual_section' => $multilingual_section,
				'disabled_0_selected'  => $disabled_0_selected,
				'disabled_1_selected'  => $disabled_1_selected,
			]
		);
	}

	/**
	 * Generate a language list to generate the dropdown from.
	 *
	 * @return array    Array of language options.
	 */
	private function generate_language_dropdown_list() {
		$languages = wp_get_available_translations();
		// en_US isn't returned by get_available_translations().
		$languages['en_US'] = array(
			'language'     => 'en_US',
			'english_name' => esc_html__( 'English (United States)' ),
		);

		$languages['separator'] = array(
			'english_name' => esc_html__( '------------', 'say_what' ),
			'language'     => ' separator',
		);

		// Sort the list
		$languages = apply_filters( 'say_what_pro_language_list', $languages );
		array_unshift(
			$languages,
			array(
				'english_name' => esc_html__( 'Any', 'say_what' ),
				'language'     => '',
			)
		);

		return $languages;
	}

	/**
	 * Render the add/edit page for a wildcard.
	 */
	public function admin_addedit_wildcards() {
		global $wpdb, $table_prefix;
		$wildcard = false;
		if ( isset( $_GET['id'] ) ) {
			$sql      = "SELECT *
			                  FROM {$table_prefix}say_what_wildcards
							 WHERE wildcard_id = %d";
			$wildcard = $wpdb->get_row( $wpdb->prepare( $sql, $_GET['id'] ) );
		}
		if ( ! $wildcard ) {
			$wildcard              = new stdClass();
			$wildcard->wildcard_id = '';
			$wildcard->original    = '';
			$wildcard->replacement = '';
			$wildcard->lang        = '';
		}

		$wildcard_id_field = '';
		if ( ! empty( $wildcard->wildcard_id ) ) {
			$wildcard_id_field = $this->template_loader->get_with_vars(
				'admin',
				'add-edit-wildcard-wildcard-id',
				[ 'wildcard_id' => esc_attr( $wildcard->wildcard_id ) ]
			);
		}
		if ( $this->settings->show_multi_lingual() ) {
			$language_options = '';
			$languages        = $this->generate_language_dropdown_list();
			foreach ( $languages as $lang ) {
				$language_text = '';
				if ( ! empty( $lang['language'] ) && ' separator' !== $lang['language'] ) {
					$language_text = '(' . esc_html( $lang['language'] ) . ')';
				}
				$language_options .= $this->template_loader->get_with_vars(
					'admin',
					'add-edit-wildcard-multilingual-language-option',
					[
						'language_attr' => esc_attr( $lang['language'] ),
						'language_text' => $language_text,
						'selected'      => selected( $wildcard->lang, $lang['language'], false ),
						'disabled'      => ' separator' === $lang['language'] ? 'disabled' : '',
						'english_name'  => esc_html( $lang['english_name'] ),
					]
				);

			}

			$multilingual_section = $this->template_loader->get_with_vars(
				'admin',
				'add-edit-wildcard-multilingual-section',
				[
					'language_options' => $language_options,
				]
			);
		} else {
			$multilingual_section = $this->template_loader->get_with_vars(
				'admin',
				'add-edit-wildcard-multilingual-hidden',
				[
					'lang' => esc_attr( $wildcard->lang ),
				]
			);
		}

		if ( ! empty( $wildcard->wildcard_id ) ) {
			$button_text = esc_html__( 'Update', 'say_what' );
		} else {
			$button_text = esc_html__( 'Add', 'say_what' );
		}
		$this->template_loader->output_with_vars(
			'admin',
			'add-edit-wildcard',
			[
				'original'             => esc_textarea( $wildcard->original ),
				'replacement'          => esc_textarea( $wildcard->replacement ),
				'button_text'          => esc_attr( $button_text ),
				'wildcard_id_field'    => $wildcard_id_field,
				'multilingual_section' => $multilingual_section,
			]
		);
	}

	/**
	 * Strip CRs out of strings. array_walk() callback.
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function strip_cr_callback( &$val, $key ) {
		$val = str_replace( "\r\n", "\n", $val );
	}

	/**
	 * Something on the admin pages needs saved. Handle it here
	 * Output error/warning messages as required
	 */
	private function save() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'swaddedit' ) ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}
		$_POST = stripslashes_deep( $_POST );
		array_walk( $_POST, array( $this, 'strip_cr_callback' ) );
		if ( isset( $_POST['say_what_string_id'] ) ) {
			$this->settings->update_replacement(
				$_POST['say_what_string_id'],
				$_POST['say_what_orig_string'],
				$_POST['say_what_domain'],
				$_POST['say_what_context'],
				$_POST['say_what_replacement_string'],
				$_POST['say_what_disabled'],
				$_POST['say_what_lang']
			);
		} else {
			$this->settings->insert_replacement(
				$_POST['say_what_orig_string'],
				$_POST['say_what_domain'],
				$_POST['say_what_context'],
				$_POST['say_what_replacement_string'],
				$_POST['say_what_disabled'],
				$_POST['say_what_lang']
			);
		}
		$this->settings->invalidate_caches();
		wp_safe_redirect( 'tools.php?page=say_what_admin', '303' );
		exit;
	}

	/**
	 * A wildcard on the admin pages needs saved. Handle it here
	 * Output error/warning messages as required
	 */
	private function save_wildcard() {
		global $wpdb, $table_prefix;
		if ( ! wp_verify_nonce( $_POST['nonce'], 'swaddedit' ) ) {
			wp_die( esc_html__( 'Did you really mean to do that? Please go back and try again.', 'say_what' ) );
		}
		$_POST = stripslashes_deep( $_POST );
		array_walk( $_POST, array( $this, 'strip_cr_callback' ) );
		if ( isset( $_POST['say_what_wildcard_id'] ) ) {
			$sql = "UPDATE {$table_prefix}say_what_wildcards
					   SET original = %s,
						   replacement = %s,
						   lang = %s
					 WHERE wildcard_id = %d";
			$wpdb->query(
				$wpdb->prepare(
					$sql,
					$_POST['say_what_original'],
					$_POST['say_what_replacement'],
					$_POST['say_what_lang'],
					$_POST['say_what_wildcard_id']
				)
			);
		} else {
			$sql = "INSERT INTO {$table_prefix}say_what_wildcards
		                 VALUES ( NULL, %s, %s, %s )";
			$wpdb->query(
				$wpdb->prepare(
					$sql,
					$_POST['say_what_original'],
					$_POST['say_what_replacement'],
					$_POST['say_what_lang']
				)
			);
		}
		$this->settings->invalidate_caches();
		wp_safe_redirect( 'tools.php?page=say_what_admin&say_what_action=wildcards', '303' );
		exit;
	}

	/**
	 * AJAX callback that provides autocomplete suggestions.
	 *
	 * @return array Array of suggestions.
	 */
	public function autocomplete() {
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'say_what_autocomplete' ) ) {
			echo wp_json_encode( array() );
			exit;
		}
		$term = isset( $_GET['term'] ) ? $_GET['term'] : '';
		if ( '' === $term ) {
			echo wp_json_encode( array() );
			exit;
		}
		echo wp_json_encode( $this->autocomplete_matcher->match( $term ) );
		exit();
	}

	/**
	 * Dismiss admin notices.
	 */
	public function dismiss_notice() {
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'say_what_dismiss_notice' ) ) {
			exit;
		}
		$notice  = isset( $_POST['notice'] ) ? $_POST['notice'] : '';
		$notices = get_option( 'swp_admin_notices', [] );
		if ( isset( $notices[ $notice ] ) ) {
			unset( $notices[ $notice ] );
			update_option( 'swp_admin_notices', $notices );
		}
		exit();
	}

	/**
	 * Map a notice key to a notice type and message.
	 *
	 * @param $admin_notice
	 *
	 * @return array
	 */
	private function get_notice_template_vars( $admin_notice ) {
		// Array of lookup values.
		$message  = esc_html__( 'String discovery suggestions have been cleared out as part of the most recent upgrade. Enable String Discovery to capture suggested strings again.', 'say_what' );
		$message .= '<br><strong>';
		$message .= esc_html__( 'Your existing string replacements are unaffected.', 'say_what' );
		$message .= '</strong>';
		$messages = [
			'swp_suggestion_refresh' => [
				'type'    => 'notice-info',
				'message' => $message,
			],
		];

		$result = isset( $messages[ $admin_notice ] ) ?
			$messages[ $admin_notice ] :
			[
				'type'    => 'notice-info',
				'message' => $admin_notice,
			];

		$result['key'] = esc_attr( $admin_notice );

		return $result;
	}
}
