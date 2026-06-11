jQuery(document).ready( function(){
    $mo =jQuery;


// $mo(window).resize(function() {
//     // Check if the window width is less than 768px
//     if ($mo(window).width() < 768) {
//       // Remove the min-height property from the element
//       $mo('.element').css('min-height', '');
//     }
//   });

if($mo("#send-invite-form").length > 0){
    setSMSInviteSettings();
    sendSMSInviteButtonClicked();
    showUserGroups();
    showDropdown();

    //adding loader
    jQuery("body").append('<div id="loading" style="display:none;position: fixed;height: 100%;width: 100%;background: lightgray;opacity: 0.9;top: 0%;z-index: 999;"><center><img id="loading-image" style="display:none;z-index: 999;overflow: visible;top: 50%;position: absolute;" src="'+movarlogin.imgURL+'" alt="Loading..." /></center></div>');

    jQuery(window).load(function() {
        $mo('#loading').hide();
      });

    $mo("#mo-invitee-name").keyup(function(){
        if($mo("#mo-invitee-name").val().length > 1)
        $mo("#mo-invitee-phone").val("");
    });

    $mo("#mo-invitee-phone").keyup(function(){
        if($mo("#mo-invitee-phone").val().length > 1)
        $mo("#mo-invitee-name").val("");
    });
}

if($mo("#signup-form").length > 0){
    putPhoneNumberIfExists();
}

if(!$mo("form#loginform").length > 0) return;

    //html button for default form
    var htmlButton = '<p>'+
                        '<input type="submit"'+
                                'name="wp-submit"'+
                                'id="wp-submit"'+
                                'class="button button-primary button-large"'+
                                'style="width:48%;float:right;" '+
                                'value="'+movarlogin.buttontext+'">'+
                     '</p>';
    // html button for woocommerce form
    var htmlButton2 =   '<button  type="submit" '+
                            'class="woocommerce-Button button" '+
                            'style="width:60%;float:right;padding: 0.75em 2em;" '+
                            'name="login" '+
                            'value="'+movarlogin.buttontext+'">'+
                            movarlogin.buttontext+
                        '</button>';

    // html button for ultimate member form
    var htmlButton3 =   '<div class="um-right um-half">'+
                            '<input type="submit" name="logintype" value="'+movarlogin.buttontext+'" class="um-button um-alt">'+
                        '</div>'+
                        '<div class="um-clear"></div>';






//CSS
// var x = window.matchMedia("(min-width: 700px)")
$mo(window).resize(function() {
    if (window.innerWidth < 992) {
        // Add CSS rules here
        $mo('div#login').css({
            'margin-top':'10%', 'left': '50%','transform': 'translateX(-50%)','margin-right':'', 'margin-left':''   // Add more CSS properties as needed
        });
        $mo(".privacy-policy-page-link").css({'margin':'2em 0'});
    }
    else
    {
         $mo('div#login').css({
            'margin-top':'2%', 'left': '','transform': '','margin-right':'15%', 'margin-left':'15%'   // Add more CSS properties as needed
        });
        $mo(".privacy-policy-page-link").css({'margin':'1em 0'});


    }
});


if(window.innerWidth > 992)
{
    classelem = "margin-top:2%;margin-left:15%;min-height:0%; margin-right:15%";
            $mo(".privacy-policy-page-link").css({'margin':'1em 0'});
}
else
{
    classelem = "margin-top:10%; left:50%; transform:translateX(-50%)";
        $mo(".privacy-policy-page-link").css({'margin':'2em 0'});
}



//changing login form
$mo("body.login.js.login-action-login").css("background-color","#efe7e77a");
$mo("div#login").attr("style","background-color:#ffffff;border-radius:20px;box-shadow: 0px 3px 6px #00000029;padding: 10px;width:auto;position: absolute;"+classelem);
// $mo('div#login').css('min-height', '');
// $mo("body.login.js.login-action-login").css("background-color","#efe7e77a");
// $mo("div#login").attr("style","background-color:#ffffff;border-radius:20px;box-shadow: 0px 3px 6px #00000029;position: absolute;"+classelem);
// Create the CSS rule
// var css = '@media (max-width: 544px) { \
//             body.login.login-split-page #login { \
//                 width: 100%; \
//             } \
//         }';

// Create a style element with the CSS rule
// var style = jQuery('<style>' + css + '</style>');

// Append the style element to the document head
// jQuery('head').append(style);
  // $(window).resize(function() {
  //   // Check if the window width is less than 768px
  //   if (jQuery(window).width() < 768) {
  //     // Remove the min-height property from the element
  //     $('.element').css('min-height', '');
  //   }
  // }
//changing placeholder
setTimeout(function(){
divFooterElems = "<div id='mo_footer_div'></div>";
$mo(divFooterElems).insertAfter("p.submit");

$mo("input#user_login").attr("placeholder","Email or Phone Number");
$mo("body.login p.forgetmenot label[for='ltos_terms_agree'] a").css({"color":"#999999","font-size":"13px"});
$mo("body.login p.forgetmenot label[for='ltos_terms_agree'] a").text("I agree to the privacy policy");
$mo("body.login p.forgetmenot label[for='rememberme']").css({"color":"#999999","font-size":"13px"});

//changing places for forgetme and passreset
 var $forgetMeNot = jQuery( '.login.bb-login p.forgetmenot' );
 var $lostMeNot = jQuery( '.login.bb-login p.lostmenot' );
 jQuery( '.login.bb-login p.lostmenot' ).css({"margin-left":"36%","float":"none","font-size":"13px"});
 $mo("#mo_footer_div").append($lostMeNot);
 $mo("#mo_footer_div").append("<br>");
 $mo("#mo_footer_div").append($forgetMeNot);
 // $mo($lostMeNot).insertAfter("p.submit");

//remove loginerror
$mo("#login_error").remove();

//remove privacy link
$mo(".privacy-policy-page-link").empty();
},1000)


//change title
$mo("form#loginform").parents("div#login").find("div.login-heading").css("display","block")
$mo("form#loginform").parents("div#login").find("div.login-heading").
                html("<h2>Welcome Back</h2></br><h9 style='color:grey'>Login with your Email and Password</h9>");

//removing heading
$mo("form#loginform").parents("div#login").find("div.login-heading").remove();


//Adding new button
orDiv = "<div id='orDiv'><label id ='mo_disclaimer' style='margin-left: 0%;display: none;font-size: 7.2px;color:#999999;font-size: 7.2px;' hidden>"+
"We will send a text or email with a verification code. Message and data rates may apply.</label>"+
"<label style='margin-left: 48%;font-size: 10px;color:#999999'>or</label></div>";
isEmailorPhone = "<input type='hidden' name='isPhoneOnly' id='isPhoneOnly' value='email'/>;";
$mo("#wp-submit").css("background-color","#15689E");
$mo("#wp-submit").css("border-radius","10px");
$mo("#wp-submit").css("border-color","#F0F2F5");
$mo("#wp-submit").val("Login");
phoneOTPButton = $mo("#wp-submit").clone().prop("id","mo_send_otp_button");
$mo(phoneOTPButton).prop("type","button");
$mo(phoneOTPButton).prop("name","mo_send_otp_button");
$mo(phoneOTPButton).val("Login with One-Time Password");
$mo(phoneOTPButton).insertAfter("#wp-submit");
$mo(orDiv).insertBefore("#mo_send_otp_button");
$mo(isEmailorPhone).insertBefore("#mo_send_otp_button");
$mo("#mo_send_otp_button").css("background-color","#999999");
$mo("#mo_send_otp_button").css("border-color","#F0F2F5");

//changing button margin
$mo("#wp-submit").css("margin","0px");
$mo("#mo_send_otp_button").css("margin","0px");


//On loginwithphoneclick
$mo("#mo_send_otp_button").click(function(){
            passSelector = '#loginform label[for="user_pass"]';
        if($mo("#isPhoneOnly").val()=="email"){
            $mo(passSelector).parent().hide();
            $mo("#isPhoneOnly").val("phone");
            $mo(phoneOTPButton).val("Login with Password");
            $mo("#user_login").attr("placeholder","Email or Phone Number");
            $mo("input#user_pass").val("**********");
            $mo("#wp-submit").val("Continue");
            $mo("#mo_disclaimer").show();
        }
        else if($mo("#isPhoneOnly").val()=="phone"){
            $mo("#mo_disclaimer").hide();
            $mo(passSelector).parent().show();
            $mo("#wp-submit").val("Login")
            $mo(phoneOTPButton).val("Login with One-Time Password");
            $mo("#user_login").prop("placeholder","Email or Phone Number");
            $mo("input#user_pass").val("");
            $mo("#isPhoneOnly").val("email");
        }
})

//showPasswordFeild
$mo(".user-pass-wrap").css("display","block");
   

$mo("input#user_login").css("border-radius","10px");
$mo("input#user_pass").css("border-radius","10px");


    // if userLabel option has been set then change the label of the username field
    if(movarlogin.userLabel)
    {
        
    }
    // if password skip has been set in the setting and the fall back option is not enabled
    // hide the password field and then just show the username field
    if(movarlogin.skipPwdCheck && !movarlogin.skipPwdFallback)
    {      

    }
    // if password skip is set in the settings and fallback option is set as well.
    // need to show both username+password+login with OTP option
    else if (movarlogin.skipPwdCheck)
    {
       
    }
});

