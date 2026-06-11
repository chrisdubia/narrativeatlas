<?php

use OTP\Addons\MoBulkSMS\Handler\MoBulkSMSAddonHandler;

$otp_control_enable = get_mo_option('otp_control_enable')?"checked":"";
$otp_control_hidden = $otp_control_enable?"":"hidden";
$otp_control_limit = get_mo_option('otp_control_limit')?get_mo_option('otp_control_limit'):2;
$otp_control_time_block =  get_mo_option('otp_control_block_time')?get_mo_option('otp_control_block_time'):2;
$handler 			   = MoBulkSMSAddonHandler::instance();
$nonce 			   = $handler->getNonceValue();
$otp_timer_enable = get_mo_option('otp_timer_enable')?"checked":"";
$otp_timer_hidden = $otp_timer_enable?"":"hidden";
$otp_timer = get_mo_option('otp_timer')?get_mo_option('otp_timer'):2;


include MOV_BULK_SMS_DIR . 'views/MoBulkSMSView.php';