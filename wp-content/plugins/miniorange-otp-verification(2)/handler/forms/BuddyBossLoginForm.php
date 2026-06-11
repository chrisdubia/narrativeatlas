<?php

namespace OTP\Handler\Forms;

use OTP\Helper\FormSessionVars;
use OTP\Helper\MoConstants;
use OTP\Helper\MoMessages;
use OTP\Helper\MoOTPDocs;
use OTP\Helper\MoPHPSessions;
use OTP\Helper\MoUtility;
use OTP\Helper\SessionUtils;
use OTP\Objects\FormHandler;
use OTP\Objects\IFormHandler;
use OTP\Objects\VerificationType;
use OTP\Traits\Instance;
use ReflectionException;
use \WP_Error;
use \WP_User;

/**
 * This is the WordPress Login Form class. This class handles all the
 * functionality related to WordPress Login. It extends the FormHandler
 * and implements the IFormHandler class to implement some much needed functions.
 */
class BuddyBossLoginForm extends FormHandler implements IFormHandler
{
    use Instance;

    /**
     * Enable disable saving of phone numbers after verification
     * @var string
     */
    private $_savePhoneNumbers;

    /**
     * Allow admins to bypass otp verification
     * @var string
     */
    private $_byPassAdmin;

    /**
     * Allow users to log in with their phone number
     * @var String
     */
    private $_allowLoginThroughPhone;

    /**
     * Skip Password Check and allow users to log
     * in using OTP instead
     * @var bool
     */
    private $_skipPasswordCheck;

    /**
     * The Username field label to be shown to the
     * users.
     * @var string
     */
    private $_userLabel;

    /**
     * The option which tells if admins has set the
     * option to force users to OTP Verification only
     * in certain intervals.
     * @var bool
     */
    private $_delayOtp;

    /**
     * The interval time if $_delayOtp is set.
     * @var int
     */
    private $_delayOtpInterval;

    /**
     * Allow users to fallback to username + password
     * if they don't wish to do login with OTP
     * @var bool
     */
    private $_skipPassFallback;

    /**
     * Create User Action Hook
     * @var string
     */
    private $_createUserAction;

    /**
     * Stores the unix timestamp of when the user did OTP Verification last
     * @var string
     */
    private $_timeStampMetaKey = 'mov_last_verified_dttm';

    private $tempEmailDomain = "narrativeatlastemp.org";

    protected function __construct()
    {
        $this->_isLoginOrSocialForm = TRUE;
        $this->_isAjaxForm = TRUE;
        $this->_formSessionVar = FormSessionVars::WP_LOGIN_REG_PHONE;
        $this->_formSessionVar2 = FormSessionVars::WP_DEFAULT_LOGIN;
        $this->_phoneFormId = '#mo_phone_number';
        $this->_typePhoneTag = 'mo_bb_login_phone_enable';
        $this->_typeEmailTag = 'mo_bb_login_email_enable';
        $this->_formKey = 'BUDDYBOSSLOGINFORM';
        $this->_formName = mo_("BuddyBoss Login Form");
        $this->_isFormEnabled = get_mo_option('bb_login_enable');
        $this->_userLabel = get_mo_option('bb_username_label_text');
        $this->_userLabel = $this->_userLabel ? mo_($this->_userLabel) :mo_("Username, E-mail or Phone No.");
        $this->_skipPasswordCheck = isset($_POST['isPhoneOnly']) && $_POST['isPhoneOnly']=="phone";
        $this->_allowLoginThroughPhone = get_mo_option('bb_login_allow_phone_login');
        $this->_skipPassFallback = get_mo_option('bb_login_skip_password_fallback');
        $this->_delayOtp = get_mo_option('bb_login_delay_otp');
        $this->_delayOtpInterval = get_mo_option('bb_login_delay_otp_interval');
        $this->_delayOtpInterval = $this->_delayOtpInterval ? $this->_delayOtpInterval : 43800;
        $this->_formDocuments = MoOTPDocs::LOGIN_FORM;
        

        if($this->_skipPasswordCheck || $this->_allowLoginThroughPhone) {
            add_action('login_enqueue_scripts',array($this, 'miniorange_register_login_script'));
            add_action('wp_enqueue_scripts'   ,array($this, 'miniorange_register_login_script'));
        }
        parent::__construct();
    }

