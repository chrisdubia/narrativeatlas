function CMMRM_LocationEditor(widget, locationModel) {
	
	this.widget = widget;
	this.locationModel = locationModel;
	
	this.formItem = this.createFormItem();
	this.updateFormFields();
	
	var that = this;
	
	jQuery(this.locationModel).bind('LocationModel:remove', function() {
		that.remove();
	});
	
	this.initAddressHandlers();
	this.initImagesEditor();
	this.initInfoWindowOpen();
	this.initGenerateWazeButton();
	
	jQuery(this).trigger('LocationEditor:ready');
	
}

CMMRM_LocationEditor.prototype.createFormItem = function() {
	
	var that = this;	
	var locationsList = jQuery('#cmmrm-editor-locations .cmmrm-locations-list');
	
	var item = locationsList.find('li[data-id=0]').first().clone();
	locationsList.append(item);

	var locationId = Math.floor(Math.random()*(999-0+1))+0;
	item.find('.location-description.tinyMCEeditor').attr('id','location-description_'+locationId);
	item.find('.location-description.apply-medium-editor-loop').attr('id','medium-editor-'+locationId);

	item.show();

	if(item.find('.location-description.tinyMCEeditor').length) {
		tinyMCE.init({
			branding: false
		});
		tinyMCE.execCommand('mceAddEditor', false, 'location-description_'+locationId);
	}

	if(item.find('.location-description.apply-medium-editor-loop').length) {
		if(typeof window.MediumEditor !== 'undefined') {
			if(item.find('.location-description.apply-medium-editor-loop').length) {
				window.forums_medium_forum_editor = [];
				item.find('.location-description.apply-medium-editor-loop').each(function(i, element) {
					var key = jQuery(element).attr('id');
					var $this = jQuery(this);
					var whatsnewcontent = $this.closest('div')[0];
					window.forums_medium_forum_editor[key] = new window.MediumEditor( element, {
						placeholder: {
							text: 'Describe your location..',
							hideOnClick: true
						},
						toolbar: {
							buttons: [ 'bold', 'italic', 'unorderedlist', 'orderedlist', 'quote', 'anchor', 'pre' ],
							relativeContainer: whatsnewcontent,
							static: true,
							updateOnEmptySelection: true
						},
						imageDragging: false
					});
				});
			}
		}
	}

	item.find('.cmmrm-location-remove').on('click', function() {
		var r = confirm(CMMRM_Map_Location_Editor_Settings.confirmDeleteMsg);
		if(r) {
			that.locationModel.remove();
		}
	});
	
	return item;
	
};

CMMRM_LocationEditor.prototype.updateFormFields = function() {
	//console.log(this.locationModel.getLat());console.log(this.locationModel.getLng());
	var id = this.locationModel.getId();
	this.formItem.attr('data-id', id);
	this.formItem.find('.location-id').val(id);
	//this.formItem.find('.location-name').val('');
	this.formItem.find('.location-name').val(this.locationModel.getName());
	this.formItem.find('.location-lat').val(this.locationModel.getLat());
	this.formItem.find('.location-long').val(this.locationModel.getLng());
	if(this.formItem.find('.location-description.apply-medium-editor-loop').length > 0) {
		this.formItem.find('.location-description.apply-medium-editor-loop').html(this.locationModel.getDescription());
	}
	this.formItem.find('.location-description').val(this.locationModel.getDescription());
	this.formItem.find('.location-address').val(this.locationModel.getAddress());
	this.formItem.find('.location-linktext').val(this.locationModel.getLinktext());
	this.formItem.find('.location-linkurl').val(this.locationModel.getLinkurl());
	this.formItem.find('.location-distance').val(this.locationModel.getDistance());
};

CMMRM_LocationEditor.prototype.initAddressHandlers = function() {
	var that = this;
	//this.updateAddress();
	jQuery(this.locationModel).bind('LocationModel:setPosition', function(ev, data) {
		//console.log('setpos')
		//console.log(data);
		that.updateFormFields();
		that.updateAddress();	
	});
	jQuery(this.locationModel).bind('LocationModel:setAddress', function(ev, data) {
		jQuery('.location-address', that.formItem).val(data.address);
	});
};

CMMRM_LocationEditor.prototype.updateAddress = function() {
	var that = this;
	this.widget.map.findAddress(new google.maps.LatLng(this.locationModel.getLat(), this.locationModel.getLng()), function(result) {
		that.locationModel.setAddress(result.formatted_address);
	});
};

CMMRM_LocationEditor.prototype.remove = function() {
	this.formItem.remove();
};

CMMRM_LocationEditor.prototype.initImagesEditor = function() {
	this.formItem.find('.cmmrm-images').each(CMMRM_Editor_Images_init);
	var images = this.locationModel.getImages();
	if (images.length > 0) {
		var imageFileInput = this.formItem.find('input[type=hidden][name*=images]');
		var imageFileList = this.formItem.find('.cmmrm-images-list:first');
		for (var i=0; i<images.length; i++) {
			var image = images[i];
			CMMRM_Editor_Images_add(imageFileInput, imageFileList, image.id, image.thumb, image.url, 'yes');
		}
	}
	var cimages = this.locationModel.getcImages();
	if (cimages.length > 0) {
		var imageFileInput = this.formItem.find('input[type=hidden][name*=certbgimageid]');
		var imageFileList = this.formItem.find('.cmmrm-images-list:last');
		for (var i=0; i<cimages.length; i++) {
			var image = cimages[i];
			CMMRM_Editor_Images_add(imageFileInput, imageFileList, image.id, image.thumb, image.url, 'yes');
		}
	}
	if (typeof CMMRM_Location_Icon_init == 'function') {
		CMMRM_Location_Icon_init(this.formItem, this.locationModel.getIcon(), this.locationModel.getIconSize());
	}
};

CMMRM_LocationEditor.prototype.initInfoWindowOpen = function() {
	this.formItem.find('.cmmrm-info-window-open input[type=checkbox]').prop('checked', this.locationModel.getInfoWindowOpen());
	this.formItem.find('.cmmrm-info-window-open input[type=checkbox]').change(function() {
		jQuery(this).parents('.cmmrm-info-window-open').find('input[type=hidden]').val(this.checked ? 1 : 0);
	});
	this.formItem.find('.cmmrm-info-window-open input[type=hidden]').val(this.locationModel.getInfoWindowOpen() ? 1 : 0);
};

CMMRM_LocationEditor.prototype.initGenerateWazeButton = function() {
	this.formItem.find('.cmmrm-generate-waze-button input[type=checkbox]').prop('checked', this.locationModel.getGenerateWazeButton());
	this.formItem.find('.cmmrm-generate-waze-button input[type=checkbox]').change(function() {
		jQuery(this).parents('.cmmrm-generate-waze-button').find('input[type=hidden]').val(this.checked ? 1 : 0);
	});
	this.formItem.find('.cmmrm-generate-waze-button input[type=hidden]').val(this.locationModel.getGenerateWazeButton() ? 1 : 0);
};