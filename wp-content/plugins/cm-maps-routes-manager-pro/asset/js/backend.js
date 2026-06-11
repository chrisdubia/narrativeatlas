jQuery(function($) {
	
	// Settings tabs handler
	$('.cmmrm-settings-tabs a').click(function() {
		var match = this.href.match(/\#tab\-([^\#]+)$/);
		$('#settings .settings-category.current').removeClass('current');
		$('#settings .settings-category-'+ match[1]).addClass('current');
		$('.cmmrm-settings-tabs a.current').removeClass('current');
		$('.cmmrm-settings-tabs a[href="#tab-'+ match[1] +'"]').addClass('current');
		this.blur();
	});
	if (location.hash.length > 0) {
		$('.cmmrm-settings-tabs a[href="'+ location.hash +'"]').click();
	} else {
		$('.cmmrm-settings-tabs li:first-child a').click();
	}
	
	// Access custom cap handler
	var settingsAccessCustomCapListener = function() {
		var obj = $(this);
		var nextField = obj.parents('tr').first().next();
		if ('cmmrm_capability' == obj.val()) {
			nextField.show();
		} else {
			nextField.hide();
		}
	};
	$('select[name^=cmmrm_access_map_]').change(settingsAccessCustomCapListener);
	$('select[name^=cmmrm_access_map_]').change();
	
	$('#cmmrm-import-route-form').submit(function() {
		var form = $(this);
		var btn = form.find('input[type=submit]');
		//btn.hide();
		$('#cmmrm-import-frame').show();
	});

	$('#cmmrm-import-route-form-csv').submit(function() {
		var form = $(this);
		var btn = form.find('input[type=submit]');
		//btn.hide();
		$('#cmmrm-import-frame-csv').show();
	});
	
	$('.cmmrm-admin-notice .cmmrm-dismiss').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		var btn = $(this);
		var data = {action: btn.data('action'), nonce: btn.data('nonce'), id: btn.data('id')};
		$.post(btn.attr('href'), data, function(response) {
			btn.parents('.cmmrm-admin-notice').fadeOut('slow');
		});
	});
	
	// Custom taxonomies
	var deleteTaxHandler = function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		$(this).parents('.cmmrm-custom-tax-item').first().remove();
	};
	$('.cmmrm-custom-tax-setting .cmmmrm-custom-tax-delete a').click(deleteTaxHandler);
	$('.cmmrm-custom-tax-add-btn').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		var btn = $(this);
		var wrapper = btn.parents('.cmmrm-custom-tax-setting');
		var template = wrapper.data('template');
		btn.before(template);
		var item = wrapper.find('.cmmrm-custom-tax-item').last();
		item.find('.cmmrm-custom-tax-taxonomy').val('');
		item.find('.cmmrm-custom-tax-name-singular').val('');
		item.find('.cmmrm-custom-tax-name-plural').val('');
		item.find('.cmmmrm-custom-tax-delete a').click(deleteTaxHandler);
	});
	
	$('.cmmrm-embed-shortcode textarea').click(function() {
		this.select();
	});
	
	$('.cmmrm_category_icon_choose').click(function() {
		var btn = $(this);
		btn.parents('.cmmrm_category_icon').find('.cmmrm_category_icon_list').show();
		$('.cmmrm_category_icon_list img').css('cursor', 'pointer');
	});
	
	$('.cmmrm_category_icon_list img').click(function() {
		var obj = $(this);
		obj.parents('.cmmrm_category_icon').find('.cmmrm_category_icon_list').hide();
		obj.parents('.cmmrm_category_icon').find('.cmmrm_category_icon_image').attr('src', obj.attr('src'));
		obj.parents('.cmmrm_category_icon').find('input[name=cmmrm_category_icon]').val(obj.attr('src'));
	});

	var deleteTileHandler = function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		$(this).parents('.cmmrm-map-tile-item').first().remove();
	};
	$('.cmmrm-map-tile-setting .cmmrm-map-tile-delete a').click(deleteTileHandler);
	$('.cmmrm-map-tile-add-btn').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		if($('.cmmrm-map-tile-setting div.cmmrm-map-tile-item').length < 6) {
			var btn = $(this);
			var wrapper = btn.parents('.cmmrm-map-tile-setting');
			var template = wrapper.data('template');
			btn.before(template);
			var item = wrapper.find('.cmmrm-map-tile-item').last();
			item.find('.cmmrm-map-tile-name').val('');
			item.find('.cmmrm-map-tile-url').val('');
			item.find('.cmmrm-map-tile-delete a').click(deleteTileHandler);
		}
	});
	
	$('body').on('change', '.cmmrm-map-tile-default', function ( e ) {
		if($(this).prop('checked')) {
			$('.cmmrm-map-tile-setting').find('.cmmrm-map-tile-default').prop('checked', false);
			$(this).prop('checked', true);
		} else {
			$(this).prop('checked', false);
		}
    });

});