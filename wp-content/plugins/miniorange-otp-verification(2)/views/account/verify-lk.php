<?php
echo '<div class="mo_registration_divided_layout mo-otp-full">
			<div class="mo_registration_table_layout mo-otp-center">
			 <h3>
			 	'.mo_("Verify Your License").'
			 	<span style="float:right;margin-top:-10px;">
                    <a href="#goToLoginPage" class="button button-primary button-large">'.mo_("Go Back").'</a>
                </span>
			 </h3>
				<p>
					<div class="mo_registration_help_title">[ '.mo_("Where is my key?").']</a></div>
					<div hidden class="mo_registration_help_desc">
						'.mo_("You can find all your used and unused keys under the <i>View License Key</i> Section.").' 
							<a href="'.$url.'" target="_blank" >'.mo_("Click Here").'</a> '.mo_("to see your keys.").' 
					</div>
				</p>
								
				<form name="f" method="post" action="">';
                    wp_nonce_field($nonce);
echo'				<input type="hidden" name="option" value="mo_registration_verify_license" />
					<table class="mo_registration_settings_table">
						<tr>
							<td style="width:18%"><b><font color="#FF0000">*</font>License Key:</b></td>
							<td>
							    <input  class="mo_registration_table_textbox" 
							            required 
							            type="text"
								        name="email_lk" 
								        placeholder="'.mo_("Enter your license key to activate the plugin").'"/>
                            </td>
						</tr>
					</table>
					<br/>
					<div style="display:inline;">
						<b>Please read and accept the following before activating your license key :</b>
						<div class="mo_registration_help_desc" style="margin-left:15px;">
							<div style="float:left;height:50px;margin-right:5px;"><input type="checkbox" id="lk_check1" value="1"/></div>
							<div>
							    <b><i>'.
                                    mo_("License key you have entered here is associated with this site instance.").
                                '</i></b> '.
                                mo_("If you want to re-install the plugin for any reason, 
                                    you should deactivate and then delete the plugin from WordPress console. 
                                    Manually deleting the plugin folder will not free your key for re-use.").'
                            </div>
							<br/>
							<div style="float:left;height:50px;margin-right:5px;"><input type="checkbox" id="lk_check2" value="1"/></div>
							<div>
							    <b><i>'.mo_("This is not a developer license.</i></b> Making any kind of 
                                            change to the plugin\'s code will delete all your configuration and 
                                            make the plugin unusable.").
                            '</div>
						</div>
					</div>
					<br/>
					<input  type="submit" 
					        name="submit" 
					        disabled="true" 
					        id="activate_plugin" 
					        value="'.mo_("Activate License").'" 
					        class="button button-primary button-large" />
                    <br><br> 
				</form>
			</div>
		  </div>
		  <form id="goToLoginPageForm" method="post" action="">';
                wp_nonce_field($nonce);
echo'	    	<input type="hidden" name="option" value="mo_go_to_login_page" />
		  </form>
		  <script>
		    jQuery(document).ready(function(){	
				$mo(\'a[href="#goToLoginPage"]\').click(function(){
					$mo("#goToLoginPageForm").submit();
				});
			});
          </script>';