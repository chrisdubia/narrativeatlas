<?php

use OTP\Helper\MoMessages;

echo'   <div class="mo_registration_divided_layout mo-otp-full">
            <div class="mo_registration_table_layout mo-otp-center">';

echo '<div class="mo-loader-div" id="mo-loader-div" style ="display:none;position: fixed;background-color: #aca7a7;opacity: 0.9;height: 100%;width: 100%;top: 0;left: 0;z-index: 99;"><span id="mo-loading-span" style="display:inline-flex;position:fixed;height:2em;width:25em;overflow:show;margin:auto;top:0;left:0;bottom:0;right:0;">SMSs are being sent, please do not refresh or go back.</span></div>';

echo'           <table style="width:100%">
                    <form name="f" method="post" action="" id="mo_bulk_sms_settings">
                        <input type="hidden" id="error_message" name="error_message" value="">';

                        wp_nonce_field($nonce);

echo'                       <tr>
                                <td colspan="2">
                                    <h2>'.mo_("miniOrange Bulk SMS Addon").'
                                        <span style="float:right;margin-top:-10px;">
                                            <a  href="'.$addon.'" 
                                                id="goBack" 
                                                class="button button-primary button-large">
                                                '.mo_("Go Back").'
                                            </a>
                                        </span>
                                    </h2></td></tr>
                                    <tr>
                                <td colspan="2">   <hr>Enable this addon to send SMSs in bulk.</hr><br><br></td></tr></td>
                            </tr>
                                 ';
echo ' 
<tr>
<td>
        <div style="background: #efe8e87a;color: red;padding: 10px;"><span id="mo_bulk_sms_message">Please do not refresh or go back while sending the SMSs in bulk.</span></div><br>
        <div style="background: #efe8e87a;color: red;padding: 10px;display:none" id="mo_bulk_sms_message_error_div"><span  id= "mo_bulk_sms_message_span" style="display:none"></span><br></div>
</td>
</tr>
<tr>
<td colspan="2">
        <b>CSV BULK SMS UPLOAD FILE:</b>  <input type="file"  style="padding: 10px;-webkit-border-radius: 5px;border: 1px dashed #BBB;background-color: #efe8e87a;cursor: pointer;" class="form-control" id="mo_bulk_file" name="mo_bulk_file"/><br><br>
        <div style="background: #efe8e87a;color: black;padding: 10px;display:block"><span>You can download the sample file from here: 
        <a href="'.MOV_BULK_SMS_URL . "/includes/downlodable/samplebulkSMSupload.csv".'" target="_blank" >Download Sample CSV</a></span></div><br>
</td>
</tr>
<tr>
<td style="text-align: center;background: #efe8e87a;"><b>OR</b><br></td>
</tr>
<tr>
<td colspan="2">
        <b>Input Comma Seperated Phone Numbers:</b><br><br>  <textarea rows="3" cols="50" class="form-control" id="mo_phone_number_csv" name="mo_phone_number_csv" placeholder="+178945613,+44189456123,+91798456123"></textarea>
</td>
</tr>
<tr>
<td colspan="2">
        <b>Input Common Message for above Provided Phone Numbers:</b><br><br>  <textarea rows="3" cols="50" class="form-control" id="mo_bulk_sms_body_csv" name="mo_bulk_sms_body_csv" placeholder="'.mo_("Dear user, you have been notified by the administrator of site: ").get_site_url().'"></textarea><br><br>
</td>
</tr>

<tr>
<td>
    <input type="button" name="mo-bulk-sms-submit" id="mo-bulk-sms-submit" class="button button-primary button-large" value="Bulk Send"/>
</tr>
</td>';
echo'
                                </td>
                            </tr>
                        </form> 
                    </table>
                </div>
            </div>';

echo '';