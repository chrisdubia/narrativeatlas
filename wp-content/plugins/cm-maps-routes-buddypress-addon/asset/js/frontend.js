jQuery(function($) {
	$('.cmmrm-buddypress-show-map-btn').click(function(ev) {
		var btn = $(this);
		var wrapper = btn.parents('.cmmrm-buddypress-profile-user-maps');
		wrapper.find('.cmmrm-map-shortcode').slideToggle('fast');
		btn.hide();
		var widget = $('#item-body .cmmrm-route-map-canvas')[0].cmmrm;
		var googleMap = widget.map.map;
		google.maps.event.trigger(googleMap, "resize");
		widget.map.center();
	});
	$('.cmmrm-buddypress-manage-btn').click(function(ev) {
		$('.cmmrm-buddypress-manage-routes').slideToggle('fast');
	});
});