    /**
     * Function checks if form has been enabled by the admin and initializes
     * all the class variables. This function also defines all the hooks to
     * hook into to make OTP Verification possible.
     */
    function handleForm()
    {
        $this->_otpType = get_mo_option('bb_login_enable_type');
        $this->_phoneKey = get_mo_option('bb_login_key');
        $this->_savePhoneNumbers = get_mo_option('bb_login_register_phone');
        $this->_byPassAdmin = get_mo_option('bb_login_bypass_admin');
        $this->_restrictDuplicates = get_mo_option('bb_login_restrict_duplicates');
        add_filter( 'authenticate', array($this,'_handle_mo_wp_login'), 99, 3 );

        // ajax handler to check if current user is admin. Only invokes if is skippassword and bypassadmin enabled
        add_action("wp_ajax_mo-admin-check", [$this,'isAdmin']);
        add_action("wp_ajax_nopriv_mo-admin-check", [$this,'isAdmin']);
       
        
        if(class_exists("UM")) {
            add_filter('wp_authenticate_user', array($this, '_get_and_return_user'), 99, 2);
        }
        $this->routeData();
        $this->_phoneFieldIdFromDB = $this->getPhoneNumberFieldIDFromDatabase();
        //for invite submit
        add_action( 'bp_actions', [$this,'bp_member_invite_submit'] );

        //for register action
        add_filter("bp_core_validate_user_signup",[$this,"checkUserRegistrationError"],1);
        add_action( 'bp_core_signup_user', [$this,'disable_validation'] );

        //for sms invite
        add_action("wp_ajax_mo-send-sms-invites", [$this,'sendSMSInvite']);
        add_action("wp_ajax_nopriv_mo-send-sms-invites", [$this,'sendSMSInvite']);

        //for Email Invite Enter  
        add_filter("bp_member_invitation_accept_url",[$this,"addGroupsInCurrentAcceptURL"]);


        add_filter( 'bp_registration_needs_activation', [$this,'fix_signup_form_validation_text'] );
        // $this->checkInvitations();
    }

    function bp_member_invite_submit(){
        global $bp;
        if(isset($_POST['member-invite-submit']) && $_POST['member-invite-submit'] == 'Send Invites'){
            if(!isset($_POST['mo_invitee_group']) || $_POST['mo_invitee_group'] == ""){
                bp_core_add_message( __( 'You didn\'t include any groups!', 'buddyboss' ), 'error' );
                bp_core_redirect( $bp->loggedin_user->domain . '/invites' );
                die();
            }

        }
    }
    function checkUserRegistrationError($result){
        if(!empty($result['errors']->errors)) return $result;

        $phoneNumber = $_POST['field_'.$this->_phoneFieldIdFromDB];
        $userid = $this->getUserIDFromPhoneNumber($phoneNumber);
        if($userid){
            $errors = $result['errors'];
            $errors->add( 'user_email', __( 'Phone number is already in use. Please use another number.', 'buddyboss' ) );
            return $result;
        }
        
        return $result;
    }
    function disable_validation($user_id){
        global $wpdb;
        if( isset($_GET['bp-invites']) && $_GET['bp-invites'] == "accept-member-invitation" && isset($_GET['groupsInvited']))
            $groupdInvited = $_GET['groupsInvited'];

        if(!empty($groupdInvited)){
            $groupsArray = $this->getGroupsArray($groupdInvited);
            $this->addGroupsToUser($wpdb,$user_id,$groupsArray);
        }
        //activating user
        $result = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->users SET user_status = 0 WHERE ID = %d", $user_id ) );
        
        if($result){
           
        }

    }

    function addGroupsToUser($wpdb,$userid,$groupsArray){
        foreach ($groupsArray as $key => $value) {
            if(!empty($value))        
            $result = $wpdb->insert($wpdb->prefix .'bp_groups_members', array(
                'group_id' => $value,
                'user_id' => $userid,
                'inviter_id' => '0',
                'is_admin'=>'0',
                'date_modified'=> current_time( 'mysql' ),
                'is_confirmed'=>'1',
                'is_banned' => '0',
                'invite_sent' => '0'
            ));     
        }
    }

    function getGroupsArray($groupdInvited){
        $groupdInvited = explode(",", $groupdInvited);
        return $groupdInvited;
    }

    function fix_signup_form_validation_text(){
        return false;
    }




    function addGroupsInCurrentAcceptURL($acceptLink){
        if(!isset($_POST['mo_invitee_group'])) return $acceptLink;
        $groups = explode(",",$_POST['mo_invitee_group']);

        $groupId = "";
            foreach ($groups as $key => $value) {
                $groupId .= explode("::", $value)[1] . ",";
            }
        return $acceptLink . "&groupsInvited=" . urlencode($groupId);
    }

    function sendSMSInvite(){
        $inviteeSMSEmail = isset($_POST['user_name']) ? $_POST['user_name'] : "";
        $inviteeSMSPhone = isset($_POST['user_phone']) ? $_POST['user_phone'] : "";

        if(!empty($inviteeSMSPhone)){
            $phoneString = $inviteeSMSPhone[0];
            $inviteeSMSPhone = explode( "," , $phoneString);
            $inviteeSendNotifTo = "phone";
            $inviteeFinalArray =  $inviteeSMSPhone;
        }
        elseif (!empty($inviteeSMSEmail)){
            $emailString = $inviteeSMSEmail[0];
            $inviteeSMSEmail = explode( "," ,$emailString );
            $inviteeSendNotifTo = "email";
            $inviteeFinalArray =  $inviteeSMSEmail;
        }
        
        $this->sendNotificationToEmailOrPhone($inviteeFinalArray,$inviteeSendNotifTo);
    }

