/*
 * @deprecated
 */
function CMMRM_GeolocationMarker(map) {
	
	this.map = map;
	this.userPositionMarker = null;
	this.position = null;
	
	var that = this;
	
	// testing:
	setTimeout(function() {
		//console.log('temp geolocation set position');
		var pos = {coords: {latitude: 54.352789, longitude: 18.531435, accuracy: 50}};
		that.position = pos;
		jQuery(that).trigger('GeolocationMarker:updatePosition', [pos]);
		that.showUserPositionMarker(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
	}, 1000);
	
	this.geolocationWatchPosition(function(pos) {
		that.position = pos;
		that.showUserPositionMarker(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
		jQuery(that).trigger('GeolocationMarker:updatePosition', [pos]);
	}, null, true);
	
}


CMMRM_GeolocationMarker.prototype.geolocationWatchPosition = function(callback, errorCallback, highAccuracy) {
	if ("geolocation" in navigator) {
		if (typeof highAccuracy != 'boolean') highAccuracy = true;
		var geo_options = {
		  enableHighAccuracy: highAccuracy, 
		  maximumAge        : 1000 * 60 * 1, // 1 minute
		  timeout           : 1000 * 60 * 10 // 10 minutes
		};
		errorCallback = function(err) {
			//console.log(err);
			window.CMMRM.Utils.toast('Geolocation error: [' + err.code + '] ' + err.message, null, Math.ceil(err.message.length/5));
		};
		return navigator.geolocation.watchPosition(callback, errorCallback, geo_options);
	}
};


CMMRM_GeolocationMarker.prototype.showUserPositionMarker = function(lat, long, accuracy) {
	var pos = new google.maps.LatLng(lat, long);
	if (!this.userPositionMarker) {
		this.userPositionMarker = new CMMRM_MarkerGeolocation(this.map, pos, accuracy, CMMRM_Map_Settings.geolocationIcon);
	} else {
		this.userPositionMarker.setPosition(pos);
		this.userPositionMarker.updateAccuracy(accuracy);
	}
};
