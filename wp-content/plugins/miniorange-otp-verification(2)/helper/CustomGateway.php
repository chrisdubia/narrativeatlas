<?php

namespace OTP\Helper;

if(! defined( 'ABSPATH' )) exit;

use OTP\Handler\MoOTPActionHandlerHandler;
use OTP\Objects\NotificationSettings;
use OTP\Helper\GatewayType;
use OTP\SplClassLoader;
use OTP\Helper\MoSMSBackupGateway;
use OTP\Objects\Tabs;


class CustomGateway
{   
    public function __construct(){
  
        $this->_loadHooks();
    }

    /** @var string ApplicationName used in API calls */
    protected $applicationName;

    /*
     | ---------------------------------------------------------------------------------------
     | FUNCTIONS RELATED TO LICENSING AND SYNC
     | ---------------------------------------------------------------------------------------
     */

    public function _loadHooks(){

        add_action( 'wp_ajax_miniorange_get_test_response',array($this, 'get_gateway_response'));
    }

    public function hourlySync()
    {
        if(!$this->ch_xdigit()) {
            $this->daoptions();
        }
    }

    public function flush_cache()
    {
        if(MO_TEST_MODE) {
            delete_mo_option("site_email_ckl");
            delete_mo_option("email_verification_lk");
        }else if($this->mclv()) {
            $this->mius();
        }
    }

    public function _vlk($post)
    {
        if( MoUtility::isBlank( $post['email_lk'] ) )
        {
            do_action('mo_registration_show_message', MoMessages::showMessage(MoMessages::REQUIRED_FIELDS),MoConstants::ERROR);
            return;
        }
        $code = trim($_POST['email_lk']);
        $result = json_decode($this->ccl(), true);
        switch ($result['status'])
        {
            case 'SUCCESS':
                $this->_vlk_success($code);	break;
            default:
                $this->_vlk_fail();			break;
        }
    }

    /**
     * Checks if user has upgraded to the custom gateway plugin
     */
    public function mclv()
    {
        $key 		= get_mo_option('customer_token');
        $isVerified = isset($key) && !empty($key) ? AEncryption::decrypt_data(get_mo_option('site_email_ckl'),$key) : "false";
        $licenseKey = get_mo_option('email_verification_lk');
        $email 		= get_mo_option('admin_email');
        $customerKey= get_mo_option('admin_customer_key');
        return $isVerified=="true" && $licenseKey && $email && $customerKey && is_numeric( trim( $customerKey ));
    }

    /**
     * Checks if user has saved value through any custom gateway configuration
     */
    public function isGatewayConfig()
    {
        if(get_mo_option('custome_gateway_type')) return TRUE;    
             return FALSE;
    }



    
    /**
     * @return bool
     */
    public function isMG()
    {
        return FALSE;
    }

    /**
     * Returns the application Name for the gateway
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /*
     | ---------------------------------------------------------------------------------------
     | PRIVATE FUNCTIONS RELATED TO LICENSING AND SYNC
     | ---------------------------------------------------------------------------------------
     */

    /**
     * This function is called to check if the license key has been tampered
     * with in any way. If a valid license was found return true otherwise
     * return false.
     */
    private function ch_xdigit() {
        if (!get_mo_option("site_email_ckl")) return FALSE;
        $key = get_mo_option('customer_token');
        return AEncryption::decrypt_data(get_mo_option('site_email_ckl'), $key) == "true";
    }