    function sendNotificationToEmailOrPhone($data,$notifType){
        if($notifType == "") return;
            $this->sendSMSinviteToPhoneNumberOrEmail($data,$notifType);
    }




    function sendSMSinviteToPhoneNumberOrEmail($data,$notifType){
        foreach ($data as $key => $value) {
            $phoneNumber = $value;
            $email = $notifType == "phone" ? $phoneNumber . "@". $this->tempEmailDomain : $value;
            $this->sendInviteFunction($phoneNumber,$email,$notifType);
        }
        wp_send_json("email_sent");
        exit;
    }

    function sendInviteFunction($phoneNumber,$email,$inviteSentFrom){

        $invite_correct_array[] = array(
                    'name'        => $_POST['invitee'][ $key ][0],
                    'email'       => $_POST['email'][ $key ][0],
                    'member_type' => ( isset( $_POST['member-type'][ $key ][0] ) && ! empty( $_POST['member-type'][ $key ][0] ) ) ? $_POST['member-type'][ $key ][0] : '',
                );

        foreach ( $invite_correct_array as $key => $value ) {

        if ( true === bp_disable_invite_member_email_subject() ) {
            $subject = stripslashes( strip_tags( $_POST['bp_member_invites_custom_subject'] ) );
        } else {
            $subject = stripslashes( strip_tags( bp_get_member_invitation_subject() ) );
        }

        if ( true === bp_disable_invite_member_email_content() ) {
            $message = stripslashes( strip_tags( $_POST['bp_member_invites_custom_content'] ) );
        } else {
            $message = stripslashes( strip_tags( bp_get_member_invitation_message() ) );
        }

        // $email          = $value['email'];
        $name           = $value['name'];
        $member_type    = $value['member_type'];
        $query_string[] = $email;
        $inviter_name   = bp_core_get_user_displayname( bp_loggedin_user_id() );

        $message .= '

' . bp_get_member_invites_wildcard_replace( stripslashes( strip_tags( bp_get_invites_member_invite_url() ) ), $email );

        $inviter_name = bp_core_get_user_displayname( bp_loggedin_user_id() );
        $site_name    = get_bloginfo( 'name' );
        $inviter_url  = bp_loggedin_user_domain();

        $email_encode = urlencode( $email );

        // set post variable
        $_POST['custom_user_email'] = $email;

        // Set both variable which will use in email.
        $_POST['custom_user_name']   = $name;
        $_POST['custom_user_avatar'] = apply_filters( 'bp_sent_invite_email_avatar', buddypress()->plugin_url . 'bp-core/images/mystery-man.jpg' );

        $accept_link = add_query_arg(
            array(
                'bp-invites' => 'accept-member-invitation',
                'email'      => $email_encode,
                'inviter'    => base64_encode( bp_loggedin_user_id() ),
            ),
            bp_get_root_domain() . '/' . bp_get_signup_slug() . '/'
        );
        $accept_link = apply_filters( 'bp_member_invitation_accept_url', $accept_link );
        

        $args        = array(
            'tokens' => array(
                'inviter.name' => $inviter_name,
                'inviter.url' => $inviter_url,
                'invitee.url'  => $accept_link,
            ),
        );

        // var_dump($accept_link);exit;
        if($inviteSentFrom == "email"){

            add_filter( 'bp_email_get_salutation', '__return_false' );
            // Send invitation email.
            bp_send_email( 'invites-member-invite', $email, $args );

        }
        else if($inviteSentFrom == "phone"){
            $accept_link = str_replace("&inviter=", "&", $accept_link);
            $tinyurl = file_get_contents('http://tinyurl.com/api-create.php?url='.$accept_link);
            $smsBody = 'You have been invited by '.$inviter_name.' to join the NattiveAtlas community. To accept this invitation, please click here: ' . $tinyurl;
            // var_dump($tinyurl);exit;
            MoUtility::send_phone_notif($phoneNumber, $smsBody);
        }

        $insert_post_args = array(
            'post_author'  => $bp->loggedin_user->id,
            'post_content' => $message,
            'post_title'   => $subject,
            'post_status'  => 'publish',
            'post_type'    => bp_get_invite_post_type(),
        );

        if ( ! $post_id = wp_insert_post( $insert_post_args ) ) {
            return false;
        }

        // Save a blank bp_ia_accepted post_meta
        update_post_meta( $post_id, 'bp_member_invites_accepted', '' );
        update_post_meta( $post_id, '_bp_invitee_email', $email );
        update_post_meta( $post_id, '_bp_invitee_name', $name );
        update_post_meta( $post_id, '_bp_inviter_name', $inviter_name );
        update_post_meta( $post_id, '_bp_invitee_status', 0 );
        update_post_meta( $post_id, '_bp_invitee_member_type', $member_type );

        $user_id = bp_loggedin_user_id();

        do_action( 'bp_member_invite_submit', $user_id, $post_id );
        }


    }

