(function($) {

window.CMMRMCF = {};
window.CMMRMCF.Utils = {
		
	addSingleHandler: function(handlerName, selector, action, func) {
		var obj;
		if (typeof selector == 'string') obj = $(selector);
		else obj = selector;
		obj.each(function() {
			var obj = $(this);
			if (obj.data(handlerName) != '1') {
				obj.data(handlerName, '1');
				obj.on(action, func);
			}
		});
	},
	
	leftClick: function(func) {
		return function(e) {
			// Allow to use middle-button to open thread in a new tab:
			if (e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey) return;
			func.apply(this, [e]);
			return false;
		}
	},
	
	
	toast: function(msg, className, duration) {
		if (typeof className == 'undefined') className = 'info';
		if (typeof duration == 'undefined') duration = 5;
		var toast = $('<div/>', {"class":"cmmrmcf-toast "+ className, "style":"display:none"});
		toast.html(msg);
		$('body').append(toast);
		toast.fadeIn(500, function() {
			setTimeout(function() {
				toast.fadeOut(500);
			}, duration*1000);
		});
	}
		
};


$('.cmmrmcf-delete-confirm').click(function(ev) {
	if (!confirm(CMMRMCF_Utils.deleteConfirmText)) {
		ev.stopPropagation();
		ev.preventDefault();
	}
});


})(jQuery);
	


