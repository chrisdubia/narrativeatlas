<?php
// don't load directly.
if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}

/**
 *
 * @author      author
 * @package     package
 * @since       1.0.0
 */
class BuddyBossAddonUpdateActivation
{

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var
	 */
	public static $instance;

	/**
	 * The directory of the file.
	 *
	 * @access  public
	 * @var     string
	 */
	public static $dir;

	/**
	 * The Args
	 *
	 * @access  public
	 * @var     array
	 */
	public static $args;

	/**
	 * The options
	 *
	 * @access  public
	 */
	public $options;

	/**
	 * The license_status_option
	 *
	 * @access  public
	 */
	public $license_status_option;

	/**
	 * The reActivation
	 *
	 * @access  public
	 */
	public $reActivation;

	/**
	 * The option_string
	 *
	 * @access  public
	 */
	public $option_string = MECBUDDYBOSSOPTIONS;

	/**
	 * The license_status_string
	 *
	 * @access  public
	 */
	public $license_status_string = MECBUDDYBOSSTEXTDOMAIN . 'license_status';

	/**
	 * The reActivation_string
	 *
	 * @access  public
	 */
	public $reActivation_string = MECBUDDYBOSSTEXTDOMAIN . 'reActivationOption';

	/**
	 * The text_domain_string
	 *
	 * @access  public
	 */
	public $text_domain_string = MECBUDDYBOSSTEXTDOMAIN;

	/**
	 * The name_string
	 *
	 * @access  public
	 */
	public $name_string = MECBUDDYBOSSNAME;

	/**
	 * The slug_string
	 *
	 * @access  public
	 */
	public $slug_string = MECBUDDYBOSSSLUG;

	/**
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @since   1.0.0
	 * @return  object
	 */
	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct()
	{
		$this->options = get_option($this->option_string);
		$this->license_status_option = get_option($this->license_status_string);
		$this->reActivation = get_option($this->reActivation_string);

		$this->load_auto_update();
		self::setHooks($this);
	}

	public static function setHooks($This)
	{
		add_action('admin_init', [$This, 'add_license_options'], 999);
		add_action('admin_init', [$This, 'AutomaticreActivation'], 999999);
		add_action('addons_activation', [$This, 'add_addon_section']);
		add_action('wp_ajax_activate_Addons_Integration_BUDDYBOSS', array($This, 'activate_Addons_Integration_BUDDYBOSS'));
	}

	public function AutomaticreActivation()
	{
		if (!defined('MEC_API_ACTIVATION')) return;

		// Get Options
		$options = $this->options;
		$code = isset($options['purchase_code']) ? $options['purchase_code'] : '';
		$item_id = '';
		$url = get_home_url();

		$reActivationOption = $this->reActivation;

		if (is_null($code) || empty($code) || !isset($code) || $reActivationOption) return;

		if (!$reActivationOption) {
			$verify_url = MEC_API_ACTIVATION . '/activation/verify?category=mec_addons&license=' . $code . '&url=' . $url . '&item_id=' . $item_id;

			$JSON = wp_remote_retrieve_body(wp_remote_get($verify_url, array(
				'body' => null,
				'timeout' => '120',
				'redirection' => '10',
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
			)));

			if ($JSON != '') {
				$data = json_decode($JSON);
				if ($data and !is_null($data) and isset($data->item_link) and !is_null($data->item_link)) {
					update_option($this->license_status_string, 'active');
					$options['product_id'] = $data->item_id;
					update_option($this->option_string, $options);
				} else {
					update_option($this->license_status_string, 'faild');
				}
			}
			update_option($this->reActivation_string, '1');
			return;
		}
	}

