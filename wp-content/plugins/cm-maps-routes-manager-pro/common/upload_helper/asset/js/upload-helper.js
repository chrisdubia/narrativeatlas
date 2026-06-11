function CM_UploadHelper_init() {
	
	var $ = jQuery;
	var container = $(this);
	
	CMMRM_Editor_Images_delete_init(container.find('li'));
	
	$('.cmmrm-images-upload', container).change(function() {
		
		if (this.files.length == 0) return;

		var input = $(this);

		var ismultiple = 'no';
		if(input.attr('multiple') == 'multiple') {
			ismultiple = 'yes';
		}

		var container = input.parents('.cmmrm-images').first();
		var fileList = container.find('.cmmrm-images-list');
		var idsInput = container.find('input[type=hidden]');
		
		var data = new FormData();
		data.append('action', 'cmmrm_route_image_upload');
		data.append('nonce', CMMRM_Editor_Images_Settings.nonce);
		for (var i=0; i<this.files.length; i++) {
			data.append( 'file_'+ i, this.files[i] );
		}
		
		$.ajax({
		  url: CMMRM_Editor_Images_Settings.ajaxurl,
		  data: data,
		  processData: false,
		  contentType: false,
		  type: 'POST',
		  success: function(response) {
			//console.log(response);
			input[0].value = '';
		    if (response.success) {
		    	for (var i=0; i<response.files.length; i++) {
		    		var file = response.files[i];
		    		CMMRM_Editor_Images_add(idsInput, fileList, file.id, file.thumb, file.url, ismultiple);
		    	}
		    }
		  }
		});
		
	});
	
	
	$('.cmmrm-add-video-btn', container).click(function() {
		
		var input = $(this);
		var container = input.parents('.cmmrm-images').first();
		var fileList = container.find('.cmmrm-images-list');
		var idsInput = container.find('input[type=hidden]');
		
		var video = prompt('Enter the video file URL or the YouTube URL address:');
		if (video !== false && video !== null) {
			var data = {action: 'cmmrm_route_add_video', nonce: CMMRM_Editor_Images_Settings.nonce, url: video};
			$.post(CMMRM_Editor_Images_Settings.ajaxurl, data, function(response) {
				//console.log(response);
				if (response.success) {
					CMMRM_Editor_Images_add(idsInput, fileList, response.id, response.thumb, response.url, 'no');
				}
			});
		}
	});
	
	$('.cmmrm-images-add-btn', container).click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		
		tb_show(CMMRM_Editor_Images.title, CMMRM_Editor_Images.url);
		
		var container = $(this).parents('.cmmrm-images').first();
		var fileList = container.find('.cmmrm-images-list');
		var fileInput = container.find('input[type=hidden]');
		
		window.send_to_editor = function(html) {
			//console.log(html);
			
			tb_remove();
			
			if (html.match('youtube.com') || html.match('youtu.be')) { // Youtube video
				
				var video = $('<div>' + html + '</div>');
				var href = video.find('a').attr('href');
				
				$.post(CMMRM_Editor_Images.ajax_url, {action: 'cmmrm_get_image_id', url: href}, function(response) {
					//console.log(response);
					if (response.success) {
						CMMRM_Editor_Images_add(fileInput, fileList, response.id, response.thumb, response.url, 'no');
					}
				});
				
			} else { // Image
				
				var image = $('<div>' + html + '</div>');
				var match = image.find('img').attr('class').match(/wp-image-([0-9]+)/);
				if (match && typeof match[1] == 'string') {
					var response = {id: match[1], thumb: image.find('img').attr('src'), url: image.find('a').attr('href')};
					CMMRM_Editor_Images_add(fileInput, fileList, response.id, response.thumb, response.url, 'no');
				}
				
			}
			
			/*
			var matchHref = html.match(/(a|img) (href|src)=["']([^"']+)["']/);
			if (matchHref && typeof matchHref[3] == 'string') {
				
				var href = matchHref[3];
				
				console.log(href);
				
				$.post(CMMRM_Editor_Images.ajax_url, {action: 'cmmrm_get_image_id', url: href}, function(response) {
					console.log(response);
					if (response.success) {
						CMMRM_Editor_Images_add(fileInput, fileList, response.id, response.thumb, response.url, 'no');
					}
				});
				
			}
			*/

		};
		
	});
	
	//if (jQuery.fn.sortable == 'function')
	$('.cmmrm-images-list', container).sortable({
		update: function(event, ui) {
			var items = ui.item.parents('.cmmrm-images-list').find('li:visible');
			var val = '';
			//var input = ui.item.parents('.cmmrm-images').find('input[name=images]');
			var input = ui.item.parents('.cmmrm-images').find('input[type=hidden].images');
			//console.log(input.val());
			//console.log(items);
			for (var i=0; i<items.length; i++) {
				if (val.length > 0) val += ',';
				val += items[i].getAttribute('data-id');
			}
			//console.log(val);
			input.val(val);
		}
	}).disableSelection();
	
}


