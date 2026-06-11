function CMMRM_RouteIndexRenderer(widget, routeModel, routes, index, markers) {
	
	that = this;
	this.locations = routes;
	this.index = index;

	this.widget = widget;
	this.widgetContainer = jQuery(this.widget.map.container).parents('.cmmrm-routes-archive').first();
	this.routeModel = routeModel;
	
	this.widget.map.extendBounds(this.routeModel.getBounds()).center();
	
	var infoWindow = new google.maps.InfoWindow;
	// open info window
	var onMarkerClick = function() {
		var marker = this;
		if(CMMRM_Index_Map_Settings.markerClickAction == "tooltip") {
			var offsetTop = -40;
			infoWindow.setZIndex(99999);
			infoWindow.set("pixelOffset", new google.maps.Size(0, offsetTop));
			infoWindow.setContent( marker._markerOptions.details );
			infoWindow.open(that.widget.map, marker);
		} else if(CMMRM_Index_Map_Settings.markerClickAction == "redirect") {
			window.location.href = that.routeModel.data.permalink;
		} else if(CMMRM_Index_Map_Settings.markerClickAction == "custom_redirect") {
			//window.location.href = marker._markerOptions.linkedurl;
			if(marker._markerOptions.linkedurl != '') {
				window.open(marker._markerOptions.linkedurl, '_blank');
			}
		} else {
			// none
		}
	};
	// close the info window
	google.maps.event.addListener(this.widget.map, 'click', function() {
		infoWindow.close();
	});
	
	if (typeof markers != 'undefined') {
		if(CMMRM_Map_Settings.startingPointMarker == 'locations' && markers.length > 0) {
			var marker = [];
			for (i = 0; i < markers.length; i++) {
				var nam = markers[i].name;
				var link_url = markers[i].linkurl;
				var lat = markers[i].lat;
				var lng = markers[i].lng;
				var con = markers[i].infoWindowContent;
				var coords = new google.maps.LatLng(lat, lng);
				marker[i] = new CMMRM_Marker(that.widget.map, coords, {
						draggable: false,
						style: 'cursor:pointer;',
						icon: that.routeModel.getIcon(),
						color: that.routeModel.getPathColor(),
						details: con,
						linkedurl: link_url,
					}, {
						text: nam,
						style: 'cursor:pointer;'
					}
				);
				google.maps.event.addListener(marker[i], 'click', onMarkerClick );
			}
		} else {
			this.marker = this.renderMarker();
		}
	} else {
		this.marker = this.renderMarker();
	}

	if(this.widgetContainer.data('showParamOverviewPath') == 1 || CMMRM_Index_Map_Settings.showFullRoute == "1") {
		this.polyline = this.renderPolyline();
	} else {
		this.polyline = null;
	}
	
	var that = this;
	
	jQuery(this).trigger('RouteIndexRenderer:ready');
}

CMMRM_RouteIndexRenderer.prototype.renderMarker = function() {
	var that = this;
	var coords = this.routeModel.getStartingPointCoords();
	var coordsStr = this.routeModel.getGoogleLatLngStr();
	if (coords) {
		
		if (this.checkDuplicates(coordsStr, this.index)) {
			var locationArr = coordsStr.split("|");
			var min = -0.00005;
			var max = 0.00005;
			if(CMMRM_Map_Settings.mapType == 'roadmap' || CMMRM_Map_Settings.mapType == 'terrain') {
				min = -0.00010;
				max = 0.00010;
			} else if(CMMRM_Map_Settings.mapType == 'satellite' || CMMRM_Map_Settings.mapType == 'hybrid' || CMMRM_Map_Settings.mapType == 'OSM') {
				min = -0.00060;
				max = 0.00060;
			}
			lata = parseFloat(locationArr[0]) + parseFloat((Math.random() * (max - min) + min).toFixed(6));
			longa = parseFloat(locationArr[1]) + parseFloat((Math.random() * (max - min) + min).toFixed(6));
			coords = new google.maps.LatLng({lat: lata, lng: longa});
		}

		var marker = new CMMRM_Marker(this.widget.map, coords, {
				draggable: false,
				style: 'cursor:pointer;',
				icon: this.routeModel.getIcon(),
				color: this.routeModel.getPathColor()
			}, {
				text: this.routeModel.getName(),
				style: 'cursor:pointer;'
			}
		);
		
		google.maps.event.addListener(marker, 'click', function() {
			if(CMMRM_Index_Map_Settings.markerClickAction == "tooltip") {
				var offsetTop = -40;
				var infowindow = new google.maps.InfoWindow({
					content: that.routeModel.getLocationInfo(),
					pixelOffset: new google.maps.Size(0, offsetTop)
				});
				infowindow.setZIndex(99999);
				infowindow.open(that.map, marker);
			} else if(CMMRM_Index_Map_Settings.markerClickAction == "redirect") {
				window.location.href = that.routeModel.data.permalink;
			} else if(CMMRM_Index_Map_Settings.markerClickAction == "custom_redirect") {
				// 
			} else {
				// none
			}
		});
		
		return marker;
		
	} else {
		return null;
	}
};

CMMRM_RouteIndexRenderer.prototype.checkDuplicates = function (location, index) {
	var location_arr = location.split("|");
    var flag = false;
    for (k in this.locations) {
        if (typeof index !== 'undefined' && index === k) continue;
        if (this.locations[k].lat === location_arr[0] && this.locations[k].long === location_arr[1]) {
            flag = true;
        }
    }
    return flag;
};

CMMRM_RouteIndexRenderer.prototype.renderPolyline = function() {
	var that = this;
	var waypointsCoordsString = that.routeModel.getWaypointsCoordsString();
	if(!waypointsCoordsString) {
		return;
	}
	
	var waypointsCoordsString_split = waypointsCoordsString.split(',');
	//alert(waypointsCoordsString_split.length);
    waypointsCoordsString_split.forEach(function(waypointsenocde) {
		
		if(waypointsCoordsString_split.length == 1) {
			
			var waypointsCoords = google.maps.geometry.encoding.decodePath(waypointsenocde);
			return new google.maps.Polyline({
				//path: that.routeModel.getPolylineCoords(),
				path: waypointsCoords,
				strokeColor: that.routeModel.getPathColor(),
				strokeWeight: that.getStrokeWeight(),
				opacity: 0.1,
				map: that.widget.map.map
			});

		} else {
			
			setTimeout(function() {
				var waypointsCoords = google.maps.geometry.encoding.decodePath(waypointsenocde);
				return new google.maps.Polyline({
					path: waypointsCoords,
					strokeColor: that.routeModel.getPathColor(),
					strokeWeight: that.getStrokeWeight(),
					opacity: 0.1,
					map: that.widget.map.map
				});
			}, 1000);

		}

	});

};

CMMRM_RouteIndexRenderer.prototype.removeTrailPolylines = function() {
	for (var i=0; i<this.polylines.length; i++) {
		this.polylines[i].setMap(null);
	}
	this.polylines = [];
};

CMMRM_RouteIndexRenderer.prototype.getMarker = function() {
	return this.marker;
};

CMMRM_RouteIndexRenderer.prototype.getStrokeWeight = function() {
	return CMMRM_Map_Settings.indexMapPolylineStrokeWeight;
};