CMMRM_CustomFields_Editor = {};



CMMRM_CustomFields_Editor.propagateValues = function(values) {
	
	var $ = jQuery;
	
	for (var id in values) {
		for (var meta_key in values[id]) {
			var value = values[id][meta_key];
			var selector = '#cmmrm-editor-locations .cmmrm-locations-list > li[data-id='+ id +'] .cmmrm-custom-field[data-custom-field="'+ meta_key +'"]';
//			console.log(selector);
//			console.log($(selector).length);
			$(selector + ' input, ' + selector +' textarea').val(value);
			
			var gradeScaleWrapper = $(selector + ' .cmmrm-custom-field-grade-scale');
			if (gradeScaleWrapper.length > 0) {
//				console.log(gradeScaleWrapper.length);
//				console.log(id, meta_key);
				var currentGrade = parseInt(value);
//				gradeScaleWrapper.attr('data-current-grade', currentGrade);
				CMMRM_CustomFields_Editor.applyGradeToValues(gradeScaleWrapper, currentGrade);
//				console.log(currentGrade);
				CMMRM_CustomFields_Editor.applyGradeToIcons(gradeScaleWrapper, currentGrade);
			}
			
		}
	}
		
	
};


CMMRM_CustomFields_Editor.applyGradeToIcons = function(wrapper, applyGrade) {
	var $ = jQuery;
	$('.cmmrm-custom-field-grade-scale-item', wrapper).each(function() {
		var item = $(this);
		var grade = parseInt(item.data('grade'));
//		console.log(grade, applyGrade);
		if (grade <= applyGrade) {
			item.addClass('cmmrm-marked');
		} else {
			item.removeClass('cmmrm-marked');
		}
	});
};


CMMRM_CustomFields_Editor.applyGradeToValues = function(wrapper, applyGrade) {
//	console.log('applyGradeToValues');
	if (!(applyGrade > 0)) applyGrade = 0;
	wrapper.attr('data-current-grade', applyGrade);
	jQuery('.cmmrm-custom-field-grade-scale-value span', wrapper).text(applyGrade);
	jQuery('input[type=hidden]', wrapper).val(applyGrade);
};


CMMRM_CustomFields_Editor.handleNewGradeElements = function(container) {
	var $ = jQuery;
	$('.cmmrm-custom-field-grade-scale', container).each(function() {
			
			var wrapper = $(this);
			var leaveTimer = null;
			
			var currentGrade = parseInt(wrapper.attr('data-current-grade'));
			CMMRM_CustomFields_Editor.applyGradeToIcons(wrapper, currentGrade);
			
			$('.cmmrm-custom-field-grade-scale-item', wrapper).mouseenter(function() {
	//			console.log('enter');
				clearTimeout(leaveTimer);
				var item = $(this);
				var grade = parseInt(item.data('grade'));
				CMMRM_CustomFields_Editor.applyGradeToIcons(wrapper, grade);
			});
			$('.cmmrm-custom-field-grade-scale-item', wrapper).mouseleave(function() {
				leaveTimer = setTimeout(function() {
	//				console.log('leave');
					var currentGrade = parseInt(wrapper.attr('data-current-grade'));
					CMMRM_CustomFields_Editor.applyGradeToIcons(wrapper, currentGrade);
				}, 100);
			});
			$('.cmmrm-custom-field-grade-scale-item', wrapper).click(function() {
	//			console.log('click');
				var item = $(this);
				var grade = parseInt(item.data('grade'));
//				wrapper.attr('data-current-grade', grade);
//				$('input[type=hidden]', wrapper).val(grade);
				CMMRM_CustomFields_Editor.applyGradeToValues(wrapper, grade);
				CMMRM_CustomFields_Editor.applyGradeToIcons(wrapper, grade);
			});
			
		});
};

jQuery(function($) {
	CMMRM_CustomFields_Editor.handleNewGradeElements(jQuery('.cmmrm-route-editor'));
	document.addEventListener('DOMNodeInserted', function(ev) {
//		console.log('DOMNodeInserted');
		CMMRM_CustomFields_Editor.handleNewGradeElements(ev.target);
	}, false);
});