function moIsAdminCheck(userSelector,passSelector,btnSelector,loginBtnText){
    var e = $mo(userSelector).val();
    $mo.ajax({
        url: mologin.siteURL,
        type: "POST",
        data: {
            username: e,
            action:movarlogin.isAdminAction,
        },
        crossDomain: !0, dataType: "json",
        success: function (o) {
            if (o.result === "success") {
                $mo(passSelector).parent().show(); 
                $mo("#user_pass").prop( "disabled", false );  
                $mo(btnSelector).val(loginBtnText);                        
            } else {
                $mo(passSelector).parent().hide();
                $mo(btnSelector).val(movarlogin.buttontext);
            }
        },
        error: function (o) {}
    });
}


function setSMSInviteSettings(){
    // inviteTemplate = $mo("#member-invites-table").clone().prop("id","mo_send_sms_invite");
    // $mo(inviteTemplate).insertAfter("#member-invites-table");
    // $mo("#mo_send_sms_invite").find("tr:first").css("background-color","#00000000")


    // $mo("#mo_send_sms_invite").find("thead").children("tr:first").children("th").each(function(){
    //     if($mo(this).text() == "Recipient Email")
    //         $mo(this).text("Recipient Phone");
    // })
    // $mo("#mo_send_sms_invite").find(".field-actions-remove").hide();
    // $mo("#mo_send_sms_invite").find("tbody").children("tr:nth-child(2)").hide();

$mo("#send-invite-form").each(function(){
    addIconStyle = "vertical-align: middle;display: inline-block;background-color: #007cff;color: #fff;border-radius: 100%;padding: 5px;height: auto;width: auto;line-height: 1;font-size: 20px;"
html = '<div><br><label for="bp-member-invites-custom-content">For Bulk Email or Phone, add a comma seperated list up to 50 users. Select your group(s) below. Press Send Bulk Invites when done.</label><table class="invite-settings bp-tables-user member-invites-table-sms" id="member-invites-table"><thead><tr>'+
'<th class="title">Bulk Email</th><th class="title">Bulk Phone</th>'+
'<th class="title actions"></th></tr></thead><tbody><tr>'+
'<td class="field-name"><textarea type="text" name="mo-invitee-name[]" id="mo-invitee-name" value="" class="invites-input"></textarea></td>'+
'<td class="field-email"><textarea type="number" name="mo-invitee-phone[]" id="mo-invitee-phone" value="" class="invites-input"></textarea></td>'+
'<td class="field-actions"><span class="field-actions-remove"></span></td>'+
'</tr><tr><td class="field-name" colspan="2"><input type="button" id="mo-send-sms-invites-submit-btn" style="float: right;background-color:#0069aa !important" value="Send Bulk Invites"></input></td><td class="field-actions-last" colspan=""><span class="">'+
// '<i id="mo-add-icon" class="bb-icon bb-icon-plus" style="'+addIconStyle+'"></i></span>'+                
'</td></tr></tbody></table>'+
'<div id="mo-send-sms-invites-submit" style="display:list-item;float: right;border: 1px solid #e7eaec;width: -webkit-fill-available;">'+
'<div><span id="mo-send-sms-invites-message" style="float:left;display:none;color:#e98080">Please fill invitee details properly</span>'+
'</div>'+
'</div></div>'+
'<br><hr>';

        // html = "<div id='mo-send-sms-invites'><br><div id='mo-send-sms-invites-container'></div></div>";
        $mo(html).insertAfter("#member-invites-table");
        addButtonClicked();
    })


}

