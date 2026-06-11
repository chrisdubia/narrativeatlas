<?php
/**
 * AddOn Name: miniOrange Bulk SMS Addon
 * Plugin URI: http://miniorange.com
 * Description: Allows admin to send SMSs in bulk.
 * Version: 1.0.0
 * Author: miniOrange
 * Author URI: http://miniorange.com
 * Text Domain: miniorange-otp-verification
 * License: GPL2
 */


namespace OTP\Addons\MoBulkSMS;

use OTP\Addons\MoBulkSMS\Handler\MoBulkSMSAddonHandler;
use OTP\Objects\AddOnInterface;
use OTP\Objects\BaseAddOn;
use OTP\Helper\AddOnList;
use OTP\Traits\Instance;


if(! defined( 'ABSPATH' )) exit;
include '_autoload.php';


final class MoBulkSMSInit extends BaseAddOn implements AddOnInterface
{
    use Instance;

    /** Initialize all handlers associated with the addon */
    function initializeHandlers()
    {
        /** @var AddOnList $list */

        $list = AddOnList::instance();
        /** @var OtpControlAddonHandler $handler */
        $handler = MoBulkSMSAddonHandler::instance();
        $list->add($handler->getAddOnKey(),$handler);
    }

    /** Initialize all helper associated with the addon */
    function initializeHelpers()
    {
        //CustomMessagesShortcode::instance();
    }

    /**
     * This function hooks into the mo_otp_verification_add_on_controller
     * hook to show the custom message add-on settings page.
     */
    function show_addon_settings_page()
    {
        include MOV_BULK_SMS_DIR . 'controllers/main-controller.php';
    }
}