	public function add_addon_section()
	{
		$addon_info = $this->options;
		$license_status_option = $this->license_status_option;
		$purchase_code = isset($addon_info['purchase_code']) ? $addon_info['purchase_code'] : '';

		$license_status = '';
		if (isset($addon_info['purchase_code']) && !empty($addon_info['purchase_code']) && $license_status_option == 'active') {
			$license_status = 'PurchaseSuccess';
		} elseif (isset($addon_info['purchase_code']) && !empty($addon_info['purchase_code']) && $license_status_option == 'faild') {
			$license_status = 'PurchaseError';
		}
		$factory = new \MEC_factory();
		echo '
            <style>.box-addon-activation-toggle-head {display: inline-block;}</style>
            <form id="' . $this->text_domain_string . '" class="addon-activation-form" action="#" method="post">
                <h3>' . esc_html__($this->name_string) . '</h3>
                <div class="LicenseField">
                    <input type="password" placeholder="Put your purchase code here" name="MECPurchaseCode" value="' . esc_html($purchase_code) . '">
                    <input type="submit">
                    <div class="MECPurchaseStatus ' . esc_html($license_status) . '"></div>
                </div>
                <div class="MECLicenseMessage"></div>
            </form>';
		$factory->params('footer', function () {
			echo '
            <script>
            if (jQuery("#' . $this->text_domain_string . '").length > 0)
            {
                jQuery("#' . $this->text_domain_string . ' input[type=submit]").on("click", function(e){
                    e.preventDefault();
                    jQuery(".wna-spinner-wrap").remove();
                    jQuery("#' . $this->text_domain_string . '").find(".MECLicenseMessage").text(" ");
                    jQuery("#' . $this->text_domain_string . '").find(".MECPurchaseStatus").removeClass("PurchaseError");
                    jQuery("#' . $this->text_domain_string . '").find(".MECPurchaseStatus").removeClass("PurchaseSuccess");
                    var PurchaseCode = jQuery("#' . $this->text_domain_string . ' input[type=password][name=MECPurchaseCode]").val();
                    var information = { PurchaseCodeJson: PurchaseCode };
                    jQuery.ajax({
                        url: mec_admin_localize.ajax_url,
                        type: "POST",
                        data: {
                            action: "activate_Addons_Integration_BUDDYBOSS",
                            nonce: mec_admin_localize.ajax_nonce,
                            content: information,
                        },
                        beforeSend: function () {
                            jQuery("#' . $this->text_domain_string . ' .LicenseField").append("<div class=\"wna-spinner-wrap\"><div class=\"wna-spinner\"><div class=\"double-bounce1\"></div><div class=\"double-bounce2\"></div></div></div>");
                        },
                        success: function (response) {
                            if (response == "success")
                            {
                                jQuery(".wna-spinner-wrap").remove();
                                jQuery("#' . $this->text_domain_string . '").find(".MECPurchaseStatus").addClass("PurchaseSuccess");
                            }
                            else
                            {
                                jQuery(".wna-spinner-wrap").remove();
                                jQuery("#' . $this->text_domain_string . '").find(".MECPurchaseStatus").addClass("PurchaseError");
                                jQuery("#' . $this->text_domain_string . '").find(".MECLicenseMessage").append(response);
                            }
                        },
                    });
                });
            }
            </script>';
		});
	}

	public function activate_Addons_Integration_BUDDYBOSS()
	{
		if (!wp_verify_nonce($_REQUEST['nonce'], 'mec_settings_nonce')) exit();

		$options = get_option($this->option_string);
		$options['purchase_code'] = $_REQUEST['content']['PurchaseCodeJson'];
		$options['product_name'] = $this->name_string;

		$verify = NULL;
		$verify = $this->plugin_activation_request($options);

		if ($verify and !is_null($verify) and isset($verify->item_link) and !is_null($verify->item_link)) {
			$LicenseStatus = 'success';
			update_option($this->license_status_string, 'active');
			$options['product_id'] = $verify->item_id;
		} else {
			$LicenseStatus = __('Activation failed. Please check your purchase code or license type.<br><b>Note: Your purchase code should match your licesne type.</b>', 'mec') . '<a style="text-decoration: underline; padding-left: 7px;" href="https://webnus.net/dox/modern-events-calendar/auto-update-issue/" target="_blank">'  . __('Troubleshooting', 'mec') . '</a>';
			update_option($this->license_status_string, 'faild');
		}

		update_option($this->option_string, $options);
		echo $LicenseStatus;
		wp_die();
	}

	public function plugin_activation_request($options)
	{
		if (!defined('MEC_API_ACTIVATION')) return;

		$code = isset($options['purchase_code']) ? $options['purchase_code'] : '';
		$product_name = isset($options['product_name']) ? $options['product_name'] : '';
		$item_id =  isset($options['product_id']) ? $options['product_id'] : '';
		$url = get_home_url();

		if (!isset($code) || empty($code)) return;

		$verify_url = MEC_API_ACTIVATION . '/activation/verify?category=mec_addons&license=' . $code . '&url=' . $url . '&item_id=' . $item_id;

		$JSON = wp_remote_retrieve_body(wp_remote_get($verify_url, array(
			'body' => null,
			'timeout' => '120',
			'redirection' => '10',
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
		)));

		if ($JSON != '') {
			$data = json_decode($JSON);
			return $data;
		} else return false;
	}

	public function add_license_options()
	{
		$addon_information = array(
			'product_name' => '',
			'purchase_code' => '',
		);

		$has_option = get_option($this->option_string, 'false');

		if ($has_option == 'false') {
			add_option($this->option_string, $addon_information);
		}
	}

	public function load_auto_update()
	{
		if (!defined('MEC_API_UPDATE')) return;
		$options = $this->options;
		$product_name = (isset($options['product_name']) && !empty($options['product_name'])) ? esc_html__($options['product_name']) : '';
		$product_id = (isset($options['product_id']) && !empty($options['product_id'])) ? esc_html__($options['product_id']) : '';
		$purchase_code = (isset($options['purchase_code']) && !empty($options['purchase_code'])) ? esc_html__($options['purchase_code']) : '';
		$url = urlencode(get_home_url());

		require_once MECBUDDYBOSSABSPATH . '/core/checkLicense/puc/plugin-update-checker.php';
		$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			add_query_arg(array('purchase_code' => $purchase_code, 'url' => $url, 'id' => $product_id, 'category' => 'mec_addons'), MEC_API_UPDATE . '/updates/?action=get_metadata&slug=' . $this->slug_string),
			MEC_BUDDYBOSS_FILE,
			$this->slug_string
		);

		add_filter('puc_request_info_result-mec', function ($info) {
			if (!$info) return;
			return $info;
		});
	}
}
new BuddyBossAddonUpdateActivation;