function addButtonClicked(){
    count = 0;
    $mo("#mo-add-icon").on('click',function(){
        count++;
        newtr = '<tr>'+
            '<td class="field-name"><input type="text" name="mo-invitee-name[]" id="mo-invitee-name" value="" class="invites-input"></td>'+
            '<td class="field-email"><input type="number" name="mo-invitee-phone[]" id="mo-invitee-phone" value="" class="invites-input"></td>'+
            '<td class="field-actions"><span class="field-actions-remove"><i class="bb-icon bb-icon-close"></i></span></td>'+
            '</tr>';
                // $mo('.member-invites-table-sms > tbody:nth-last-child(even)').append(newtr);
        $mo(newtr).insertAfter($mo('.member-invites-table-sms>tbody>tr:nth-last-child(2)'));
        // return false;
    })
}

function sendSMSInviteButtonClicked(){
    $mo("#mo-send-sms-invites-submit-btn").on('click',function(){

        if(!setValidationForPhoneAndEmail()) return false;

        if($mo('textarea[name="mo-invitee-name[]"]').val() != "")
            var users = $mo('textarea[name="mo-invitee-name[]"]').map(function(){ 
                        return this.value; 
                    }).get();

        if($mo('textarea[name="mo-invitee-phone[]"]').val() != "")
            var phonenumber = $mo('textarea[name="mo-invitee-phone[]"]').map(function(){ 
                        return this.value; 
                    }).get();
        
        $mo("#mo-send-sms-invites-message").hide();

        if(!$mo("#mo-invitee-name").val()=="" || !$mo("#mo-invitee-phone").val()=="")
        {
            if(!checkUserGroupSelected()) return false;

            var userGroups = $mo('.mo-groups-selected').attr("mo_invitee_group");

            $mo("#loading-image,#loading").show(); 
            $mo.ajax({
            url: movarlogin.siteURL,
            type: "POST",
            data: {
                user_name: users,
                user_phone: phonenumber,
                mo_invitee_group: userGroups,
                security:movarlogin.gnonce,
                action:"mo-send-sms-invites",
            },
            crossDomain: !0, dataType: "json",
            success: function (o) {
                if (o == "email_sent") {
                    $mo("#loading-image,#loading").hide(); 
                    window.location = movarlogin.sentEmailPage;
                } else {
                    // if otp wasn't sent successfully
                    $mo("#mo_message"+ otpType + formId).empty();
                    $mo("#mo_message"+ otpType + formId).append(o.message);
                    $mo("#mo_message"+ otpType + formId).css("border-top", "3px solid red");
                }
            },
            error: function (o) {}
        });

        }
        else
        $mo("#mo-send-sms-invites-message").show();
    })
}