    function getCurrentUserGroups(){
        if(isset($_REQUEST['bp-invites']) && $_REQUEST['bp-invites'] && $_REQUEST['bp-invites'] == 'accept-member-invitation') return;
        $loggedInUser = wp_get_current_user();
        $assignedGroupsArray = $this->getCurrentUserGroupsFromDB($loggedInUser->ID);
        $assignedGroupsNames = $this->getGroupsNames($assignedGroupsArray);
        return $assignedGroupsNames;
    }

    function getCurrentUserGroupsFromDB($userId){
        global $wpdb;
        $query = "SELECT `group_id` FROM `{$wpdb->prefix}bp_groups_members`"
                                    ."WHERE `user_id` = $userId and `is_banned` = 0 ";
        $results = $wpdb->get_results($query);
        return !MoUtility::isBlank($results) ? $results : false;
    }

    function getGroupsNames($assignedGroupsArray){
        global $wpdb;
        $array = [];
        if(empty($assignedGroupsArray)) return;
        foreach ($assignedGroupsArray as $key => $value) {
            $group_id = $value->group_id;
            $query = "SELECT `name` FROM `{$wpdb->prefix}bp_groups`"
                                        ."WHERE `id` = $group_id";
            $results = $wpdb->get_row($query);
            if(MoUtility::isBlank($results)) return;
            $array[$key] = $results->name . "::" . $group_id;
        }
        return $array;
    }

    
    function isAdmin()  
    {
        $username = MoUtility::sanitizeCheck("username",$_POST);
        $user = is_email( $username ) ? get_user_by("email",$username) : get_user_by("login",$username);
        $const = MoConstants::SUCCESS_JSON_TYPE;  

        $const = $user ? (in_array('administrator',$user->roles) ? $const : 'error' ) : 'error';
         wp_send_json(MoUtility::createJson(
            MoMessages::showMessage(MoMessages::PHONE_EXISTS),
            $const)
        );

    }

    function routeData()
    {
        if(!array_key_exists('option', $_REQUEST)) return;
        switch (trim($_REQUEST['option']))
        {
            case "miniorange-ajax-otp-generate":
                $this->_handle_wp_login_ajax_send_otp();                break;
            case "miniorange-ajax-otp-validate":
                $this->_handle_wp_login_ajax_form_validate_action();    break;
            case "mo_ajax_form_validate":
                $this->_handle_wp_login_create_user_action();           break;
        }
    }


    function miniorange_register_login_script()
    {
        wp_register_script( 'mologin', MOV_URL . 'includes/js/buddybosslogin.js',array('jquery') );
        wp_localize_script( 'mologin', 'movarlogin', array(
            'userLabel'         =>  $this->_allowLoginThroughPhone ? $this->_userLabel : null,
            'skipPwdCheck'      =>  $this->_skipPasswordCheck,
            'skipPwdFallback'   =>  $this->_skipPassFallback,
            'buttontext'        =>  mo_("Login with OTP"),
            'isAdminAction'     =>  'mo-admin-check',
            'byPassAdmin'       =>  $this->_byPassAdmin,
            'siteURL'           =>  wp_ajax_url(),
            'userGroups'        => $this->getCurrentUserGroups(),
            'tempemaildomain'   => $this->tempEmailDomain,
            'sentEmailPage'     => bp_displayed_user_domain() . 'invites/sent-invites',
            'imgURL'        => MOV_URL. "includes/images/loader.gif",
            //'passNote'        =>  $this->_skipPasswordCheck ? $this->_passNote : null,
        ));
        wp_enqueue_script( 'mologin' );
    }

    /**
     * Return Authenticated User object for Ultimate Member Login
     *
     * @param string|WP_User    $username   username of the user
     * @param string            $password   password of the user
     * @return WP_Error|WP_User
     */
    function _get_and_return_user($username,$password)
    {
        if(is_object($username)) return $username;
        $user = $this->getUser($username,$password);
        if(is_wp_error($user)) return $user;
        UM()->login()->auth_id = $user->data->ID;
        UM()->form()->errors = null;
        return $user;
    }


    /**
     * Function detects if the user trying to log in is an admin and detects
     * if admin has set two factor bypass for Admins. Returns True or False
     *
     * @param WP_User   $user             role or roles of the user trying to log in.
     * @param bool      $skipOTPProcess   skip validating OTP
     * @return bool
     */
    function byPassLogin($user,$skipOTPProcess)
    {
        $user_meta  = get_userdata($user->data->ID);
        $user_role  = $user_meta->roles;
        return (in_array('administrator',$user_role) && $this->_byPassAdmin) // is bypass Admin?
                || $skipOTPProcess                                                 // skip OTP?
                || $this->delayOTPProcess($user->data->ID);
    }


