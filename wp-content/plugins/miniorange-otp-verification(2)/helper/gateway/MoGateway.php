<?php

namespace OTP\Helper\Gateway;

if(! defined( 'ABSPATH' )) exit;

use OTP\Objects\IGatewayType;
use OTP\Traits\Instance;
use OTP\Helper\GatewayType;
use OTP\Helper\MocURLOTP;
use OTP\Helper\MoMessages;
use OTP\Helper\MoConstants;
use OTP\Objects\NotificationSettings;

class MoGateway implements IGatewayType
{
    use Instance;

    /** @var String $gateway_url - url of the gateway*/
    private $gateway_url;

    /** @var String $_gatewayName - Name of the gateway to be displayed */
    public $_gatewayName; 

    public function __construct()
    {
        $this->_gatewayName =   "miniOrange Gateway";
        
    }

    /**
     * Prepares the URL for api call. Calls MocURLOTP::callAPI() function make
     * GET request. 
     * 
     * @param String $message  - message
     * @param String $phone    - phone number
     * @return response
     */
    public function sendOTPRequest($message,$phone)
    {
        $message 	= str_replace(" ","+",$message);
        $url         = MoConstants::HOSTNAME . '/moas/api/notify/send';
        $customerKey = get_mo_option('admin_customer_key');
        $apiKey      = get_mo_option('admin_api_key');
              $fields      = [
                    'customerKey' => $customerKey,
                    'sendEmail' => false,
                    'sendSMS' => true,
                    'sms' => [
                        'customerKey' => $customerKey,
                        'phoneNumber' => $phone,
                        'message' => $message
                    ]
                ];

        $json        = json_encode ( $fields );
        $authHeader  = MocURLOTP::createAuthHeader($customerKey,$apiKey);
        $response    = MocURLOTP::callAPI($url, $json, $authHeader);
        return $response;

    }

    public function sendEmailOTPRequest($message,$email,$fromEmail,$subject,$fromName){
        $url         = MoConstants::HOSTNAME . '/moas/api/notify/send';
        $customerKey = get_mo_option('admin_customer_key');
        $apiKey      = get_mo_option('admin_api_key');
              $fields      = [
                    'customerKey' => $customerKey,
                    'sendEmail' => true,
                    'sendSMS' => false,
                    'email' => [
                        'customerKey' => $customerKey,
                        'fromEmail' => $fromEmail,
                        'bccEmail' => $bccEmail,
                        'fromName' => $fromName,
                        'toEmail' => $email,
                        'toName' => $email,
                        'subject' => $subject,
                        'content' => $message
                    ],
                ];

        $json        = json_encode ( $fields );
        $authHeader  = MocURLOTP::createAuthHeader($customerKey,$apiKey);
        $response    = MocURLOTP::callAPI($url, $json, $authHeader);
        return $response;
    }

    /**
     * Handles the gateway response.
     * Added a filter here so it can be extended to log gateway response.
     * 
     * @param mixed $response -  gateway response
     * @return status
     */
    public function handleGatewayResponse($response,$message,$phone)
    { 
     
        return apply_filters("mo_custom_gateway_response",$response,$message,$phone);
    }

    /**
     * Retrives the gateway configuration html view
     * i.e the fields required by that gateway
     * 
     * @param String $disabled  - conatains disabled or empty
     * @return String HTML view 
     */
    public function getGatewayConfigView($disabled,$gateway_url)
    {   
        return '<div class="mo_otp_note">
                    <i><span style="color:grey;">For more info, please contact <a style="cursor:pointer;" onClick="otpSupportOnClick();"><u> otpsupport@xecurify.com</u></a></span></i>
                </div>';
    }

    /**
     * Saves the settings for the gateway.
     * 
     * @param Mixed $posted - Posted data
     */
    public function saveGatewayDetails($posted)
    {

    }
}