function showUserGroups(){

    $mo("#send-invite-form").each(function(){
content = '';
html = '<div><input type="text" name="mo_invitee_group" id="orig-invite-send" style="display:none" hidden="hidden" value=""/><label for="bp-member-invites-custom-content">Select the groups for invitee. A link to register will be sent with these groups. You can select multiple values from the dropdown and delete all of them at the same time,by clicking on the \'x\' button.</label><div class="multiselect" id="countries" multiple="multiple" data-target="multi-0"><div class="mo-groups-selected title noselect" mo_invitee_group=""><span class="text">My Groups</span><span class="close-icon">&times;</span><span class="expand-icon">&plus;</span></div><div class="container">'+
'';

if(movarlogin.userGroups == null) return false;
movarlogin.userGroups.forEach(function(value,index){
    id = value.split("::")[1];
    name = value.split("::")[0];
    content += '<option group_name = "'+name+'" name="mo_invitee_group[]" id="mo_invitee_group" value="'+value+'" groupId="'+id+'" class="invites-input">'+name+'</option>';
    
    });

html = html + content;
html += '</div></div><div style="margin-top: 10px;color:#A3A5A9"></div></div><br><br>';
        // html = "<div id='mo-send-sms-invites'><br><div id='mo-send-sms-invites-container'></div></div>";
        $mo(html).insertAfter("#wp-bp-member-invites-custom-content-wrap");
        // addButtonClicked();
    })
}