    /**
     * This function is called after the OTP is verified to
     * login the user into WordPress.
     */
    function _handle_wp_login_create_user_action()
    {
        /**
         * Anonymous function that returns the user for the email or
         * username that the user has submitted on the login screen
         *
         * @param $postData
         * @return bool|WP_User
         */
        $getUserFromPost = function($postData) {
            $username = MoUtility::sanitizeCheck("log",$postData);
            if(!$username) {
                $array = array_filter($postData, function($key) {
                    return strpos($key, 'username') === 0;
                },ARRAY_FILTER_USE_KEY);
                $username = !empty($array) ? array_shift($array) : $username;
            }
            return is_email( $username ) ? get_user_by("email",$username) : get_user_by("login",$username);
        };

        $postData = $_POST;

        if(!SessionUtils::isStatusMatch($this->_formSessionVar,self::VALIDATED,$this->getVerificationType())) {
            return;
        }

        $user = $getUserFromPost($postData);
        update_user_meta($user->data->ID, $this->_phoneKey ,$this->check_phone_length($postData['mo_phone_number']));
        $this->login_wp_user($user->data->user_login);
    }


    /**
     * The function is called to login the user
     *
     * @param $user_log - the username of the user logging in
     * @param $extra_data - array of extra data related to the user
     */
    function login_wp_user($user_log,$extra_data=null)
    {
        $user = is_email( $user_log ) ? get_user_by("email",$user_log)  // if $user_log is email
                : ( $this->allowLoginThroughPhone() && MoUtility::validatePhoneNumber($user_log)    // if phone no
                    ? $this->getUserIDFromPhoneNumber(MoUtility::processPhoneNumber($user_log)) : get_user_by("login",$user_log) ); // otherwise username

        wp_set_auth_cookie($user->data->ID);
        if($this->_delayOtp && $this->_delayOtpInterval>0) {
        update_user_meta($user->data->ID,$this->_timeStampMetaKey,time());
        }
        $this->unsetOTPSessionVariables();
        do_action( 'wp_login', $user->user_login, $user );
        $redirect = MoUtility::isBlank($extra_data) ? site_url() : $extra_data;
        wp_redirect($redirect);
        exit;
    }


    /**
     * The function hooks into the authenticate hook of WordPress to
     * start the OTP Verification process.
     *
     * @param $user - the WordPress user data object containing all the user information
     * @param $username - username of the user trying to log in
     * @param $password - password of the user trying to log in
     * @return WP_Error|WP_User
     * @throws ReflectionException
     */
    function _handle_mo_wp_login($user,$username,$password)
    {
        if(!MoUtility::isBlank($username)) {
            $skipOTPProcess = $this->skipOTPProcess($password);        
            $user = $this->getUser($username,$password); // get user object based on username and password passed
            if(is_wp_error($user)) return $user;                            // return user if wp_error
            if(!$this->_skipPasswordCheck && isset($_POST['pwd']) && $_POST['pwd']!='**********') return $user;
    
            if($this->byPassLogin($user,$skipOTPProcess)) return $user;     // by pass otp check for admin?

            $this->startOTPVerificationProcess($user,$username,$password);
        }
        return $user;
    }


    /**
     * Function checks the type of verification enabled by the admins and then starts the appropriate
     * OTP Verification.
     *
     * @param WP_User $user the user object of the user who needs to be logged in
     * @param string $username the username provided by the user
     * @param string $password the password provided by the user
     * @throws ReflectionException
     */
    function startOTPVerificationProcess($user,$username,$password)
    {
        $otpType = $this->getVerificationType();
        $otpType = is_email($username) ? 'email' : (MoUtility::validatePhoneNumber($username) ? 'phone' : 'email');
        if(SessionUtils::isStatusMatch($this->_formSessionVar,self::VALIDATED,$otpType)
            || SessionUtils::isStatusMatch($this->_formSessionVar2,self::VALIDATED,$otpType)) {
            return;
        }

        if($otpType===VerificationType::PHONE){
            $phone_number = $this->getUserPhoneNumberFromDB($user->ID);
            $phone_number = $this->check_phone_length($phone_number);
            $this->askPhoneAndStartVerification($user,$this->_phoneKey,$username,$phone_number);
            $this->fetchPhoneAndStartVerification($username,$password,$phone_number);
        } else if($otpType===VerificationType::EMAIL){
            $email= $user->data->user_email;
            $this->startEmailVerification($username,$email);
        }
    }


