function CMMRM_Disclaimer_CreateCookie(name, value, days){
	var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "; expires=" + date.toGMTString();
    document.cookie = name + "=" + value + expires + "; path=/";
}
function CMMRM_Disclaimer_ReadCookie(name){
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++){
        var c = ca[i];
        while(c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if(c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}
function CMMRM_Disclaimer_CheckCookies(){
    if(CMMRM_Disclaimer_ReadCookie('cmmrm_disclaimer') != 'Y') {
        var message = cmmrm_disclaimer_opts.content;
        var message_container = document.createElement('div');
        message_container.id = 'cmmrm-disclaimer-message-container';
        var html_code = '';
		html_code += '<div id="disclaimer-message">';
		html_code += '<div id="disclaimer-message-inner">';
		html_code += message;
        html_code += '<br />';
        html_code += '<br />';
		html_code += '<a href="javascript:CMMRM_Disclaimer_CloseCookiesWindow();" id="accept-disclaimer-checkbox">'+cmmrm_disclaimer_opts.acceptText + '</a>';
        html_code += '<a href="javascript:CMMRM_Disclaimer_RejectCookiesWindow();" id="reject-disclaimer-checkbox">'+cmmrm_disclaimer_opts.rejectText+'</a>';
		html_code += '</div>';
		html_code += '</div>';
        message_container.innerHTML = html_code;
        document.body.appendChild(message_container);
        var elem = document.getElementById('disclaimer-message');
        elem.style.marginTop = '-' + (elem.offsetHeight / 2) + 'px';
    }
}
function CMMRM_Disclaimer_CloseCookiesWindow(){
	CMMRM_Disclaimer_CreateCookie('cmmrm_disclaimer', 'Y', 365);
    document.getElementById('cmmrm-disclaimer-message-container').removeChild(document.getElementById('disclaimer-message'));
    document.getElementById('cmmrm-disclaimer-message-container').parentNode.removeChild(document.getElementById('cmmrm-disclaimer-message-container'));
}
function CMMRM_Disclaimer_RejectCookiesWindow(){
    document.getElementById('cmmrm-disclaimer-message-container').removeChild(document.getElementById('disclaimer-message'));
    document.getElementById('cmmrm-disclaimer-message-container').parentNode.removeChild(document.getElementById('cmmrm-disclaimer-message-container'));
    window.location = cmmrm_disclaimer_opts.rejecturl;
}
window.onload = CMMRM_Disclaimer_CheckCookies;