    /**
     * This is called to delete all plugin settings in case the license
     * key was tampered with or there is no valid license key for the
     * site. Deletes all the enable settings for each of the forms.
     *
     * @todo: Figure out a way to optimize this code.
     */
    private function daoptions()
    {
        delete_mo_option('wp_default_enable');
        delete_mo_option('wc_default_enable');
        delete_mo_option('pb_default_enable');
        delete_mo_option('um_default_enable');
        delete_mo_option('simplr_default_enable');
        delete_mo_option('event_default_enable');
        delete_mo_option('bbp_default_enable');
        delete_mo_option('crf_default_enable');
        delete_mo_option('uultra_default_enable');
        delete_mo_option('wc_checkout_enable');
        delete_mo_option('upme_default_enable');
        delete_mo_option('pie_default_enable');
        delete_mo_option('cf7_contact_enable');
        delete_mo_option('classify_enable');
        delete_mo_option('gf_contact_enable');
        delete_mo_option('nja_enable');
        delete_mo_option('ninja_form_enable');
        delete_mo_option('tml_enable');
        delete_mo_option('ultipro_enable');
        delete_mo_option('userpro_default_enable');
        delete_mo_option('wp_login_enable');
        delete_mo_option('formcraft_premium_enable' );
        delete_mo_option('wp_member_reg_enable');
        delete_mo_option('gf_otp_enabled');
        delete_mo_option('wc_social_login_enable');
        delete_mo_option('formcraft_enable');
        delete_mo_option('mo_customer_validation_admin_email');
        delete_mo_option('wpcomment_enable' );
        delete_mo_option('docdirect_enable');
        delete_mo_option('wpform_enable');
        delete_mo_option('crf_otp_enabled');
        delete_mo_option('caldera_enable');
        delete_mo_option('formmaker_enable');
        delete_mo_option('um_profile_enable');
        delete_mo_option('visual_form_enable');
        delete_mo_option('frm_form_enable');
        delete_mo_option('wc_billing_enable');
        delete_mo_option('plugin_activation_date');
    }

    /**
     * Function handles what needs to be done after license key is verified successfully.
     * Certain values are stored in the Wordpress Database so that we can check if the
     * license key is a valid license key in future.
     *
     * @param $code - the license key entered by the user
     */
    private function _vlk_success($code)
    {
        $content = json_decode($this->vml($code),true);
        if(strcasecmp($content['status'], 'SUCCESS') == 0)
        {
            $key = get_mo_option('customer_token');
            update_mo_option('email_verification_lk'	, AEncryption::encrypt_data($code,  $key) );
            update_mo_option('site_email_ckl'		 	, AEncryption::encrypt_data("true", $key) );
            do_action('mo_registration_show_message'	, MoMessages::showMessage(MoMessages::VERIFIED_LK),'SUCCESS');
        }
        else if(strcasecmp($content['status'], 'FAILED') == 0)
        {
            if(strcasecmp($content['message'], 'Code has Expired') == 0)
                do_action('mo_registration_show_message', MoMessages::showMessage(MoMessages::LK_IN_USE), 'ERROR');
            else
                do_action('mo_registration_show_message', MoMessages::showMessage(MoMessages::INVALID_LK), 'ERROR');
        }
        else
            do_action('mo_registration_show_message', MoMessages::showMessage(MoMessages::UNKNOWN_ERROR),'ERROR');
    }


    /**
     * This functions handles what needs to be done after license key is not verified successfully.
     */
    private function _vlk_fail()
    {
        $key = get_mo_option('customer_token');
        update_mo_option('site_email_ckl', AEncryption::encrypt_data("false", $key));
        do_action('mo_registration_show_message', MoMessages::showMessage(MoMessages::NEED_UPGRADE_MSG),'ERROR');
    }

    private function vml($code)
    {
        $url = MoConstants::HOSTNAME . '/moas/api/backupcode/verify';
        $customerKey = get_mo_option ( 'admin_customer_key' );
        $apiKey 	 = get_mo_option ( 'admin_api_key' );

        $fields = array (
            'code' => $code ,
            'customerKey' => $customerKey,
            'additionalFields' => array(
                'field1' => site_url()
            )
        );

        $json 		 = json_encode($fields);
        $authHeader  = MocURLOTP::createAuthHeader($customerKey,$apiKey);
        $response    = MocURLOTP::callAPI($url, $json, $authHeader);
        return $response;

    }

    private function ccl()
    {
        $url = MoConstants::HOSTNAME . '/moas/rest/customer/license';
        $customerKey = get_mo_option ( 'admin_customer_key' );
        $apiKey 	 = get_mo_option ( 'admin_api_key' );

        //*check for otp over sms/email
        $fields = array(
            'customerId' => $customerKey,
            'applicationName' => $this->applicationName,
        );

        $json 		 = json_encode($fields);
        $authHeader  = MocURLOTP::createAuthHeader($customerKey,$apiKey);
        $response    = MocURLOTP::callAPI($url, $json, $authHeader);
        return $response;
    }

