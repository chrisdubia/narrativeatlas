function CMMRM_LocationRendererEditor(widget, locationModel) {
	CMMRM_LocationRenderer.call(this, widget, locationModel);
	this.editor = new CMMRM_LocationEditor(widget, locationModel);
	//this.editor.updateAddress();
}

CMMRM_LocationRendererEditor.prototype = Object.create(CMMRM_LocationRenderer.prototype);
CMMRM_LocationRendererEditor.prototype.contructor = CMMRM_LocationRenderer;

CMMRM_LocationRendererEditor.prototype.createMarker = function() {
	var marker = CMMRM_LocationRenderer.prototype.createMarker.call(this);
	var that = this;
	google.maps.event.addListener(marker, 'positionUpdated', function() {
	    var pos = marker.getPosition();
    	that.locationModel.setPosition(pos.lat(), pos.lng());
	});
	return marker;
};

CMMRM_LocationRendererEditor.prototype.getMarkerIconOptions = function() {
	var options = CMMRM_LocationRenderer.prototype.getMarkerIconOptions.call(this);
	options.draggable = true;
	return options;
};