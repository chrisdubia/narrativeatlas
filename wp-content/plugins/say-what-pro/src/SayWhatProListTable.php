<?php

use leewillis77\WpListTableExportable\WpListTableExportable;

/**
 * List table class for the admin pages.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SayWhatProListTable extends WPListTableExportable implements SayWhatProListTableInterface {

	/**
	 * @var SayWhatProTemplateLoader
	 */
	protected $template_loader;

	/**
	 * @var SayWhatProSettingsInterface
	 */
	private $settings;

	/**
	 * @var array
	 */
	private $translations;

	/**
	 * Constructor
	 *
	 * @param SayWhatProSettingsInterface $settings
	 * @param SayWhatProTemplateLoader $template_loader
	 */
	public function __construct(
		SayWhatProSettingsInterface $settings,
		SayWhatProTemplateLoader $template_loader
	) {
		$this->settings        = $settings;
		$this->template_loader = $template_loader;
		$this->translations    = wp_get_available_translations();
		parent::__construct();
		$this->export_button_text = esc_html__( 'Export replacements', 'say_what' );
	}

	/**
	 * Description shown when no replacements configured
	 */
	public function no_items() {
		if ( ! empty( $_GET['s'] ) ) {
			echo sprintf(
				// Translators: %s is the search term.
				esc_html__( 'No string replacements matching &quot;%s&quot;.', 'say_what' ),
				esc_html( $_GET['s'] )
			);

			return;
		}
		echo esc_html__( 'No string replacements configured yet.', 'say_what' );
	}

	/**
	 * Specify the list of columns in the table
	 * @return array The list of columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'                 => '<input type="checkbox" />',
			'string_id'          => esc_html_x( 'ID', 'Unique ID of the string replacement', 'say_what' ),
			'replacement_active' => esc_html_x( 'Active?', 'Heading for column in admin table indicating if replacement is active', 'say_what' ),
			'orig_string'        => esc_html__( 'Original string', 'say_what' ),
			'domain'             => esc_html__( 'Text domain', 'say_what' ),
			'context'            => esc_html__( 'Text context', 'say_what' ),
			'replacement_string' => esc_html__( 'Replacement string', 'say_what' ),
		);
		if ( $this->settings->show_multi_lingual() ) {
			$columns['lang'] = esc_html__( 'Affected language', 'say_what' );
		}

		return $columns;
	}

	/**
	 * Set the primary column.
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'orig_string';
	}

	/**
	 * Retrieve the items for display
	 */
	public function prepare_items() {

		global $wpdb, $table_prefix;

		$this->process_bulk_actions();

		$columns               = $this->get_columns();
		$hidden                = array( 'string_id', 'replacement_active' );
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// We don't use the replacements from the settings object, we query them separately to make
		// ordering/searching/pagination easier. This may turn out bad if people have "lots"
		$sql = "SELECT * FROM {$table_prefix}say_what_strings";

		// Handle searching.
		if ( ! empty( $_GET['s'] ) ) {
			$sql .= ' WHERE orig_string LIKE %s
			            OR replacement_string LIKE %s
			            OR domain LIKE %s';
		}

		// Handle sorting
		$sql .= ' ORDER BY ';
		if ( isset( $_GET['orderby'] ) ) {
			$order_by = $_GET['orderby'];
			if ( isset( $_GET['order'] ) ) {
				$order_by .= ' ' . $_GET['order'];
			}
		} else {
			$order_by = 'orig_string ASC';
		}
		$sql .= sanitize_sql_orderby( $order_by );

		// Replace search placeholders if required.
		if ( ! empty( $_GET['s'] ) ) {
			$value = '%' . $_GET['s'] . '%';
			$sql   = $wpdb->prepare( $sql, $value, $value, $value );
		}

		// Retrieve the results.
		$this->items = $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Indicate which columns are sortable
	 * @return array A list of the columns that are sortable.
	 */
	public function get_sortable_columns() {
		return array(
			'orig_string'        => array( 'orig_string', true ),
			'domain'             => array( 'domain', false ),
			'context'            => array( 'context', false ),
			'replacement_string' => array( 'replacement_string', false ),
		);
	}

	/**
	 * Specify the bulk actions available.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'disable' => esc_html__( 'Disable replacements', 'say_what' ),
			'enable'  => esc_html__( 'Enable replacements', 'say_what' ),
			'delete'  => esc_html__( 'Delete', 'say_what' ),
		);

		return $actions;
	}

	/**
	 * Renders the search box.
	 *
	 * @param string $text
	 * @param string $input_id
	 */
	public function search_box( $text, $input_id ) {

		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		$this->template_loader->output_with_vars(
			'admin',
			'replacement-search-box',
			[
				'input_id'      => esc_attr( $input_id ),
				'text'          => esc_html( $text ),
				'submit_button' => get_submit_button(
					$text,
					'',
					'',
					false,
					array( 'id' => 'search-submit' )
				),
			]
		);
	}

	/**
	 * Bulk action controller.
	 */
	public function process_bulk_actions() {
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Nope! Security check failed!' );
			}
		}
		$action = $this->current_action();
		switch ( $action ) {
			case 'delete':
				if ( ! empty( $_POST['string_id'] ) ) {
					$this->process_bulk_delete( $_POST['string_id'] );
				}
				break;
			case 'enable':
				if ( ! empty( $_POST['string_id'] ) ) {
					$this->process_bulk_set_disabled( $_POST['string_id'], 0 );
				}
				break;
			case 'disable':
				if ( ! empty( $_POST['string_id'] ) ) {
					$this->process_bulk_set_disabled( $_POST['string_id'], 1 );
				}
				break;
			default:
				break;
		}
	}

	private function process_bulk_set_disabled( $ids, $disabled ) {
		global $wpdb, $table_prefix;
		$id_list = implode( ',', array_map( 'intval', $ids ) );
		$wpdb->query(
			"UPDATE {$table_prefix}say_what_strings SET disabled=" . $disabled . ' WHERE string_id IN (' . $id_list . ')'
		);
		$this->settings->invalidate_caches();
		wp_safe_redirect( 'tools.php?page=say_what_admin', '303' );
		exit;
	}

	/**
	 * Process the delete bulk action for a list of IDs.
	 *
	 * @param array $ids Array of IDs to remove.
	 */
	private function process_bulk_delete( $ids ) {
		global $wpdb, $table_prefix;
		$id_list = implode( ',', array_map( 'intval', $ids ) );
		$wpdb->query(
			"DELETE FROM {$table_prefix}say_what_strings WHERE string_id IN (" . $id_list . ')'
		);
		$this->settings->invalidate_caches();
		wp_safe_redirect( 'tools.php?page=say_what_admin', '303' );
		exit;
	}

	/**
	 * Add styles to the rows to indicate active status.
	 *
	 * @param array|object $item
	 */
	public function single_row( $item ) {
		$row_class = $item['disabled'] ?
			'row-replacement-disabled' :
			'row-replacement-active';
		echo '<tr class="' . esc_attr( $row_class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Checkboxes for the rows.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="string_id[]" value="%d" />',
			$item['string_id']
		);
	}

	public function column_replacement_active( $item ) {
		return $item['disabled'] ?
			esc_html__( 'No', 'say_what' ) :
			esc_html__( 'Yes', 'say_what' );
	}

	/**
	 * Output column data.
	 */
	public function column_default( $item, $column_name ) {
		return esc_html( htmlspecialchars( $item[ $column_name ] ) );
	}

	/**
	 * Output column data.
	 */
	public function column_lang( $item ) {
		if ( empty( $item['lang'] ) ) {
			return esc_html__( 'Any', 'say_what' );
		} else {
			if ( ! empty( $this->translations[ $item['lang'] ] ) ) {
				return esc_html( $this->translations[ $item['lang'] ]['english_name'] ) . ' (' . $item['lang'] . ')';
			}

			return esc_html( $item['lang'] );
		}
	}

	/**
	 * Output column data.
	 */
	public function column_csv_lang( $item ) {
		if ( empty( $item['lang'] ) ) {
			return '';
		} else {
			return $item['lang'];
		}
	}

	/**
	 * Output the orig_string column.
	 *
	 * Includes row actions.
	 *
	 * @param array $item The row item.
	 *
	 * @return string       The output for the column.
	 */
	public function column_orig_string( $item ) {
		global $disable_say_what_replacements;
		$data                          = esc_html( htmlspecialchars( $item['orig_string'] ) );
		$disable_say_what_replacements = true;
		// phpcs:disable WordPress.WP.I18n
		if ( empty( $item['context'] ) && empty( $item['domain'] ) ) {
			$translation = __( $item['orig_string'] );
		} elseif ( empty( $item['context'] ) ) {
			$translation = __( $item['orig_string'], $item['domain'] );
		} elseif ( empty( $item['domain'] ) && ! empty( $item['context'] ) ) {
			$translation = _x( $item['orig_string'], $item['context'] );
		} else {
			$translation = _x( $item['orig_string'], $item['context'], $item['domain'] );
		}
		// phpcs:enable
		if ( $translation !== $item['orig_string'] ) {
			$data .= '<br><em>(' . esc_html( htmlspecialchars( $translation ) ) . ')</em>';
		}
		$disable_say_what_replacements = false;

		$disabled = $item['disabled'] ? '<small><em>' . esc_html__( '(disabled)', 'say_what' ) . '</em></small>' : '';

		return $data . ' ' . $disabled . ' ' . $this->generate_row_actions( $item );
	}

	/**
	 * Output the orig_string column for CSV output.
	 *
	 * @param array $item The row item.
	 *
	 * @return string       The output for the column.
	 */
	public function column_csv_orig_string( $item ) {
		return $item['orig_string'];
	}

	/**
	 * Generate the row actions markup.
	 *
	 * @param array $item The row item.
	 *
	 * @return array        Array of row action links.
	 */
	private function generate_row_actions( $item ) {
		return $this->row_actions(
			array(
				'edit'   => '<a href="tools.php?page=say_what_admin&amp;say_what_action=addedit&amp;id=' .
							rawurlencode( $item['string_id'] ) .
							'&amp;nonce=' .
							rawurlencode( wp_create_nonce( 'swaddedit' ) ) .
							'">' .
							esc_html__( 'Edit', 'say_what' ) .
							'</a>',
				'delete' => '<a href="tools.php?page=say_what_admin&say_what_action=delete&id=' .
							rawurlencode( $item['string_id'] ) .
							'&nonce=' .
							rawurlencode( wp_create_nonce( 'swdelete' ) ) .
							'">' .
							esc_html__( 'Delete', 'say_what' ) .
							'</a>',
			)
		);
	}

	/**
	 * Make sure that the ID column isn't hidden when exporting.
	 */
	public function hidden_columns_csv() {
		return array();
	}
}