    private function mius()
    {
        $url = MoConstants::HOSTNAME . '/moas/api/backupcode/updatestatus';
        $customerKey = get_mo_option ( 'admin_customer_key' );
        $apiKey 	 = get_mo_option ( 'admin_api_key' );


        $key = get_mo_option('customer_token');
        $code = AEncryption::decrypt_data(get_mo_option('email_verification_lk'),$key);
        $fields = array (
            'code' => $code,
            'customerKey' => $customerKey
        );
        $json 		 = json_encode($fields);
        $authHeader  = MocURLOTP::createAuthHeader($customerKey,$apiKey);
        $response    = MocURLOTP::callAPI($url, $json, $authHeader);
        return $response;
    }

    /*
     | ---------------------------------------------------------------------------------------
     | FUNCTIONS RELATED TO CUSTOM SMS AND EMAIL TEMPLATES
     | ---------------------------------------------------------------------------------------
     */

    public function custom_wp_mail_from_name($original_email_from)
    {
        return get_mo_option('custom_email_from_name')
            ? get_mo_option('custom_email_from_name') : $original_email_from;
    }

    /**
     * Save SMS Template admin settings.
     *
     * @param array $posted the template data set by the admin
     */
    function _mo_configure_sms_template($posted)
    {
        // if sms template being saved
        if(isset($posted['mo_customer_validation_custom_sms_msg'])){
            // trim the custom message set by the user before saving in the database
            $custom_msg = trim($posted['mo_customer_validation_custom_sms_msg']);
            // replace all the new line characters with %0a which is the unicode format for new line character
            $custom_msg = str_replace(PHP_EOL, '%0a', $custom_msg);

            update_mo_option('custom_sms_msg'       ,$custom_msg);
        }

        // if gateway settings being saved
        if(isset($posted['mo_customer_validation_custom_gateway_type'])){
            update_mo_option('custome_gateway_type' ,$posted['mo_customer_validation_custom_gateway_type'] );

            //call the gateway class whose data being stored
            $gatewayType = GatewayType::instance();
            $gatewayType->saveGatewayDetails($posted);
        }

    }

    /**
     * Save Email Template admin settings.
     *
     * @param array $posted the template data set by the admin
     */
    function _mo_configure_email_template($posted)
    {
        update_mo_option('custom_email_msg', wpautop($posted['mo_customer_validation_custom_email_msg']));
        update_mo_option('custom_email_subject', sanitize_text_field($posted['mo_customer_validation_custom_email_subject'])) ;
        update_mo_option('custom_email_from_id', sanitize_text_field($posted['mo_customer_validation_custom_email_from_id'])) ;
        update_mo_option('custom_email_from_name'	, sanitize_text_field($posted['mo_customer_validation_custom_email_from_name']));
    }

