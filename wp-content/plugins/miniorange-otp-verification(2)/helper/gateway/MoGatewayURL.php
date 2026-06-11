<?php

namespace OTP\Helper\Gateway;

if(! defined( 'ABSPATH' )) exit;

use OTP\Objects\IGatewayType;
use OTP\Traits\Instance;
use OTP\Helper\GatewayType;
use OTP\Helper\MocURLOTP;
use OTP\Helper\MoMessages;

class MoGatewayURL implements IGatewayType
{
    use Instance;

    /** @var String $gateway_url - url of the gateway*/
    private $gateway_url;

    /** @var String $_gatewayName - Name of the gateway to be displayed */
    public $_gatewayName; 

    public function __construct()
    {
        $this->_gatewayName =   "Gateway URL";
        
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
        $url 		= get_mo_option('custom_sms_gateway');
        $method     = get_mo_option('custome_gateway_method');
        $message 	= str_replace(" ","+",$message);
        $url 		= str_replace("##message##"	,$message,$url);
        $url 		= str_replace("##phone##"	,apply_filters('mo_filter_phone_before_api_call',$phone),$url);
        $url        = apply_filters('customize_otp_url_before_api_call',$url,$message,
                                apply_filters('mo_filter_phone_before_api_call',$phone));
        $response 	= MocURLOTP::callAPI($url,null,null,$method);
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
        $checked_method = get_mo_option("custome_gateway_method");
        $checked_method_post = $checked_method == "POST" ?"checked":"";
        $checked_method_get = $checked_method == "GET" ?"checked":"";
        
        return '<div id="mo_method">
            <b>'.mo_("SMS Gateway URL Method").':</b>&nbsp
                                    <input type="radio" '.$disabled.' class="app_enable" id="mo_post" checked name="mo_customer_validation_custom_gateway_method" '.$checked_method_post.' value="POST">
                                    <label for="mo_post">POST</label>&nbsp
                                    <input type="radio" '.$disabled.' class="app_enable" id="mo_get"  name="mo_customer_validation_custom_gateway_method" '.$checked_method_get.' value="GET">
                                    <label for="mo_get">GET</label><br>
                                    </div><br>

        <b>'.mo_("SMS Gateway URL").':</b>
                <div >
                    <input type="url" '.$disabled.' 
                        id="custom_sms_gateway" 
                        class="mo_registration_table_textbox" 
                        style="border:1px solid #ddd" 
                        name="mo_customer_validation_custom_sms_gateway" 
                        required 
                        placeholder="'.mo_("Enter Gateway URL").'" value = "'.$gateway_url.'" />
                    <br>
                </div>
                <br>
                <div class="mo_otp_note">
                    <i><span style="color:red;">'.MoMessages::showMessage(MoMessages::GATEWAY_PARAM_NOTE).'</span></i>
                </div>';
                
    }

    /**
     * Saves the settings for the gateway.
     * 
     * @param Mixed $posted - Posted data
     */
    public function saveGatewayDetails($posted)
    {
        update_mo_option('custom_sms_gateway'    ,$posted['mo_customer_validation_custom_sms_gateway']    );
        update_mo_option('custome_gateway_method',!empty($posted['mo_customer_validation_custom_gateway_method']) ?$posted['mo_customer_validation_custom_gateway_method'] : "POST" );
    }
}