    /**
     * This functions checks if user has enabled phone number as a valid username and fetches the user
     * associated with the phone number. Checks if the skip Password is enabled with feedback to handle
     * OTP login and normal login.
     *
     * @param string    $username           the user's username
     * @param string    $password           the users's password
     * @return WP_Error|WP_User
     */
    function getUser($username, $password = null)
    {
        $user = is_email( $username ) ? get_user_by("email",$username) : get_user_by("login",$username);
        if($this->_allowLoginThroughPhone && MoUtility::validatePhoneNumber($username)){
            $username = MoUtility::processPhoneNumber($username);
            $user = $this->getUserIDFromPhoneNumber($username);
        }
        if($user && !$this->isLoginWithOTP($user->roles) ){
            $user = wp_authenticate_username_password(NULL,$user->data->user_login,$password);
        }
        return $user ? $user : new WP_Error( 'INVALID_USERNAME' , mo_(" <b>ERROR:</b> Invalid UserName. ") );
    }


    /**
     * This functions fetches the user associated with a phone number
     *
     * @param string $username  the user's username
     * @return bool|WP_User
     */
    function getUserIDFromPhoneNumber($username)
    {
        global $wpdb;
        $query = "SELECT `user_id` FROM `{$wpdb->prefix}bp_xprofile_data`"
                                    ."WHERE `field_id` = $this->_phoneFieldIdFromDB AND `value` =  '$username'";
        $results = $wpdb->get_row($query);
        return !MoUtility::isBlank($results) ? get_userdata($results->user_id) : false;
    }

    /**
     * This functions fetches the user associated with a phone number
     *
     * @param string $username  the user's username
     * @return bool|WP_User
     */
    function getUserPhoneNumberFromDB($userID)
    {
        global $wpdb;
        $query = "SELECT `value` FROM `{$wpdb->prefix}bp_xprofile_data`"
                                    ."WHERE `field_id` = $this->_phoneFieldIdFromDB AND `user_id` =  '$userID'";
        $results = $wpdb->get_row($query);
        return !MoUtility::isBlank($results) ? $results->value : false;
    }


    /**
     * This functions is used to ask users the phone number and start the otp verification
     * process.
     *
     * @param string $user the WordPress user data object containing all the user information
     * @param string $key the phone user_meta key which stores the user's phone number
     * @param string $username the user's username
     * @param string $phone_number the phone number entered by the user
     * @throws ReflectionException
     */
    function askPhoneAndStartVerification($user,$key,$username,$phone_number)
    {
        if(!MoUtility::isBlank($phone_number)) return;

        if( !$this->savePhoneNumbers() ) {
            miniorange_site_otp_validation_form(null, null, null,
                MoMessages::showMessage(MoMessages::PHONE_NOT_FOUND), null, null);
        } else {
            MoUtility::initialize_transaction($this->_formSessionVar);
            $this->sendChallenge(
                NULL,$user->data->user_login,NULL,NULL,
                'external',NULL, [
                    'data'=>array('user_login'=>$username),
                    'message'=>MoMessages::showMessage(MoMessages::REGISTER_PHONE_LOGIN),
                    'form'=>$key,'curl'=>MoUtility::currentPageUrl()
                ]
            );
        }
    }


    /**
     * This functions is used to fetch the phone number from the database and start
     * the OTP Verification process.
     *
     * @param $username - the user's username
     * @param $password - the password provided by the user.
     * @param $phone_number - phone number to send otp to
     * @throws ReflectionException
     */
    function fetchPhoneAndStartVerification($username,$password,$phone_number)
    {
        MoUtility::initialize_transaction($this->_formSessionVar2);
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : MoUtility::currentPageUrl();
        $this->sendChallenge($username,null,null,$phone_number,VerificationType::PHONE, $password,$redirect_to,false);
    }


    /**
     * This functions is used to  start the otp verification process via email.
     *
     * @param $username - the user's username
     * @param $email - email to send otp to
     * @throws ReflectionException
     */
    function startEmailVerification($username,$email)
    {
        MoUtility::initialize_transaction($this->_formSessionVar2);
        $this->sendChallenge($username,$email,null,null,VerificationType::EMAIL);
    }


    /**
     * This function is used to send the OTP to the user's phone number.
     */
    function _handle_wp_login_ajax_send_otp()
    {
        $data = $_POST;

        if($this->restrictDuplicates()
            && !MoUtility::isBlank($this->getUserIDFromPhoneNumber($data['user_phone']))) {
            wp_send_json(MoUtility::createJson(
                MoMessages::showMessage(MoMessages::PHONE_EXISTS),
                MoConstants::ERROR_JSON_TYPE)
            );
        }elseif(SessionUtils::isOTPInitialized($this->_formSessionVar)) {
            $this->sendChallenge('ajax_phone', '', null, trim($data['user_phone']), VerificationType::PHONE, null, $data);
        }
    }


