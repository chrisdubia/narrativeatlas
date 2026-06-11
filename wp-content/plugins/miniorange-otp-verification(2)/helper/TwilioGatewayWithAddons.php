<?php

namespace OTP\Helper;

if(! defined( 'ABSPATH' )) exit;

use OTP\Addons\CustomMessage\MiniOrangeCustomMessage;
use OTP\Addons\PasswordResetwc\WooCommercePasswordReset;
use OTP\Addons\WpSMSNotification\WordPressSmsNotification;
use OTP\Addons\regwithphone\RegisterWithPhoneOnly;
use OTP\Addons\PasswordReset\UltimateMemberPasswordReset;
use OTP\Addons\UmSMSNotification\UltimateMemberSmsNotification;
use OTP\Addons\WcSMSNotification\WooCommerceSmsNotification;
use OTP\Addons\passwordresetwp\WordPressPasswordReset;
use OTP\Addons\CountryCode\SelectedCountryCode;
use OTP\Addons\APIVerification\APIAddon;
use OTP\Addons\ResendControl\MiniOrangeOTPControl;
use OTP\Addons\PasscodeOverCall\OTPOverCallAddon;
use OTP\Addons\MoBulkSMS\MoBulkSMSInit;
use OTP\Addons\CountryCodeDropdown\CountryCodeDropdownInit;
use OTP\Objects\BaseAddOnHandler;
use OTP\Objects\IGatewayFunctions;
use OTP\Traits\Instance;

/**
 * This class has Custom Gateway With AddOns Plan specific functions
 */
class TwilioGatewayWithAddons extends CustomGateway implements IGatewayFunctions
{
    use Instance;

    /** @var string ApplicationName used in API calls */
    protected $applicationName = 'wp_email_verification_intranet_twilio';

    /*
     | ---------------------------------------------------------------------------------------
     | FUNCTIONS RELATED TO ADDONS
     | ---------------------------------------------------------------------------------------
     */

    public function registerAddOns()
    {
        $customMessage = MOV_DIR.'addons/custommessage';
        if(file_exists($customMessage))
        {
            MiniOrangeCustomMessage::instance();
            
        }
        $umpasswordreset = MOV_DIR.'addons/passwordreset';
        if(file_exists($umpasswordreset ))
        {
            UltimateMemberPasswordReset::instance();
            
        }
        $umsmsNotification = MOV_DIR.'addons/umsmsnotification';
        if(file_exists($umsmsNotification))
        {
            UltimateMemberSmsNotification::instance();
            
        }
        $wcsmsnotification = MOV_DIR.'addons/wcsmsnotification';
        if(file_exists($wcsmsnotification))
        {
            WooCommerceSmsNotification::instance();
            
        }
        $wcpasswordreset = MOV_DIR.'addons/passwordresetwc';
        if(file_exists($wcpasswordreset))
        {
            WooCommercePasswordReset::instance();
            
        }
        $regwithphone = MOV_DIR.'addons/regwithphone';
        if(file_exists($regwithphone))
        {
            RegisterWithPhoneOnly::instance();
            
        }
        $wpsmsnotification = MOV_DIR.'addons/wpsmsnotification';
        if(file_exists($wpsmsnotification))
        {
            WordPressSmsNotification::instance();
            
        }
        $wpsmsnotification = MOV_DIR.'addons/wpsmsnotification';
        if(file_exists($wpsmsnotification))
        {
            WordPressSmsNotification::instance();
            
        }
        if(file_exists(MOV_DIR.'addons/apiverification'))
            APIAddon::instance(); 
        if(file_exists(MOV_DIR.'addons/resendcontrol'))
            MiniOrangeOTPControl::instance();
        if(file_exists(MOV_DIR.'addons/countrycode'))
            SelectedCountryCode::instance();   
        if(file_exists(MOV_DIR.'addons/passcodeovercall'))
            OTPOverCallAddon::instance();  
        if(file_exists(MOV_DIR.'addons/mobulksms'))
            MoBulkSMSInit::instance();    
        if(file_exists(MOV_DIR.'addons/countrycodedropdown'))
            CountryCodeDropdownInit::instance(); 
    }

    public function showAddOnList()
    {
        /** @var AddOnList $addonList */
        $addonList = AddOnList::instance();
        $addonList = $addonList->getList();

        /** @var  BaseAddOnHandler  $addon  */
        foreach ($addonList as $addon) {
            echo    '<tr>
                    <td class="addon-table-list-status">
                        '.$addon->getAddOnName().'
                    </td>
                    <td class="addon-table-list-name">
                        <i>
                            '.$addon->getAddOnDesc().'
                        </i>
                    </td>
                    <td class="addon-table-list-actions">
                        <a  class="button-primary button tips" 
                            href="'.$addon->getSettingsUrl().'">
                            '.mo_("Settings").'
                        </a>
                    </td>';
                    /* removed temporarily
                    <td class="addon-table-list-docslink">
                     <a class="dashicons mo-addon-links dashicons-book-alt mo_book_icon" 
                        href="'.$addon->getAddOnDocs().'"
                        title="Instruction Guide"
                        id="guideLink" 
                        target="_blank">
                    <span class="mo-link-text">'.mo_("Setup Guide").'</span>
                    </a>
                    </td>
                    <td class="addon-table-list-videolink">
                    <a class="dashicons mo-addon-links dashicons-video-alt3 mo_video_icon" 
                       href="'.$addon->getAddOnVideo().'"
                       title="Video Guide"
                       id="videoLink" 
                       target="_blank">
                   <span class="mo-link-text">'.mo_("Video Guide").'</span>
                   </a>
                   </td>*/
            echo '
                </tr>';
        }
    }

    /*
     | ---------------------------------------------------------------------------------------
     | FUNCTION RELATED TO VISUAL TOUR
     | ---------------------------------------------------------------------------------------
     */

    public function getConfigPagePointers()
    {
        // TODO: Implement getConfigPagePointers() method.
        return [];
    }
}