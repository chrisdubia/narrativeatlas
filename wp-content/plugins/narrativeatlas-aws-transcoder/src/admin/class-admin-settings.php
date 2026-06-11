<?php
/**
 * Admin Settings Pages Helper.
 *
 * @package    Narrativeatlas_AWS_Transcoder
 * @subpackage Admin
 * @copyright  Copyright (c) 2018, BuddyDev.Com
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Ravi Sharma, Brajesh Singh
 * @since      1.0.0
 */

namespace Narrativeatlas_AWS_Transcoder\Admin;

use \Press_Themes\PT_Settings\Page;
use Narrativeatlas_AWS_Transcoder\Services\Media_Convert\Media_Convert_Admin_Settings;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Admin settings class.
 */
class Admin_Settings {

	/**
	 * Admin Menu slug.
	 *
	 * @var string
	 */
	private $menu_slug;

	/**
	 * Used to keep a reference of the Page, It will be used in rendering the view.
	 *
	 * @var \Press_Themes\PT_Settings\Page
	 */
	private $page;

	/**
	 * Boots settings class.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup callbacks on different hooks.
	 */
	public function setup() {
		$this->menu_slug = 'aws-transcoder-settings';
		$basename        = narrative_aws_transcoder()->basename;

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_filter( "plugin_action_links_{$basename}", array( $this, 'filter_links' ) );
	}

	/**
	 * Renders the setting page.
	 */
	public function render() {
		$this->page->render();
	}

	/**
	 * Checks if needs loading?.
	 *
	 * @return bool
	 */
	private function needs_loading() {

		global $pagenow;

		// We need to load on options.php otherwise settings won't be reistered.
		if ( 'options.php' === $pagenow ) {
			return true;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] === $this->menu_slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Initializes the admin settings panel and fields.
	 */
	public function init() {

		if ( ! $this->needs_loading() ) {
			return;
		}

		$page = new Page(
			'na_aws_transcoder',
			__( 'AWS Transcoder', 'narrativeatlas-aws-transcoder' )
		);

		// General settings tab.
		$general = $page->add_panel(
			'general',
			_x( 'General', 'Admin settings panel title', 'narrativeatlas-aws-transcoder' )
		);

		$section_general = $general->add_section(
			'settings',
			_x( 'AWS Transcoder Settings', 'Admin settings section title', 'narrativeatlas-aws-transcoder' )
		);

		$fields = array(
			array(
				'name'    => 'enabled_transcoder',
				'label'   => _x( 'Transcoder', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'desc'    => __( 'Select the transcoding service used to transcode the videos.', 'narrativeatlas-aws-transcoder' ),
				'type'    => 'select',
				'options' => array(
					'media_convert' => _x( 'AWS MediaConvert', 'Admin settings', 'narrativeatlas-aws-transcoder' )
				),
				'default' => 'media_convert',
			),
		);

		$section_general->add_fields( $fields );

		if ( 'media_convert' === na_aws_transcoder_get_option( 'enabled_transcoder', 'media_convert' ) ) {
			( new Media_Convert_Admin_Settings() )->register_settings( $page );
		}

		$this->register_notification_settings( $page );

		$this->page = $page;

		// allow enabling options.
		$page->init();
	}

	/**
	 * Adds menu page.
	 */
	public function add_menu() {

		$page_hook = add_options_page(
			_x( 'AWS Transcoder', 'Admin settings page title', 'narrativeatlas-aws-transcoder' ),
			_x( 'AWS Transcoder', 'Admin settings menu label', 'narrativeatlas-aws-transcoder' ),
			'manage_options',
			$this->menu_slug,
			array( $this, 'render' )
		);

		add_action("load-$page_hook", array( $this, 'add_help_tab' ) );
		add_action("admin_footer-{$page_hook}", array( $this, 'print_js' ) );
	}

	/**
	 * Filters action links.
	 *
	 * @param array $links Links.
	 *
	 * @return array
	 */
	public function filter_links( $links ) {
		$url = esc_url(
			add_query_arg(
				'page',
				$this->menu_slug,
				get_admin_url() . 'options-general.php'
			)
		);

		$settings_link = sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'narrativeatlas-aws-transcoder' ) );

		array_unshift(
			$links,
			$settings_link
		);

		return $links;
	}

