<?php

//wp login form
use OTP\Handler\Forms\BuddyBossLoginForm;
/** @var WPLoginForm $handler */
$handler                = BuddyBossLoginForm::instance();
$bb_login_enabled 		= (Boolean) $handler->isFormEnabled() ? "checked" : "";
$bb_login_hidden 		= $bb_login_enabled == "checked" ? "" : "hidden";
$bb_login_enabled_type 	= (Boolean) $handler->savePhoneNumbers() ? "checked" : "";
$bb_login_field_key    	= $handler->getPhoneKeyDetails();
$bb_login_admin			= (Boolean) $handler->byPassCheckForAdmins() ? "checked" : "";
$bb_login_with_phone 	= (Boolean) $handler->allowLoginThroughPhone() ? "checked" : "";
$bb_handle_duplicates   = (Boolean) $handler->restrictDuplicates() ? "checked" : "";
$bb_enabled_type        = $handler->getOtpTypeEnabled();
$bb_phone_type          = $handler->getPhoneHTMLTag();
$bb_email_type          = $handler->getEmailHTMLTag();
$form_name              = $handler->getFormName();
$skip_pass              = $handler->getSkipPasswordCheck() ? "checked" : "" ;
$skip_pass_fallback_div = $handler->getSkipPasswordCheck() ? "block" : "hidden";
$skip_pass_fallback     = $handler->getSkipPasswordCheckFallback() ? "checked" : "";
$user_field_text        = $handler->getUserLabel();
$otpd_enabled           = $handler->isDelayOtp() ? "checked" : "";
$otpd_enabled_div       = $handler->isDelayOtp() ? "block" : "hidden";
$otpd_time_interval     = $handler->getDelayOtpInterval();

get_plugin_form_link($handler->getFormDocuments());
include MOV_DIR . 'views/forms/BuddyBossLoginForm.php';