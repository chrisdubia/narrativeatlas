jQuery(function($) {
	
	$('.cmmrm-custom-fields-settings .cmmrmcf-add-new').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		
		var btn = $(this);
		var wrapper = btn.parents('.cmmrm-custom-fields-settings');
		var tableBody = wrapper.find('tbody');
		
		var template = tableBody.find('tr').first().clone(true, true);
		template.find('.col-meta-key input').val('');
		template.find('.col-label input').val('');
		
		tableBody.append(template);
		
	});
	
	
	$('.cmmrm-custom-fields-settings .cmmrmcf-delete').click(function(ev) {
		if (confirm('Are you sure?')) {
			$(this).parents('tr').first().remove();
		}
	});
	
});