<?php

use OTP\Helper\MoMessages;

echo'	<div class="mo_otp_form" id="'.get_mo_class($handler).'">
            <input  type="checkbox" '.$disabled.'
                    id="bb_login"
                    class="app_enable"
                    data-toggle="bb_login_options"
                    name="mo_customer_validation_bb_login_enable"
                    value="1"
                    '.$bb_login_enabled.' />
            <strong>'.$form_name.'</strong>';

echo'	    <div class="mo_registration_help_desc" '.$bb_login_hidden.' id="bb_login_options">
    
                 <p>
                    <input  type="radio" '.$disabled.'
                            id="bb_form_phone"
                            class="app_enable"
                            data-toggle="bb_phone_option"
                            name="mo_customer_validation_bb_login_enable_type"
                            value="'.$bb_phone_type.'"
                            '.( $bb_enabled_type == $bb_phone_type ? "checked" : "").' />
                    <strong>'. mo_( "Enable Phone Verification" ).'</strong>
                 </p>
                 <div   '.($bb_enabled_type != $bb_phone_type ? "hidden" :"").'
                        class="mo_registration_help_desc"
                        id="bb_phone_option" '.$disabled.'">
                    '. mo_( "Follow the following steps to add a users phone number in the database" ).':
                    <ol>
                        <li>'. mo_( "Enter the phone User Meta Key" );

                            mo_draw_tooltip(
                                MoMessages::showMessage(MoMessages::META_KEY_HEADER),
                                MoMessages::showMessage(MoMessages::META_KEY_BODY)
                            );

echo'					    : <input    class="mo_registration_table_textbox"
                                        id="mo_customer_validation_bb_login_phone_field_key"
                                        name="mo_customer_validation_bb_login_phone_field_key"
                                        type="text"
                                        value="'.$bb_login_field_key.'">
                            <div class="mo_otp_note" style="margin-top:1%">
                                '.mo_( "If you don't know the metaKey against which the phone number ".
                                        "is stored for all your users then put the default value as phone." ).'
                            </div>
                        </li>                            
                        <li>'. mo_( "Click on the Save Button to save your settings." ).'</li>
                    </ol>

                    <input  type="checkbox" '.$disabled.' id="bb_login_reg" 
                            name="mo_customer_validation_bb_login_register_phone" value="1"
                        '.$bb_login_enabled_type .' />'.
                        '<strong>'.
                            mo_( "Allow the user to add a phone number if it does not exist." ).
                        '</strong>
                    <p>
                        <input  type="checkbox" '.$disabled.'
                                id="bb_login_admin"
                                name="mo_customer_validation_bb_login_allow_phone_login"
                                value="1"
                                class="app_enable"
                                data-toggle="bb_change_labels"
                                '.$bb_login_with_phone.' /><strong>'.
                                mo_( "Allow users to login with their phone number." ).'</strong>
                        <div    '.( !$bb_login_with_phone ? "hidden":"").'
                                id="bb_change_labels"
                                class="mo_registration_help_desc">
                            <p style="margin-left:2%;">
                                <i><b>'.mo_("Username Field text").' : </b></i>
                                    <input  class="mo_registration_table_textbox"
                                            name="mo_customer_validation_bb_username_label_text"
                                            type="text"
                                            value="'.$user_field_text.'">
                            </p>
                        </div>
                    </p>
                    <input  type="checkbox" '.$disabled.'
                            id="bb_login_admin"
                            name="mo_customer_validation_bb_login_restrict_duplicates"
                            value="1"
                            '.$bb_handle_duplicates.' />
                    <strong>'. mo_( "Do not allow users to use the same phone number for multiple accounts." ).'</strong>
                  </div>
                </p>
            </div>
         </div>';