    /**
     * This function is used to process the OTP entered by the user. Check
     * if the phone number being sent is the same one OTP was sent to .
     */
    function _handle_wp_login_ajax_form_validate_action()
    {
        $data = $_POST;

        if (!SessionUtils::isOTPInitialized($this->_formSessionVar)) return;

        $phone = MoPHPSessions::getSessionVar('phone_number_mo');
        if (strcmp($phone, $this->check_phone_length($data['user_phone']))) {
            wp_send_json(MoUtility::createJson(
                MoMessages::showMessage(MoMessages::PHONE_MISMATCH), MoConstants::ERROR_JSON_TYPE)
            );
        }else {
            $this->validateChallenge($this->getVerificationType());
        }
    }


    /**
     * This function hooks into the otp_verification_failed hook. This function
     * details what is done if the OTP verification fails.
     *
     * @param string $user_login the username posted by the user
     * @param string $user_email the email posted by the user
     * @param string $phone_number the phone number posted by the user
     * @param string $otpType the verification type
     */
    function handle_failed_verification($user_login,$user_email,$phone_number,$otpType)
    {
        if(SessionUtils::isOTPInitialized($this->_formSessionVar)){
            SessionUtils::addStatus($this->_formSessionVar,self::VERIFICATION_FAILED,$otpType);
            wp_send_json( MoUtility::createJson( MoUtility::_get_invalid_otp_method(), MoConstants::ERROR_JSON_TYPE) );
        }

        if(SessionUtils::isOTPInitialized($this->_formSessionVar2)) {
            miniorange_site_otp_validation_form($user_login, $user_email, $phone_number,
                MoUtility::_get_invalid_otp_method(), "phone", FALSE);
        }
    }


