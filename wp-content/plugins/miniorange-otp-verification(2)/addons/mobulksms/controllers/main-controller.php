<?php

use OTP\Addons\MoBulkSMS\Handler\MoBulkSMSAddonHandler;

$registered 	= MoBulkSMSAddonHandler::instance()->moAddOnV();
$disabled  	 	= !$registered ? "disabled" : "";
$current_user 	= wp_get_current_user();
$controller 	= MOV_BULK_SMS_DIR . 'controllers/';
$addon          = add_query_arg( array('page' => 'addon'), remove_query_arg('addon',$_SERVER['REQUEST_URI']));

if(isset( $_GET[ 'addon' ]))
{
    switch($_GET['addon'])
    {
        case 'mo_bulk_sms':
            include $controller . 'MoBulkSMSController.php'; break;
    }
}