jQuery(function($) {

	var getArchiveFromResponse = function(response) {
		var html = $(response);
		return html.find('.cmmrm-routes-archive');
	};
	
	var initHandlers = function() {
		
		var wrapper = $(this);
		wrapper.trigger('CMMRM.initHandlers');
		//console.log('initHandlers', wrapper.get(0));
		
		var requestUrl = function(request, callback) {
			var loader = $('<div>', {'class': 'cmmrm-loader-big'});
			wrapper.addClass('cmmrm-loading');
			wrapper.append(loader);
			if (typeof request == 'string') {
				request = {url: request};
			}
			if (typeof request != 'object') request = {};
			request.success = function(response) {
				loader.remove();
				wrapper.removeClass('cmmrm-loading');
				callback(response);
			};
			$.ajax(request);
		};
		
		var replaceWrapperWithResponse = function(response) {
			var content = getArchiveFromResponse(response);
			wrapper.html(content.html());
			initHandlers.apply(wrapper);
		};
		
		var refreshMapFromResponse = function(response) {
			var html = $(response);
			var scriptTag = '<script type="text/javascript" data-cmmrm-script="index-map">';
			var scriptIndex = response.indexOf(scriptTag);
			if (scriptIndex > -1) {
				var scriptContentIndex = scriptIndex + scriptTag.length;
				var scriptContent = response.substr(scriptContentIndex, response.indexOf('</script>', scriptIndex) - scriptContentIndex);
				eval(scriptContent);
			}
		};
		
		/*
		$('.cmmrm-filter select', wrapper).change(function(ev) {
			ev.stopPropagation();
			var filter = $(this);
			var form = filter.parents('form').first();
			console.log('form', form.length);
			var url = filter.val() + '&' + form.serialize();
			console.log(url);
			requestUrl(url, function(response) {
				replaceWrapperWithResponse(response);
				refreshMapFromResponse(response);
			});
		});
		*/
		
		$('.cmmrm-route-index-search-form', wrapper).submit(function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			var form = $(this);
			var request = {url: form.attr('action'), data: form.serialize()};
			requestUrl(request, function(response) {
				replaceWrapperWithResponse(response);
				refreshMapFromResponse(response);
			});
		});
		
		$('.cmmrm-pagination a', wrapper).click(function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			requestUrl($(this).attr('href'), function(response) {
				var html = $(response);
				var archive = $('.cmmrm-routes-archive', html);
				if($('.cmmrm-routes-archive-list').length == '0')
				{
					var listSelector = '.cmmrm-routes-archive-tiles';
				}
				else
				{
					var listSelector = '.cmmrm-routes-archive-list';
				}
				var list = $(listSelector, wrapper);
				list.html($(listSelector, archive).html());
				//initHandlers.apply(list);
				var paginationSelector = '.cmmrm-pagination';
				var pagination = $(paginationSelector, wrapper);
				pagination.html($(paginationSelector, archive).html());
				initHandlers.apply(wrapper);
			});
		});
		
	};
	
	$('.cmmrm-routes-archive[data-ajax=1]').each(initHandlers);
	
});