    /**
     * This function hooks into the otp_verification_successful hook. This function is
     * details what needs to be done if OTP Verification is successful.
     *
     * @param string $redirect_to the redirect to URL after new user registration
     * @param string $user_login the username posted by the user
     * @param string $user_email the email posted by the user
     * @param string $password the password posted by the user
     * @param string $phone_number the phone number posted by the user
     * @param string $extra_data any extra data posted by the user
     * @param string $otpType the verification type
     */
    function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data,$otpType)
    {
        if(SessionUtils::isOTPInitialized($this->_formSessionVar)) {
            SessionUtils::addStatus($this->_formSessionVar,self::VALIDATED,$otpType);
            wp_send_json( MoUtility::createJson('',MoConstants::SUCCESS_JSON_TYPE) );
        }

        if(SessionUtils::isOTPInitialized($this->_formSessionVar2)) {
            $username = MoUtility::isBlank($user_login) ? MoUtility::sanitizeCheck('log',$_POST) : $user_login;
            $username = MoUtility::isBlank($username) ? MoUtility::sanitizeCheck('username',$_POST) : $username;
            $this->login_wp_user($username,$extra_data);
        }
    }


    /**
     * Unset all the session variables so that a new form submission starts
     * a fresh process of OTP verification.
     */
    public function unsetOTPSessionVariables()
    {
        SessionUtils::unsetSession([$this->_txSessionId, $this->_formSessionVar,$this->_formSessionVar2]);
    }


    /**
     * This function is called by the filter mo_phone_dropdown_selector
     * to return the Jquery selector of the phone field. The function will
     * push the formID to the selector array if OTP Verification for the
     * form has been enabled.
     *
     * @param  $selector - the Jquery selector to be modified
     * @return array
     */
    public function getPhoneNumberSelector($selector)
    {
        if($this->isFormEnabled()) {
            array_push($selector, $this->_phoneFormId);
        }
        return $selector;
    }


    /**
     * Checks if user has initiated login with OTP
     * @return TRUE or FALSE
     */
    private function isLoginWithOTP($user_roles=[])
    {
        $loginWithOTPText = $this->_skipPasswordCheck == "phone";
        if(in_array('administrator',$user_roles) && $this->_byPassAdmin) return false;
        return $loginWithOTPText;
    }

    /**
     * check if the user needs to be validated via OTP. Makes sure to check if admin has
     * allowed fallback. If so check if password is entered by the user. If password is entered
     * then do not initiate OTP
     *
     * @param string $password  password entered by the user
     * @return bool
     */
    private function skipOTPProcess($password)
    {
        return $this->_skipPasswordCheck    // is skipPassword check enabled
            && $this->_skipPassFallback     // is password fallback enabled
            && isset($password)             // is password submitted
            && !$this->isLoginWithOTP();    // is login with OTP button not clicked
    }

    // function to check the length of the phone number
    private function check_phone_length($phone)
    {
        $phone_check=MoUtility::processPhoneNumber($phone);
        return strlen($phone_check)>=5 ? $phone_check: "";
            
    }

    /**
     * Checks to see if delay OTP has been enabled and if user's last verified DTTM is
     * greater or equal to the time interval that has been set.
     *
     * @param int   $user_id    user id of the user
     * @return bool TRUE or FALSE
     */
    private function delayOTPProcess($user_id)
    {
        if($this->_delayOtp && $this->_delayOtpInterval<0) return TRUE;
        $lastVerifiedDTTM = get_user_meta($user_id,$this->_timeStampMetaKey,true);
        if(MoUtility::isBlank($lastVerifiedDTTM)) return FALSE;
        $timeDiff = time() - $lastVerifiedDTTM;
        return $this->_delayOtp && $timeDiff < ($this->_delayOtpInterval * 60);
    }


    /**
     * Handles saving all the WordPress Login Form related options by the admin.
     */
    function handleFormOptions()
    {
        if(!MoUtility::areFormOptionsBeingSaved($this->getFormOption())) return;

        $this->_isFormEnabled = $this->sanitizeFormPOST('bb_login_enable');
        $this->_savePhoneNumbers = $this->sanitizeFormPOST('bb_login_register_phone');
        $this->_byPassAdmin = $this->sanitizeFormPOST('bb_login_bypass_admin');
        $this->_phoneKey = $this->sanitizeFormPOST('bb_login_phone_field_key');
        $this->_allowLoginThroughPhone = $this->sanitizeFormPOST('bb_login_allow_phone_login');
        $this->_restrictDuplicates = $this->sanitizeFormPOST('bb_login_restrict_duplicates');
        $this->_otpType = $this->sanitizeFormPOST('bb_login_enable_type');
        $this->_skipPasswordCheck = $this->sanitizeFormPOST('bb_login_skip_password');
        $this->_userLabel = $this->sanitizeFormPOST('bb_username_label_text');
        $this->_skipPassFallback = $this->sanitizeFormPOST('bb_login_skip_password_fallback');
        $this->_delayOtp = $this->sanitizeFormPOST('bb_login_delay_otp');
        $this->_delayOtpInterval = $this->sanitizeFormPOST('bb_login_delay_otp_interval');
        //$this->_passNote = $this->sanitizeFormPOST('um_password_note_text');

        update_mo_option('bb_login_enable_type', $this->_otpType);
        update_mo_option('bb_login_enable', $this->_isFormEnabled);
        update_mo_option('bb_login_register_phone', $this->_savePhoneNumbers);
        update_mo_option('bb_login_bypass_admin', $this->_byPassAdmin);
        update_mo_option('bb_login_key', $this->_phoneKey);
        update_mo_option('bb_login_allow_phone_login', $this->_allowLoginThroughPhone);
        update_mo_option('bb_login_restrict_duplicates', $this->_restrictDuplicates);
        update_mo_option('bb_login_skip_password', $this->_skipPasswordCheck && $this->_isFormEnabled);
        update_mo_option('bb_login_skip_password_fallback', $this->_skipPassFallback);
        update_mo_option('bb_username_label_text', $this->_userLabel);
        update_mo_option('bb_login_delay_otp', $this->_delayOtp && $this->_isFormEnabled);
        update_mo_option('bb_login_delay_otp_interval', $this->_delayOtpInterval);
    }

    public function getPhoneNumberFieldIDFromDatabase(){
        global $wpdb;
        $results = $wpdb->get_row("SELECT `id` FROM `{$wpdb->prefix}bp_xprofile_fields`"
                                    ."WHERE `name` = '$this->_phoneKey'");
        return !MoUtility::isBlank($results) ? $results->id : false;
    }


    /*
    |--------------------------------------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------------------------------------
    */

    /**
     * Checks if admin has set the option to save the phone number in the database for each user.
     * @return string
     */
    public function savePhoneNumbers() { return $this->_savePhoneNumbers; }

    /**
     * Checks if admin has set the option to bypass two factor for logged in users.
     * @return string
     */
    function byPassCheckForAdmins() { return $this->_byPassAdmin; }

    /**
     * Checks if admin has set the option to allow phone number login
     * @return String
     */
    function allowLoginThroughPhone() { return $this->_allowLoginThroughPhone; }

    /**
     * Checks if admin has set the option to allow login through username+otp
     * @return bool|String
     */
    public function getSkipPasswordCheck() { return $this->_skipPasswordCheck; }

    /**
     * Gets the User Label Text to be shown on the Default Login Form
     * @return string
     */
    public function getUserLabel() { return mo_($this->_userLabel); }

    /**
     * Checks if admin has set the option to allow users to use username + password as well as username + otp
     * @return bool
     */
    public function getSkipPasswordCheckFallback() { return $this->_skipPassFallback; }

    /**
     * Getter for $_delayOtp
     * @return bool
     */
    public function isDelayOtp(){ return $this->_delayOtp; }

    /**
     * Getter for $_delayOtpInterval
     * @return int
     */
    public function getDelayOtpInterval(){ return $this->_delayOtpInterval; }
}