function putPhoneNumberIfExists(){
 setTimeout(function(){
    email = $mo("#signup_email").val();
        if(email.includes(movarlogin.tempemaildomain))
            $mo("input[type='tel']").val(email.split("@")[0]);
    },300);
}

function setValidationForPhoneAndEmail(){
    var email = $mo("#mo-invitee-name").val();
    var emailcount = (email.match(/,/g) || []).length;

    var phone = $mo("#mo-invitee-phone").val();
    var phonecount = (phone.match(/,/g) || []).length;
    if(emailcount > 51 || phonecount > 51){
        $mo("#mo-send-sms-invites-message").empty()
        $mo("#mo-send-sms-invites-message").text("You can only send the invite to 50 users at a time")
        $mo("#mo-send-sms-invites-message").show()
        return false;
    }
    return true;
}

function checkUserGroupSelected(){
     
        if(!$mo('.mo-groups-selected')[0].hasAttribute("title")) {
            $mo("#mo-send-sms-invites-message").empty()
            $mo("#mo-send-sms-invites-message").text("Please select the groups for the invitee.")
            $mo("#mo-send-sms-invites-message").show()
            return false;
        }
        else if($mo('.mo-groups-selected').attr("title") == "My Groups"){
            $mo("#mo-send-sms-invites-message").empty()
            $mo("#mo-send-sms-invites-message").text("Please select the groups for the invitee.")
            $mo("#mo-send-sms-invites-message").show()
            return false;
        }
        else return true;
     return true;
}