function CMMRM_Editor_Images_add(fileInput, fileList, id, thumb, url, ismultiple) {
	if(ismultiple == 'no') {
		fileInput.val(',' + id);
	} else {
		fileInput.val(fileInput.val() + ',' + id);
	}
	
	var item = fileList.find('li[data-id=0]').first().clone();
	item.data('id', id);
	item.attr('data-id', id);
	item.find('img').first().attr('src', thumb);
	item.find('a').first().attr('href', url);
	if(ismultiple == 'no') {
		if(fileList.find('li').length == 1) {
			fileList.append(item);
		} else {
			fileList.find('li:last').replaceWith(item);
		}
	} else {
		fileList.append(item);
	}
	item.fadeIn('slow', function() {
		CMMRM_Editor_Images_delete_init(item);
	});
	fileList.parents('.cmmrm-images').first().find('.cmmrm-field-desc').show();
}

function CMMRM_Editor_Images_delete_init(items) {

	jQuery('.cmmrm-image-delete', items).click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		var obj = jQuery(this);
		var item = obj.parents('li').first();
		var id = item.data('id');
		var container = items.first().parents('.cmmrm-images').first();
		var fileInput = container.find('input[type=hidden]');
		//console.log(fileInput.val());
		var val = fileInput.val().split(',');
		for (var i=0; i<val.length; i++) {
			if (val[i] == id) {
				val.splice(i, 1);
				break;
			}
		}
		fileInput.val(val.join(','));
		//console.log(fileInput.val());
		item.fadeOut('slow', function() {
			item.remove();
		});
	});

}

function CMMRM_Location_Icon_init(locationWrapper, iconUrl, iconSize) {
	
	if (!iconUrl) iconUrl = '';
	
	var $ = jQuery;
	
	// URL
	var hidden = locationWrapper.find('.cmmrm-location-icon input[type=hidden]');
	if (iconUrl.length > 0) {
		var img = $('<img />', {src: iconUrl});
		hidden.before(img);
		hidden.val(iconUrl);
	}
	
	// Icon size
	var size = locationWrapper.find('.cmmrm-location-icon-size');
	if (typeof iconSize == 'string') {
		var option = size.find('option[value="'+ iconSize +'"]');
		if (option.length == 1) {
			size.val(iconSize);
		} else {
			size.val('normal');
		}
	} else {
		size.val('normal');
	}

	// Event click
	$('.cmmrm-location-choose-icon', locationWrapper).click(function(ev) {
		ev.stopPropagation();
		var btn = $(this);
		var container = btn.parents('.cmmrm-location-icon').first();
		var overlayId = 'cmmrm-location-icons-overlay';
		var overlay = $('#'+ overlayId);
		if (overlay.length == 0) {
			overlay = $('<div />', {id: overlayId});
			$('body').append(overlay);
			var list = $('<ul />');
			overlay.append(list);
			for (var i=0; i<CMMRM_Editor_Images_Settings.icons.length; i++) {
				var icon = CMMRM_Editor_Images_Settings.icons[i];
				var item = $('<li><img /></li>');
				item.find('img').attr('src', icon);
				list.append(item);
			}
			$('body').click(function() {
				overlay.hide();
			});
		}
		overlay.find('li').off('click').click(function(ev) {
			var iconUrl = $(this).find('img').attr('src');
			var hidden = container.find('input[type=hidden]');
			hidden.val(iconUrl);
			container.find('img').remove();
			var img = $('<img />', {src: iconUrl});
			hidden.before(img);
		});
		overlay.css('top', btn.offset().top);
		overlay.css('left', container.offset().left);
		overlay.css('width', container.width()+'px');
		overlay.fadeIn();
	});

	$('.cmmrm-location-remove-icon', locationWrapper).click(function(ev) {
		var btn = $(this);
		var container = btn.parents('.cmmrm-location-icon').first();
		container.find('img').remove();
		container.find('input[type=hidden]').attr('src', '');
	});

}	