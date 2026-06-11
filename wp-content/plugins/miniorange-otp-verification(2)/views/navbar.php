<?php

echo'	<div class="wrap">
			<div><img style="float:left;" src="'.MOV_LOGO_URL.'"></div>
			<div class="otp-header">
				'.mo_("OTP Verification").'
				<a class="add-new-h2" id="accountButton" href="'.$profile_url.'">'.mo_("Account").'</a>
                <a class="add-new-h2" id="LicensingPlanButton" style="background-color:orange;color:black;" href="'.$license_url.'">'.mo_("Licensing Plans").'</a>
				<a class="add-new-h2" id="faqButton" href="'.$help_url.'" target="_blank">'.mo_("FAQs").'</a>';
        
		        
echo '          <a class="mo-otp-demo add-new-h2" onClick="otpSupportOnClick(\'Hi! I am interested in using your plugin and would like to get a demo of the features and functionality. Please schedule a demo for the plugin. \');" id="demoButton">'.mo_("Need a Demo?").'</a>
	            <div class="mo-otp-help-button static" style="z-index:10">


  <a class="mo-offer-button button-primary button-large" id="mo_otp_offers" style="background:red;color:white">
                    <span class="dashicons dashicons-money-alt" style="margin:4% 0 0 0;"></span>
                     '.mo_("<b>Monthly Plan</b>").'   
                    </a>';










echo '

                    <a id="show_prem_addons_button" class="button button-primary button-large" style="background:orange;color:black">
                        <span class="dashicons dashicons-admin-tools" style="margin:5% 0 0 0;"></span>
                            '.mo_("Premium Addons").'
                    </a>';
                    if($isLoggedIn && $isFreePlugin)
echo'
                     <a id="mo_check_transactions" class="button button-primary button-large">
                            <span class="dashicons dashicons-visibility" style="margin:5% 0 0 0;"></span>
                                '.mo_("View Transactions").'
                    </a>';
                    


echo '
                </div>
            </div>
		<form id="mo_check_transactions_form" style="display:none;" action="" method="post">';
            
            wp_nonce_field('mo_check_transactions_form','_nonce');
echo        '<input type="hidden" name="option" value="mo_check_transactions" />
        </form></div>';

echo'	<div id="tab">
			<h2 class="nav-tab-wrapper">';

        
        foreach ($tabDetails->_tabDetails as $tabs)
        {
            if($tabs->_showInNav) {
                echo '<a  class="nav-tab 
                        ' . ($active_tab === $tabs->_menuSlug ? 'nav-tab-active' : '') . '" 
                        href="' . $tabs->_url . '"
                        style="'. $tabs->_css .'"
                        id="' . $tabs->_id . '">
                        ' . $tabs->_tabName . '
                    </a>';
            }
        }

        echo '</h2>';

        if(!$registered) {
            echo '<div  style="background-color:rgba(255,5,0,0.29);font-size:0.9em;" 
                        class="notice notice-error">
                        <h2>' .$registerMsg.'</h2>
                  </div>';
        }else if(!$activated) {
            echo '<div  style="background-color:rgba(255,5,0,0.29);font-size:0.9em;" 
                        class="notice notice-error">
                        <h2>' .$activationMsg.'</h2>
                  </div>';
        }
        else if(!$gatewayconfigured) {
            echo '<div  style="background-color:rgba(255,5,0,0.29);font-size:0.9em;" 
                        class="notice notice-error">
                        <h2>' .$gatewayMsg.'</h2>
                  </div>';
        }