function showDropdown(){
    html = '';
style = '<style>body{font-family:sans-serif}.noselect{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.multiselect{width:inherit;font-size:15px;padding-bottom:4px;border-radius:3px;border:1px solid rgba(0,0,0,.1);transition:0.2s;outline:none}.multiselect:hover{border:1px solid rgba(0,0,0,.3)}.multiselect.active{border-bottom-left-radius:0;border-bottom-right-radius:0;border-bottom:1px solid transparent}.multiselect>.title{cursor:pointer;height:35px;padding:6px}.multiselect>.title>.text{max-width:max-content;max-height:25px;display:block;float:left;overflow:hidden;line-height:1.3em}.multiselect>.title>.expand-icon,.multiselect>.title>.close-icon{float:right;border-radius:50%;padding:0 4px;border:1px solid rgba(0,0,0,.1);font-weight:700;transition:0.2s;display:none}.multiselect.selection>.title>.expand-icon{display:none}.multiselect>.title>.expand-icon,.multiselect.selection>.title>.close-icon{display:block}.multiselect>.title>.close-icon:hover{border:1px solid rgba(0,0,0,.3);background:rgb(203,32,32);color:#fff}.multiselect>.container{max-height:200px;overflow:auto;margin-top:4px;margin-left:-1px;width:66.75%;transition:0.2s;position:absolute;z-index:99;background:#fff;border:1px solid transparent;border-top:1px solid rgba(0,0,0,.1)}.multiselect.active>.container{border:1px solid rgba(0,0,0,.1);border-bottom-left-radius:3px;border-bottom-right-radius:3px;border-top:0}.multiselect:hover>.container{border-top-color:rgba(0,0,0,.3)}.multiselect.active:hover>.container{border-color:rgba(0,0,0,.3)}.multiselect>.container>option{display:none;padding:5px;cursor:pointer;transition:0.2s;border-top:1px solid transparent;border-bottom:1px solid transparent}.multiselect>.container>option.selected{background:rgb(122,175,233);border-top:1px solid rgba(0,0,0,.1);border-bottom:1px solid rgba(0,0,0,.1);color:#fff}.multiselect>.container>option:hover{background:rgba(0,0,0,.1);color:#000}.multiselect.active>.container>option{display:block}</style>';
html = style + html;
$mo(html).insertBefore("#wp-bp-member-invites-custom-content-wrap");
Array.prototype.search = function(elem) {
    for(var i = 0; i < this.length; i++) {
        if(this[i] == elem) return i;
    }
    
    return -1;
};

var Multiselect = function(selector) {
    if(!$mo(selector)) {
        console.error("ERROR: Element %s does not exist.", selector);
        return;
    }

    this.selector = selector;
    this.selections = [];
    this.valuewithid = [];

    (function(that) {
        that.events();
    })(this);
};

Multiselect.prototype = {
    open: function(that) {
        var target = $mo(that).parent().attr("data-target");

        // If we are not keeping track of this one's entries, then
        // start doing so.
        if(!this.selections) {
            this.selections = [ ];
        }
         if(!this.valuewithid) {
            this.valuewithid = [ ];
        }

        $mo(this.selector + ".multiselect").toggleClass("active");
    },

    close: function() {
        $mo(this.selector + ".multiselect").removeClass("active");
    },

    events: function() {
        var that = this;

        $mo(document).on("click", that.selector + ".multiselect > .title", function(e) {
            if(e.target.className.indexOf("close-icon") < 0) {
                that.open();
            }
        });

        $mo(document).on("click", that.selector + ".multiselect option", function(e) {
            var selection = $mo(this).attr("group_name");
            var valuewithidvalue = $mo(this).attr("value");
            var target = $mo(this).parent().parent().attr("data-target");

            var io = that.selections.search(selection);

            if(io < 0) that.selections.push(selection);
            else that.selections.splice(io, 1);

            var iovalue = that.valuewithid.search(valuewithidvalue);

            if(iovalue < 0) that.valuewithid.push(valuewithidvalue);
            else that.valuewithid.splice(iovalue, 1);

            that.selectionStatus();
            that.setSelectionsString();
        });

        $mo(document).on("click", that.selector + ".multiselect > .title > .close-icon", function(e) {
            that.clearSelections();
        });

        $mo(document).click(function(e) {
            if(e.target.className.indexOf("title") < 0) {
                if(e.target.className.indexOf("text") < 0) {
                    if(e.target.className.indexOf("-icon") < 0) {
                        if(e.target.className.indexOf("selected") < 0 ||
                           e.target.localName != "option") {
                            that.close();
                        }
                    }
                }
            }
        });
    },

    selectionStatus: function() {
        var obj = $mo(this.selector + ".multiselect");

        if(this.selections.length) obj.addClass("selection");
        else obj.removeClass("selection");
    },

    clearSelections: function() {
        this.selections = [];
        this.selectionStatus();
        this.setSelectionsString();
    },

    getSelections: function() {
        return this.selections;
    },

    getvaluewithId: function() {
        return this.valuewithid;
    },
    setSelectionsString: function() {
        var selects = this.getSelectionsString().split(", ");
        var valueid = this.getvaluewithIdString().split(", ");
        $mo(this.selector + ".multiselect > .title").attr("title", selects);
        $mo(this.selector + ".multiselect > .title").attr("mo_invitee_group", valueid);
        $mo("#orig-invite-send").attr("value",valueid);

        var opts = $mo(this.selector + ".multiselect option");

        if(selects.length > 6) {
            var _selects = this.getSelectionsString().split(", ");
            _selects = _selects.splice(0, 6);
            $mo(this.selector + ".multiselect > .title > .text")
                .text(_selects + " [...]");
        }
        else {
            $mo(this.selector + ".multiselect > .title > .text")
                .text(selects);
        }

        for(var i = 0; i < opts.length; i++) {
            $mo(opts[i]).removeClass("selected");
        }

        for(var j = 0; j < selects.length; j++) {
            var select = selects[j];

            for(var i = 0; i < opts.length; i++) {
                if($mo(opts[i]).attr("value") == select) {
                    $mo(opts[i]).addClass("selected");
                    break;
                }
            }
        }
    },

    getSelectionsString: function() {
        if(this.selections.length > 0)
            return this.selections.join(", ");
        else return "My Groups";
    },
    getvaluewithIdString:function(){
        if(this.valuewithid.length > 0)
            return this.valuewithid.join(", ");
        else return "";
    },
    setSelections: function(arr) {
        if(!arr[0]) {
            error("ERROR: This does not look like an array.");
            return;
        }

        this.selections = arr;
        this.selectionStatus();
        this.setSelectionsString();
    },
};

$mo(document).ready(function() {
    var multi = new Multiselect("#countries");
});

}