	/**
	 * Adds helps tab.
	 */
	public function add_help_tab() {
		$screen = get_current_screen();

		$screen->add_help_tab(
			array(
				'id'      => 'narrativeatlas_settings_overview',
				'title'   => _x( 'Overview', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
				'content' => sprintf( '<p>%s</p>', __( 'This page allows you to manage video transcoding settings.', 'narrativeatlas-aws-transcoder' ) ),
			)
		);

		if ( 'media_convert' === na_aws_transcoder_get_option( 'enabled_transcoder', 'media_convert' ) ) {
			( new Media_Convert_Admin_Settings() )->add_help_tab( $screen );
		}
	}

	/**
	 * Prints js.
	 */
	public function print_js() {
		?>
		<script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.pt-settings-field-description a').on('click', function(e) {
                    e.preventDefault();
                    $('#contextual-help-link').trigger('click');
                });
            });
		</script>
		<?php
	}

	/**
     * Registers notification settings.
     *
	 * @param Page $page Page object.
	 */
    public function register_notification_settings( Page $page ) {
	    // General settings tab.
	    $notification = $page->add_panel(
		    'notification',
		    _x( 'Notification', 'Admin settings panel title', 'narrativeatlas-aws-transcoder' )
	    );

	    $notification_general = $notification->add_section(
		    'notification_general',
		    _x( 'General Settings', 'Admin settings section title', 'narrativeatlas-aws-transcoder' )
	    );

	    $fields = array(
		    array(
			    'name'    => 'notification_email',
			    'label'   => _x( 'Notification Email', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'desc'    => __( 'Please provide the email which will be notified on success or failure of transcoding job.', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'text',
			    'default' => get_option( 'admin_email' ),
		    ),
	    );

	    $notification_general->add_fields( $fields );

	    $notification_submit = $notification->add_section(
		    'notification_submit',
		    _x( 'On Submit', 'Admin settings section title', 'narrativeatlas-aws-transcoder' ),
		    __( 'If enabled, The user will receive a notification when a new transcoding job is submitted.', 'narrativeatlas-aws-transcoder' )
	    );

	    $fields = array(
		    array(
			    'name'    => 'notify_on_submit',
			    'label'   => _x( 'Enable Notification', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'radio',
			    'options' => array(
				    1 => _x( 'Yes', 'Admin settings','narrativeatlas-aws-transcoder' ),
				    0 => _x( 'No', 'Admin settings','narrativeatlas-aws-transcoder' ),
			    ),
			    'default' => 1,
		    ),
		    array(
			    'name'    => 'notification_submit_email_subject',
			    'label'   => _x( 'Email Subject', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'desc'    => __( 'Email subject for on submit notifications.', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'text',
			    'default' => __( 'A new transcoding job is submitted', 'narrativeatlas-aws-transcoder' ),
		    ),
		    array(
			    'name'    => 'notification_submit_email_message',
			    'label'   => _x( 'Email Message', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'desc'    => __( 'Email message for on submit notifications.', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'textarea',
			    'default' => $this->get_email_message( 'submit' ),
		    ),
	    );

	    $notification_submit->add_fields( $fields );

	    $notification_success = $notification->add_section(
		    'notification_success',
		    _x( 'On Success', 'Admin settings section title', 'narrativeatlas-aws-transcoder' ),
		    __( 'If enabled, The user will receive a notification when a transcoding job is successfully completed.', 'narrativeatlas-aws-transcoder' )
	    );

	    $fields = array(
		    array(
			    'name'    => 'notify_on_success',
			    'label'   => _x( 'Enable Notification', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'radio',
			    'options' => array(
				    1 => _x( 'Yes', 'Admin settings','narrativeatlas-aws-transcoder' ),
				    0 => _x( 'No', 'Admin settings','narrativeatlas-aws-transcoder' ),
			    ),
			    'default' => 1,
		    ),
		    array(
			    'name'    => 'notification_success_email_subject',
			    'label'   => _x( 'Email Subject', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'desc'    => __( 'Email subject for successful transcoding job notifications.', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'text',
			    'default' => __( 'A transcoding job have completed successfully', 'narrativeatlas-aws-transcoder' ),
		    ),
		    array(
			    'name'    => 'notification_success_email_message',
			    'label'   => _x( 'Email Message', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'desc'    => __( 'Email message for successful transcoding job notifications.', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'textarea',
			    'default' => $this->get_email_message( 'success' ),
		    ),
	    );

	    $notification_success->add_fields( $fields );

	    $notification_error = $notification->add_section(
		    'notification_error',
		    _x( 'On Error', 'Admin settings section title', 'narrativeatlas-aws-transcoder' ),
		    __( 'If enabled, The user will receive a notification when a transcoding job is completed with error.', 'narrativeatlas-aws-transcoder' )
	    );

	    $fields = array(
		    array(
			    'name'    => 'notify_on_error',
			    'label'   => _x( 'Enable Notification', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'radio',
			    'options' => array(
				    1 => _x( 'Yes', 'Admin settings','narrativeatlas-aws-transcoder' ),
				    0 => _x( 'No', 'Admin settings','narrativeatlas-aws-transcoder' ),
			    ),
			    'default' => 1,
		    ),
		    array(
			    'name'    => 'notification_error_email_subject',
			    'label'   => _x( 'Email Subject', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'desc'    => __( 'Email subject for unsuccessful transcoding job notifications.', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'text',
			    'default' => __( 'A transcoding job has failed', 'narrativeatlas-aws-transcoder' ),
		    ),
		    array(
			    'name'    => 'notification_error_email_message',
			    'label'   => _x( 'Email Message', 'Admin settings', 'narrativeatlas-aws-transcoder' ),
			    'desc'    => __( 'Email message for unsuccessful transcoding job notifications.', 'narrativeatlas-aws-transcoder' ),
			    'type'    => 'textarea',
			    'default' => $this->get_email_message( 'error' ),
		    ),
	    );

	    $notification_error->add_fields( $fields );
    }

	/**
     * Returns message based on type.
     *
	 * @param string $type Message type.
	 *
	 * @return string
	 */
    private function get_email_message( string $type ): string {
	    $message = '';

	    if ( 'submit' === $type ) {
		    $message = <<<EOD
Hello,

This is to inform you that a new transcoding job has submitted.

Job Details:
- Link: {{link}}
EOD;
	    } elseif ( 'success' === $type ) {
		    $message = <<<EOD
Hello,

This is to inform you that a transcoding job has completed successfully.

Job Details:
- Link: {{link}}
EOD;
	    } elseif ( 'error' === $type ) {
		    $message = <<<EOD
Hello,

This is to inform you that a transcoding job has completed in failed state.

Job Details:
- Link: {{link}}
EOD;
	    }

        return $message;
    }
}
