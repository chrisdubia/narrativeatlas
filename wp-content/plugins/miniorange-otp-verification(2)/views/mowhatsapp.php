<?php

echo'	<div class="mo_registration_divided_layout mo-otp-full">
				<div id="whatsappTable" class="mo_registration_table_layout mo-otp-center">
				    <table style="width:100%">
						<form name="f" method="post" action="" id="mo_whatsapp_settings">
							<tr>
								<td>
									<h2>'.mo_("WHATSAPP FOR OTP VERIFICATION AND NOTIFICATIONS").'</h2>
									<hr>
								</td>
							</tr>
							<tr>
								<td class="mo_otp_note">'.mo_("This feature allows you to configure WhatsApp for OTP Verification as well as sending notifications and alerts via WhatsApp.").'
                                </td>
							</tr>
							<tr>
							 <td style="padding-left:3%">
							 <ul style="list-style:circle;">
							 <li> This is a monthly subscription module with unlimited sms over WhatsApp.</li>
							 <li> An activation code will be provided to you, this code needs to be used for activating your WhatsApp instance. </li>
							 <li> Instant Notifications and OTP codes sent via WhatsApp.</li>
							 <li> No Coding required, easy and seamless set up process.</li>
							 </ul>
							 </td>
							</tr>
							<tr>
							<td style="text-align: center;"><img class = "mo_whatsapp_steps" src="'.MOV_URL.'includes/images/mo_whatsapp_steps.png">
							</td>
							</tr>
							 <td><b>'.mo_('Please reach out to us for enabling WhatsApp on your wordpress site : <a style="cursor:pointer;" onClick="otpSupportOnClick(\'Hi! I am interested in using WhatsApp for my website, can you please help me with more information?\');"><u>Contact for WhatsApp</u></a>').'
                                </b>
                             </td>
						</form>	
					</table>
				</div>
			</div>';