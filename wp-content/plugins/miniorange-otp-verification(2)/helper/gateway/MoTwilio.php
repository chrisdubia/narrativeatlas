<?php

namespace OTP\Helper\Gateway;
use Twilio\Exceptions\TwilioException;

if(! defined( 'ABSPATH' )) exit;

use OTP\Objects\IGatewayType;
use OTP\Helper\MoMessages;
use OTP\Traits\Instance;
use OTP\Helper\GatewayType;
use OTP\Helper\MocURLOTP;

class MoTwilio implements IGatewayType
{
    use Instance;
    /** @var String $_gatewayName - Name of the gateway to be displayed */
    public $_gatewayName;

    public $_errorMessage; 

    /** @var String $_SDKPath - SDK path */
    private $_SDKPath;

    public function __construct()
    {
        $this->_gatewayName =   "Twilio";
        $this->SDKPath = MOV_DIR.'sms-gateways/twilio-php-master/src';
        if(is_dir($this->SDKPath))
            require_once $this->SDKPath.'/Twilio/autoload.php';
        add_filter("mo_get_otp_sent_failed_message", array($this ,"_get_otp_sent_failed_message" ),1,1);
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
        try{
        $response;
        $sid      = get_mo_option('twilio_sid');
        $token    = get_mo_option('twilio_token');
        $client   = new \Twilio\Rest\Client($sid, $token);

        
          $sms    = $client->messages->create(
            '+'.apply_filters('mo_filter_phone_before_api_call',$phone),
            array( 'from' => '+'.get_mo_option('twilio_from_phone'), 'body' => $message )
          );
          // var_dump($sms);exit;
          $response   = $sms->sid ? true :false;
      }
      catch (TwilioException $e) {
        // var_dump($e);exit;
        $errorCode = $e->getCode();
        
        switch ($errorCode) {
          case '21211':
            $this->_errorMessage = $phone.mo_(" is not a valid phone number. Please enter a valid phone number");
            break;
          case '21408':
            $this->_errorMessage = mo_("There was an error in sending the OTP to the given country. Please Try Again or contact site Admin.");
            break;
          case '21608':
            $this->_errorMessage = mo_("There was an error in sending the OTP to the given country. Please Try Again or contact site Admin.");
            break;
          default:
            $this->_errorMessage = get_mo_option("error_phone_message","mo_otp_") ? get_mo_option('error_phone_message',"mo_otp_") : MoMessages::showMessage(MoMessages::ERROR_OTP_PHONE);
            break;
        }
        return false;
      }
      return $response;
    }


    public function _get_otp_sent_failed_message($failedMSG)
    {
        return $this->_errorMessage;
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
        $response 	= apply_filters("mo_custom_gateway_response",$response,$message,$phone); 
        return $response;
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
        $sms_gateway_secret = get_mo_option('twilio_sid');
        $sms_gateway_key    = get_mo_option('twilio_token');
        $sms_callerID 	    = get_mo_option('twilio_from_phone');

        if(!is_dir($this->SDKPath)){
        
            return '<div class="mo_otp_note" style="margin-bottom:10px;">
                        <ol>
                            <li>Download the SDK from the link below.</li>
                            <li>Extract and put twilio-master-php folder in the path : <code>(otp plugin folder)/sms-gateways</code></li>
                        </ol>
                    </div>
                    <a href="https://github.com/twilio/twilio-php/archive/master.zip" target="">
                        <div class="mo-gateway-sdk-notice">
                            <div class="mo-sdk-download-link">
                                <span class="dashicons dashicons-download   "></span>
                            </div>
                            Click here to Download The SDK
                        </div>
                    </a>';
        }
        else    
        return '<div class="mo-gateway-text-pair mo-flex-pair">
                    <label for="custom_sms_gateway_secret">'.mo_("Twilio ACCOUNT SID : ").'</label>
                    <input type="text" '.$disabled.' 
                        id="custom_sms_gateway_secret" 
                        class="mo_registration_table_textbox" 
                        name="custom_sms_gateway_secret" 
                        placeholder="Enter your Twilio ACCOUNT SID" 
                        value = "'.$sms_gateway_secret.'" required/>
                </div>
                <div class="mo-gateway-text-pair mo-flex-pair">
                    <label for="custom_sms_gateway_key">'.mo_("Twilio AUTH TOKEN : ").'</label>
                    <input type="text" '.$disabled.' 
                        id="custom_sms_gateway_key" 
                        class="mo_registration_table_textbox" 
                        name="custom_sms_gateway_key" 
                        placeholder="Enter your Twilio Auth Token" 
                        value = "'.$sms_gateway_key.'" required/>
                </div>
                <div class="mo-gateway-text-pair mo-flex-pair">
                    <label for="custom_sms_gateway_callerID">'.mo_("Twilio PHONE NUMBER : ").'</label>
                    <input type="text" '.$disabled.' 
                        id="custom_sms_gateway_callerID" 
                        class="mo_registration_table_textbox" 
                        name="custom_sms_gateway_callerID" 
                        placeholder="Enter your Twilio Phone Number" 
                        value = "'.$sms_callerID.'" required/>
                </div>';
                // <div class="mo_otp_note">
                //     '.$sms_gateway_note.'
                //     <br><i><span style="font-size:11px;color:red;word-break: break-all;">'.$sms_gateway_ex.'</span></i>
                //     <br><br/><i><b>'.$smsg_help_header.'</b></i>
                //     <br>'.$sms_gateway_help.'
                // </div>';
    }

    /**
     * Saves the settings for the gateway.
     * 
     * @param Mixed $posted - Posted data
     */
    public function saveGatewayDetails($posted)
    {
        update_mo_option('twilio_sid'           ,$posted['custom_sms_gateway_secret']   );
		update_mo_option('twilio_token' 	    ,$posted['custom_sms_gateway_key']      );
		update_mo_option('twilio_from_phone' 	,$posted['custom_sms_gateway_callerID'] );    
    }
}