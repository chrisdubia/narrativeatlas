<?php

echo'	<div class="mo_registration_divided_layout mo-otp-full">
			<div class="mo_registration_table_layout mo-otp-half" id="mo-sms-configuration">
				<table class="mo_registration_settings_table" style="width: 100%;">
					<form name="f" method="post" action="" id="sms-configuration-form">
						'.$nonce_field.'
						<input type="hidden" name="option" value="mo_customer_validation_sms_configuration" />
						<tr id="sms">
							<td>
								<h2>
									'.$sms_title.'
									<span style="float:right;margin-top:-10px;">
										<input '.$disabled.'	type="submit" 
												name="submit" 
												value="'.$submit_button_txt.'"
												class="button button-primary button-large" />
									</span>
								</h2>
								<hr>
							</td>
						</tr>
						<tr>
							<td>
								<b>'.$sms_msg.':</b><br>
								<div>					
									 <textarea '.$disabled.' id="custom_sms_msg" 
									            class="mo_registration_table_textbox" 
												name="mo_customer_validation_custom_sms_msg" 
												rows="3" style="height:unset"
									            placeholder="'.$sms_msg_placeholder.'" required>'.$sms_template.
                                    '</textarea>
									 <br>
									 <div class="mo_otp_note">
									 	<i>
									 		<span style="color:red">
									 			'.$sms_msg_note.'
									 		</span>
									 	</i>
									 </div>
								</div>
								';

echo'						</td>
						</tr>	
					</form>
				</table>
			</div>

			<div class="mo_registration_table_layout mo-otp-half" style="float:right;">
				<table class="mo_registration_settings_table" style="width: 100%;">
					<form name="f" method="post" action="" id="configuration-email-form">
					'.$nonce_field.'
					<input type="hidden" name="option" value="mo_customer_validation_email_configuration" />
						<tr id="email">
							<td> 
								<h2>
									'.$email_title.'
									<span style="float:right;margin-top:-10px;">
										<input  type="submit" 
												name="submit" 
												value="'.$mail_submit_btn_txt.'"
												class="button button-primary button-large" />
									</span>
								</h2> 
								<hr>
							</td>
						</tr>
						<tr>
							<td>
								<div class="mo_otp_note">
									<span style="color:red">
										<b>Note: </b>'.$email_note.'
									</span>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<b>'.$from_id.':</b>
								<div >
									 <input '.$disabled.' 
								            id="custom_email_from_id" 
								            class="mo_registration_table_textbox" 
								            style="border:1px solid #ddd" 
								            name="mo_customer_validation_custom_email_from_id" 
								            placeholder="'.$mail_frm_addr.'" 
								            value = "'.$email_from_id.'" required/>
								</div><br>
								<b>'.$from_name.':</b>
								<div >
									 <input  '.$disabled.' 
								            id="custom_email_from_name" 
								            class="mo_registration_table_textbox" 
								            style="border:1px solid #ddd" 
								            name="mo_customer_validation_custom_email_from_name" 
								            placeholder="'.$mail_frm_pholder.'" 
								            value = "'.$email_from_name.'" required/>
								</div><br>
								<b>'.$subject.':</b>
								<div >
									 <input '.$disabled.' 
									        id="custom_email_subject" 
									        class="mo_registration_table_textbox" 
									        style="border:1px solid #ddd" 
									        name="mo_customer_validation_custom_email_subject" 
									        placeholder="'.$mail_sub_pholder.'" 
									        value = "'.$email_subject.'" required/>
								</div><br>
								<b>'.$body.':</b>';

                                wp_editor( $content, $editor_id ,$template_settings);

echo'						</td>
						</tr>
					</form>
				</table>
			</div>
			<div class="mo_registration_table_layout mo-otp-half">
			<form name="f" method="post" action="" id="gateway-configuration-form">
				<table class="mo_registration_settings_table" style="width: 100%;">
						'.$nonce_field.'
						<input type="hidden" name="option" value="mo_customer_validation_sms_configuration" />
						<tbody>
						<tr id="gateway">
							<td>
								<h2>
									'.$sms_gateway_title.'
									<span style="float:right;margin-top:-10px;">
										<input 	type="submit" 
												name="submit" '.$disabled.'
												value="'.$gateway_submit_button_txt.'"
												class="button button-primary button-large" />
									</span>
								</h2>
								<hr>
							</td>
						</tr>

						<tr>
							<td>
					
								<b>'.mo_("Select Gateway Request type").':</b>
								<div class="mo-select-wrapper">
									<select id="custom_gateway_type" '.$disabled.' name="mo_customer_validation_custom_gateway_type">'.
										$gateway_list.
									'</select>									
								</div>
								<br>
							</td>
						</tr>
						<tr>
							<td id="gateway_configuration_fields">'
							.$gateway_config_view.
							'</td>
						</tr>
					</table>
				</form>
			
			
				<table class="" style="width: 100%;">
						<tr>
						    <td><br>
						       <b>'.$test_configuration_title .':</b><br><br>
						       <div class="">
						          <label>Phone Number:</label>
						           <input type="text" id="mo_test_configuration_phone" name="mo_test_configuration_phone"  placeholder="Enter Phone Number">
						           <span style="float:right;margin-top:-10px;">
										<input 	type="submit" 
												name="mo_gateway_submit" '.$disabled.'
												id="gateway_submit"
												value="'.$test_configuration_submit_button_txt.'"
												class="button button-primary button-large" />
									</span>
                                      
						        </div><br>
						        <div name="mo_test_config_hide_response" id="test_config_hide_response" style="display: none;" >
				            
						         <b>'.$test_configuration_response.':</b><br><br>
								<div>					
									 <textarea readonly'.$disabled.' id="test_config_response" 
									            class="mo_registration_table_textbox" 
												name="mo_test_configuration_response" 
												rows="3" style="height:120px;" placeholder="Your Gateway Response"required;
									           >
                                    </textarea>
									 <br>
									 
								
								</div>
				</div>

						        <br>
						        </td>
						 </tr>

						</tbody>
				</table>
				</div>
				
						   
			</div>
		</div>';
	$request_uri    = remove_query_arg(['addon','form','subpage'],$_SERVER['REQUEST_URI']);
    $license_url    = add_query_arg( array('page' => 'pricing'), $request_uri );

	$html   =           '<div class="mo_registration_table_layout mo-otp-half" style="margin-left:0.1em;width:750px">
		        	
				     <table class="mo_registration_settings_table" style="width: 100%;">
						'.$nonce_field.'
						<input type="hidden" name="option" value="mo_customer_validation_sms_configuration" />
						<tbody>
						<tr id="gateway">
							<td>
								<h2>
									'.mo_("SMS BACKUP GATEWAY CONFIGURATION").'
									
								</h2>
								<hr>

							</td>
						</tr>
						     </table>
						     <tr>
						     <td>
						       <div class="generated-otp-type-card mo-plan-ui">
								<div class="mo_premium_option_text">
									<span style="color:red;">*</span>
										This is a Enterprise Plan feature. Check <a href="'.$license_url.'">Licensing Tab</a> to learn more.
											</a>
								</div>
								</div>
							   </td>
							 </tr>';

	$html = apply_filters('mo_smsbackupgateway_card__ui', $html,$disabled);
	echo $html;


    echo '  
 
		<script>
			jQuery("#customemaileditor").prop("required",true);
			jQuery("#custom_gateway_type").val("'.$active_gateway.'");
            jQuery("#gateway_submit");
		</script>';