    public function showConfigurationPage($disabled)
    {
        //data
        $sms_template 	    = get_mo_option('custom_sms_msg')
            ? get_mo_option('custom_sms_msg')
            : MoMessages::showMessage(MoMessages::DEFAULT_SMS_TEMPLATE);
        $sms_template       = mo_($sms_template);

        $email_subject      = get_mo_option('custom_email_subject')
            ? get_mo_option('custom_email_subject')
            : MoMessages::showMessage(MoMessages::EMAIL_SUBJECT);

        $email_from_id      = get_mo_option('custom_email_from_id')
            ? get_mo_option('custom_email_from_id')
            : get_mo_option('admin_email');

        $email_from_name    = get_mo_option('custom_email_from_name')
            ? get_mo_option('custom_email_from_name')
            : get_bloginfo('name');

        $content 		    = get_mo_option('custom_email_msg')
            ? stripslashes(get_mo_option('custom_email_msg'))
            : MoMessages::showMessage(MoMessages::DEFAULT_EMAIL_TEMPLATE);

        $editor_id 		    = 'customemaileditor';

        $template_settings  = [
            'media_buttons'=>false,
            'textarea_name'=>'mo_customer_validation_custom_email_msg',
            'editor_height' => '170px',
            'wpautop'=>false
        ];

        /** @var MoOTPActionHandlerHandler $adminHandler */
        $adminHandler               = MoOTPActionHandlerHandler::instance();

        $nonce                      = $adminHandler->getNonceValue();
        $nonce_field                = wp_nonce_field($nonce);

        //Strings
        $sms_title                  = mo_("SMS TEMPLATE CONFIGURATION");
        $sms_gateway_title          = mo_("SMS GATEWAY CONFIGURATION");
        $sms_msg                    = mo_("SMS Template");
        $sms_msg_placeholder        = mo_("Enter OTP SMS Message");
        $sms_msg_note               = mo_("You need to write ##otp## where you wish to place generated otp in this template.");
        $sms_gateway_note           = mo_("You will need to place your SMS gateway URL in the field above in order to be
                                            able to send OTPs to the user's phone.")
                                            ."<br/>". mo_("You will be able to get this URL from your SMS gateway provider.");
        $sms_gateway_help           = mo_("If you are having trouble in finding your gateway URL then you drop us an
                                            email at <a style='cursor:pointer;'' onClick='otpSupportOnClick();'>otpsupport@xecurify.com</a>. We will help you with the setup.");
        $test_configuration_title   = mo_("Test SMS Gateway Configurations");
        $test_configuration_submit_button_txt =mo_("Test Configuration");
        $test_configuration_response= mo_("Gateway Response");
        $sms_gateway_ex             = "Example:- http://alerts.sinfini.com/api/web2sms.phpusername=XYZ&password=password&to=##phone##&sender=senderid&message=##message##";
        $smsg_help_header           = mo_("CANNOT FIND THE GATEWAY URL?");
        $submit_button_txt          = mo_("Save SMS Configurations");
        $gateway_submit_button_txt  = mo_("Save Gateway Configurations");
        $email_title                = mo_("EMAIL CONFIGURATION");
        $email_note                 = mo_("You need to configure your php.ini file with SMTP settings to be able to send emails.");
        $mail_submit_btn_txt        = mo_("Save Email Configurations");
        $mail_sub_pholder           = mo_("Enter your OTP Email Subject");
        $mail_frm_pholder           = mo_("Enter Name");
        $mail_frm_addr              = mo_("Enter email address");

        $from_id                = mo_("From ID");
        $from_name              = mo_("From Name");
        $subject                = mo_("Subject");
        $body                   = mo_("Body");

        /** @var GatewayType $gatewayType - current active gateway object */
        $gatewayType            = GatewayType::instance();
        $gateway_url  =   get_mo_option('custom_sms_gateway')
                                        ? get_mo_option('custom_sms_gateway')
                                        : '';
        $gateway_config_view    = $gatewayType->getGatewayConfigView($disabled,$gateway_url);
        $gateway_list           = $this->get_gateway_list();
        $active_gateway         = get_mo_option("custome_gateway_type")
                                        ? get_mo_option("custome_gateway_type")
                                        : "MoGatewayURL";
        include MOV_DIR . 'views/cconfiguration.php';
    }

    /**
     * Reads through the gateway directory and forms a list of options for gateway
     * dorpdown.
     */
    public function get_gateway_list()
    {   $list = '';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(MOV_DIR . 'helper/gateway',\RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        /** @var SplFileInfo $it */
        foreach($iterator as $it){
            $filename = $it->getFilename();
            $className = "OTP\\Helper\\Gateway\\" . str_replace('.php','',$filename);

            /** @var Gateway $handlerList */
            $gateway = $className::instance();
            $list .= $this->addOption($gateway->_gatewayName,str_replace('.php','',$filename));
        }
        return $list;
    }


    /**
       Function to Get getway response when user tests SMS Gateway Configurations
    **/
    public function get_gateway_response(){

        $test_configuration_number= isset($_POST["test_config_number"]) ? $_POST["test_config_number"] : '';
        $test_gateway_response=  $this->mo_send_otp_token('SMS','',$test_configuration_number);
        print_r($test_gateway_response);    
            
        die();
    }

    /** Return an option element */
    private function addOption($name,$class)
    {
        return '<option value="'.$class.'">'.$name.'</option>';
    }

    /*
     | ---------------------------------------------------------------------------------------
     | FUNCTIONS RELATED TO SENDING OTP
     | ---------------------------------------------------------------------------------------
     */

    /**
     * Calls the server to send OTP to the user's phone or email
     *
     * @param string $authType  OTP Type - EMAIL or SMS
     * @param string $email     Email Address of the user
     * @param string $phone     Phone Number of the user
     * @return array
     */
    public function mo_send_otp_token($authType, $email, $phone)
    {
        if(MO_TEST_MODE) {
            return ['status'=>'SUCCESS','txId'=> MoUtility::rand()];
        }else{
            $content = $this->send_otp_token($authType,$email,$phone);
            return json_decode($content,TRUE);
        }
    }

    /**
     * Use the custom email or sms gateway to send message to the user's email or phone
     *
     * @param NotificationSettings $settings
     * @return string
     */
    public function mo_send_notif(NotificationSettings $settings)
    {
        $response = $settings->sendSMS
            ? self::send_sms_token($settings->message,$settings->phoneNumber)
            : self::send_email_token($settings->message,$settings->toEmail, $settings->fromEmail,$settings->subject);
        return !is_null($response) ? json_encode(array("status" => "SUCCESS")) : json_encode(array("status"=>"ERROR"));
    }

    /*
     |-------------------------------------------------------------------------------------------
     | PRIVATE FUNCTIONS RELATED TO SENDING OTP
     |-------------------------------------------------------------------------------------------
     */

    /**
     * This function generates and sends the otp token to the user. The
     * transaction id is generated and returned back for validation.
     *
     * @param string $authType  the auth type
     * @param string $email     the email otp has to be sent to
     * @param string $phone     the phone otp has to be sent to
     *
     * @return string
     */
    private function send_otp_token($authType,$email=null,$phone=null)
    {
        $mo_otp_length 	= get_mo_option('otp_length') ? get_mo_option('otp_length') : 5;
        $otp 			      = wp_rand(pow(10,$mo_otp_length-1),pow(10,$mo_otp_length)-1);
	      $otp            = apply_filters("mo_alphanumeric_otp_filter",$otp);


        $customerKey    = get_mo_option('admin_customer_key');
        $stringToHash 	= $customerKey . $otp;
        $transactionId 	= hash("sha512", $stringToHash);
        $response 		= self::httpRequest($authType,$otp,$email,$phone);

        if($response)
        {
            MoPHPSessions::addSessionVar('mo_otptoken',true);
            MoPHPSessions::addSessionVar('sent_on',time());
            $content = array('status' => 'SUCCESS','txId' => $transactionId);
        }
        else
        {
            $content = array('status' => 'FAILURE');
        }

        if(isset($_POST['action']) && $_POST['action']=='miniorange_get_test_response')
        {
            return json_encode($response);
        }

        return json_encode($content);
    }


    /**
     * This function processes the messages and otp to be sent to the user's
     * phone or email.
     *
     * @param string $authType  Email or SMS Authentication type
     * @param string $otp       OTP entered by the user
     * @param string $email     email address of the user to send otp to
     * @param string $phone     phone number of the user to send otp to
     *
     * @return bool|mixed|null
     */
    private function httpRequest($authType,$otp,$email=null,$phone=null)
    {
        $response 	= null;
        switch($authType)
        {
            case 'SMS':
                $message  = get_mo_option('custom_sms_msg')
                    ? mo_(get_mo_option('custom_sms_msg'))
                    : mo_(MoMessages::showMessage(MoMessages::DEFAULT_SMS_TEMPLATE));
                $message  = mo_($message);
                $message  = str_replace('##otp##', $otp, $message);
                $response = $this->send_sms_token($message,$phone);
                break;

            case 'EMAIL':
                $message  = get_mo_option('custom_email_msg')
                    ? mo_(get_mo_option('custom_email_msg'))
                    : mo_(MoMessages::showMessage(MoMessages::DEFAULT_EMAIL_TEMPLATE));
                $message  = mo_($message);
                $message  = stripslashes($message);
                $message  = str_replace('##otp##', $otp, $message);
                $fromEmail= get_mo_option('custom_email_from_id');
                $subject  = get_mo_option('custom_email_subject');
                $fromName = get_mo_option('custom_email_from_name');
                $response = $this->send_email_token($message,$email,$fromEmail,$subject,$fromName);
                break;

        }
        return $response;
    }

    /**
     * @param string $message   message to be sent
     * @param string $phone     phone number to be sent otp to
     * @return mixed
     */
    private function send_sms_token($message,$phone)
    {
        $gateway = GatewayType::instance();
        do_action("mo_send_whatsapp_sms",$message,apply_filters('mo_filter_phone_before_api_call',$phone));
        $response = $gateway->sendOTPRequest($message,$phone);
        return $gateway->handleGatewayResponse($response,$message,$phone);
    }

    /**
     * @param $message
     * @param $email
     * @param null $fromEmail
     * @param null $subject
     * @param null $fromName
     * @return bool
     */
    private function send_email_token($message,$email,$fromEmail = null,$subject=null,$fromName=null)
    {
        $fromEmail  = !MoUtility::isBlank($fromEmail) ? $fromEmail : MoConstants::FROM_EMAIL;
        $subject    = !MoUtility::isBlank($subject) ? $subject : MoMessages::showMessage(MoMessages::EMAIL_SUBJECT);
        $fromName   = !MoUtility::isBlank($fromName) ? $fromName: $fromEmail;
        $headers 	= "From:".$fromName." <".$fromEmail."> \n";
        $headers   .= MoConstants::HEADER_CONTENT_TYPE;
        $content 	= $message;
        return (ini_get('SMTP')!= FALSE)  || (ini_get('smtp_port') != FALSE)
            ? wp_mail($email,$subject,$content,$headers) : false;
    }

    /*
     |-------------------------------------------------------------------------------------------
     | FUNCTIONS RELATED TO VALIDATING OTP
     |-------------------------------------------------------------------------------------------
     */

    /**
     * Validates the OTP entered by the user
     *
     * @param string $txId      Transaction ID from session
     * @param string $otp_token OTP Token to validate
     * @return array
     */
    public function mo_validate_otp_token($txId, $otp_token)
    {
        return  MO_TEST_MODE
            ? (MO_FAIL_MODE ? ['status' => ''] : ['status' => 'SUCCESS'])
            : $this->validate_otp_token($txId,$otp_token);
    }

    /*
     |-------------------------------------------------------------------------------------------
     | PRIVATE FUNCTIONS RELATED TO SENDING OTP
     |-------------------------------------------------------------------------------------------
     */
    /**
     * This function validates the otp token entered by the user.
     *
     * @param string $transactionId the transactionID generated during otp sending
     * @param string $otpToken      the otp token entered by the user
     *
     * @return array
     */
    private function validate_otp_token($transactionId,$otpToken)
    {

        $customerKey = get_mo_option('admin_customer_key');
        if(MoPHPSessions::getSessionVar('mo_otptoken'))
        {
            $pass =	$this->checkTimeStamp(MoPHPSessions::getSessionVar('sent_on'),time());
            $pass = $this->checkTransactionId($customerKey,$otpToken,$transactionId,$pass);
            if($pass)
                $content = array('status' => MoConstants::SUCCESS);
            else
                $content = array('status' => MoConstants::FAILURE);
            MoPHPSessions::unsetSession('$mo_otptoken');
        }
        else
            $content = array('status' => MoConstants::FAILURE);
        return $content;
    }

    /**
     * This function checks the time otp was sent to and the time
     * user is validating the otp. The time difference shouldn't be
     * more that 60 seconds.
     *
     * @param string $sentTime      the time otp was sent to
     * @param string $validatedTime the time otp was validated
     *
     * @return bool
     */
    private function checkTimeStamp($sentTime,$validatedTime)
    {
        $mo_otp_validity = get_mo_option('otp_validity') ? get_mo_option('otp_validity') : 5;
        $diff = round(abs($validatedTime - $sentTime) / 60,2);
        return $diff > $mo_otp_validity ? false : true;
    }


    /**
     * This function checks and compares the transaction set in session
     * and one generated during validation. Both need to match for the
     * otp to be validated.
     *
     * @param string    $customerKey    the customer key of the user
     * @param string    $otpToken       otp token entered by the user
     * @param string    $transactionId  the transaction id in session
     * @param string    $pass           the boolean value passed after the time check
     *
     * @return bool
     */
    private function checkTransactionId($customerKey,$otpToken,$transactionId,$pass)
    {
        if(!$pass) return false;
        $stringToHash 	= $customerKey . $otpToken;
        $txtID 			= hash("sha512", $stringToHash);
        return $txtID===$transactionId;
    }
}
