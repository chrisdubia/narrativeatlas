function CMMRM_Editor(containerId, routeData, waypointsString, locations) {
	
	CMMRM_WidgetSingleRoute.call(this, containerId, routeData, waypointsString, locations);
	
	var that = this;
	
	this.map.map.setOptions({
		draggableCursor: 'crosshair',
	});
	
	if (CMMRM_Map_Settings.editorTabsFlip == '1') {
		this.editorMode = 'waypoint';
	} else {
		this.editorMode = 'location';
	}
	this.locationsCounter = locations.length;
	this.waypointsRenderers = [];
	this.firstTrailRequest = true;
	
	this.initToolMenu();
	this.initSearchBox();
	this.initImportTool();
	this.initCreatingLocations();
	this.initCreatingPolyline();
	this.initViewpoint();
	this.initPolylinesDivision();
	//mkk
	//this.initSortableLocations();
	this.initParamsEditor();
	
	jQuery(this.routeModel).bind('RouteModel:setWaypointsString', function(ev, data) {
		jQuery('input[name="waypoints-string"]', that.getWidgetElement()).val(this.getWaypointsString());
	});
	
	jQuery(this.routeModel).bind('RouteModel:setPolylineString', function(ev, data) {
		jQuery('input[name=overview-path]', that.getWidgetElement()).val(data.polylineString);
	});
	
	jQuery(this.routeModel).bind('RouteModel:setTravelMode', function(ev, data) {
		jQuery('input[name=travel-mode]', that.getWidgetElement()).val(data.travelMode);
	});
	
	jQuery(this.routeRenderer).bind('RouteRenderer:trailRequestSuccess', function(ev, data) {
		if (that.firstTrailRequest) {
			//console.log('firstTrailRequest');
			that.firstTrailRequest = false;
		} else {
			//console.log('another TrailRequest');
			var distance = data.request.getDistance();
			that.updateDistance(distance);
			var duration = data.request.getDuration();
			that.updateDuration(duration);
			var speed = distance/duration;
			that.updateAvgSpeed(speed);
		}
	});
	
	jQuery(this.blockRouteParams.elevationGraph).bind('ElevationGraph:successResponse', function(ev, data) {
		if (that.firstTrailRequest) {
			return;
		}
		// Update hidden input fields with the elevation data
		that.updateMaxElevation(this.getMaxElevation());
		that.updateMinElevation(this.getMinElevation());
		that.updateElevationGain(this.getElevationGain());
		that.updateElevationDescent(this.getElevationDescent());
		// Use the elevation data to calculate the surface distance
		that.updateDistance(this.calculateSurfaceDistance(data));
	});
	
	
	// Create waypoints renderers
	var waypointsCoords = this.routeModel.getWaypointsCoords();
	if (waypointsCoords.length < CMMRM_Map_Settings.editorWaypointsLimit) {
		// Display waypoints dots for smaller routes
		jQuery(waypointsCoords).each(function(index, value) {
			that.createWaypointRenderer(this, index);
		});
	} else {
		// Hide some features for big routes
		jQuery('.cmmrm-locations-editor-mode a[data-mode=waypoint]').parents('li').first().addClass('cmmrm-disabled');
		//mkk
		jQuery('.cmmrm-route-travel-mode a[data-mode!=DIRECT]').hide();
	}
	
	jQuery('.cmmrm-editor-instructions-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		jQuery(this).parents('form').find('.cmmrm-editor-instructions').slideToggle();
	});
	
	if (CMMRM_Map_Settings.editorGeolocation == '1') {
		this.geolocation = new CMMRM_GeolocationFeature(this);
		if (CMMRM_Map_Settings.editorCenterMapGeolocation == '1' && this.routeModel.isEmpty()) {
			this.geolocation.initMapCenterToUserPosition();
		}
	}
	
	jQuery('.cmmrm-route-params-recalculate-btn').click(function() {
		that.routeModel.updateWaypointsString();
	});

	jQuery('.cmmrm-custom-params-recalculate-btn').click(function() {
		var custom_distance = jQuery('input[name="distance"]').val();
		var custom_time = jQuery('input[name="duration"]').val();
		var custom_speed = jQuery('input[name="avg-speed"]').val();

		custom_distance = (Math.round(custom_distance/1000)) * 1000;

		var newduration = custom_distance/custom_speed;
		jQuery('input[name="duration"]').val(newduration);
		jQuery('.cmmrm-route-duration span').html(CMMRM_BlockRouteParams.prototype.getDurationLabel(newduration));
	});
	
	jQuery(this).trigger('Editor:ready');
	
}

CMMRM_Editor.prototype = Object.create(CMMRM_WidgetSingleRoute.prototype);
CMMRM_Editor.prototype.contructor = CMMRM_WidgetSingleRoute;

CMMRM_Editor.prototype.getDependencies = function() {
	var deps = CMMRM_WidgetSingleRoute.prototype.getDependencies.call(this);
	deps.RouteRenderer = CMMRM_RouteRendererEditor;
	deps.LocationRenderer = CMMRM_LocationRendererEditor;
	deps.ElevationGraph = CMMRM_ElevationGraphEditor;
	return deps;
};

CMMRM_Editor.prototype.getWidgetElement = function() {
	return jQuery(this.container).parents('.cmmrm-route-editor').first().get(0);
};

CMMRM_Editor.prototype.initSearchBox = function() {
	var $ = jQuery;
	var that = this;
	var searchBoxInput = $('.cmmrm-find-location', this.getWidgetElement());
	searchBoxInput.keypress(function(e) {
		e = e || event;
		 var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
		 var result = txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
		 if (!result) this.blur();
		 return result;
	})
	this.searchBox = new google.maps.places.SearchBox(searchBoxInput[0]);
	this.searchBox.addListener('places_changed', function() {
		var places = that.searchBox.getPlaces();
		if (places.length == 0) return;
		var bounds = new google.maps.LatLngBounds();
		places.forEach(function(place) {
			if (place.geometry.viewport) {
		        // Only geocodes have viewport.
		        bounds.union(place.geometry.viewport);
		      } else {
		        bounds.extend(place.geometry.location);
		      }
		});
		that.map.map.fitBounds(bounds);
	});
};

CMMRM_Editor.prototype.initImportTool = function() {
	var $ = jQuery;
	$('.cmmrm-import-kml-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		//$(this).parents('form').find('.cmmrm-import-kml-wrapper').slideToggle();
		if($(this).parents('form').find('.cmmrm-import-kml-wrapper').css('display') == 'none') {
			$(this).parents('form').find('.cmmrm-import-kml-wrapper').css('display', 'block');
			$(this).parents('form').find('.cmmrm-import-kml-wrapper input[type="file"]').removeAttr('disabled');
		} else {
			$(this).parents('form').find('.cmmrm-import-kml-wrapper').css('display', 'none');
			$(this).parents('form').find('.cmmrm-import-kml-wrapper input[type="file"]').attr('disabled', 'disabled');
		}
	});
};

CMMRM_Editor.prototype.initToolMenu = function() {
	var $ = jQuery;
	var that = this;
	$('.cmmrm-locations-editor-mode a', this.getWidgetElement()).click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		
		var obj = $(this);
		
		var waypointsCoords = that.routeModel.getWaypointsCoords();
		//if (obj.data('mode') == 'waypoint' && waypointsCoords.length >= CMMRM_Map_Settings.editorWaypointsLimit) {
		if (obj.data('mode') == 'waypoint' && CMMRM_Editor_Settings.editorWaypointsEnable == 1) {
			alert('This drawing tool is disabled because cannot edit the imported route.');
			return;
		}
		
		that.editorMode = obj.data('mode');
		obj.parents('ul').find('li.current').removeClass('current');
		obj.parents('li').first().addClass('current');
		
	});
};

CMMRM_Editor.prototype.initCreatingLocations = function() {
	var $ = jQuery;
	var that = this;
	
	// Click listener
	google.maps.event.addListener(this.map.map, 'click', function(ev) {
		//console.log(that.map.suspendAddWaypoints);
		if (that.map.suspendAddWaypoints) return;
		if ('location' == that.editorMode) {
			that.addLocation(ev.latLng.lat(), ev.latLng.lng());
		}
	});
	
};

CMMRM_Editor.prototype.addLocation = function(lat, lng) {
	this.locationsCounter++;
	
	var locationModel = this.routeModel.addLocation({
		id: 0,
		name: CMMRM_Editor_Settings.newLocationLabel.replace('%d', this.locationsCounter),
		lat: lat,
		lng: lng,
		description: "",
		type: "location",
		address: "",
		icon: "",
		images: []
	});
	
	var renderer = new (this.resolve('LocationRenderer'))(this, locationModel);
	renderer.editor.updateAddress();
};

CMMRM_Editor.prototype.initCreatingPolyline = function() {
	var $ = jQuery;
	var that = this;
	google.maps.event.addListener(this.map.map, 'click', function(ev) {
		if (that.map.suspendAddWaypoints) return;
		if ('waypoint' == that.editorMode) {
			that.createWaypoint(ev.latLng);
		}
	});
};

CMMRM_Editor.prototype.createWaypoint = function(coords, index) {
	this.routeModel.addWaypoint(coords, index);
	this.createWaypointRenderer(coords, index);
	this.routeModel.updateWaypointsString();
};

CMMRM_Editor.prototype.createWaypointRenderer = function(waypointCoords, index) {
	
	if (typeof index == 'undefined') {
		index = this.waypointsRenderers.length;
	}
	
	//console.log('create '+ index);
	
	var that = this;
	var renderer = new (this.resolve('WaypointRenderer'))(this, waypointCoords, index);
	this.waypointsRenderers.splice(index, 0, renderer);
	
	// Update renderers index
	for (var i=index+1; i<this.waypointsRenderers.length; i++) {
		this.waypointsRenderers[i].setWaypointIndex(i+1);
	}
	
	// Check if this is correct
	/*
	for (var i=0; i<this.waypointsRenderers.length; i++) {
		console.log('i='+ i +' index='+ this.waypointsRenderers[i].index);
	}
	*/
	
	jQuery(renderer).bind('WaypointRenderer:updatePosition', function(ev, data) {
		that.routeModel.waypointsCoords[this.getWaypointIndex()] = this.getWaypointCoords();
		that.routeModel.updateWaypointsString();
	});
	jQuery(renderer).bind('WaypointRenderer:remove', function(ev, data) {
		
		//console.log('remove '+ this.index);
		that.routeModel.removeWaypointByIndex(this.index);
		that.routeModel.updateWaypointsString();
		
		// Update renderers index
		for (var i=this.index+1; i<that.waypointsRenderers.length; i++) {
			that.waypointsRenderers[i].setWaypointIndex(i-1);
		}
		
		var removed = that.waypointsRenderers.splice(this.index, 1);
		
		// Check if this is correct
		/*		
		for (var i=0; i<that.waypointsRenderers.length; i++) {
			console.log('i='+ i +' index='+ that.waypointsRenderers[i].index);
		}
		*/
		
	});
	return renderer;
};

CMMRM_Editor.prototype.initViewpoint = function() {
	var that = this;
	var waypointsString = this.routeModel.getWaypointsString();
	if (0 == this.routeModel.getLocations().length && (!waypointsString || 0 == waypointsString.length)) {
		var defaultCoords = new google.maps.LatLng(CMMRM_Editor_Settings.defaultLat, CMMRM_Editor_Settings.defaultLong);
		//that.map.extendBounds([defaultCoords]);
		//var oldCenter = that.map.center;
		that.map.center = function() {
			that.map.map.panTo(defaultCoords);
			that.map.map.setZoom(parseInt(CMMRM_Editor_Settings.defaultZoom));
		};
		/*
		setTimeout(function() {
			that.routeRenderer.widget.map.map.panTo(new google.maps.LatLng(CMMRM_Editor_Settings.defaultLat, CMMRM_Editor_Settings.defaultLong));
			that.routeRenderer.widget.map.map.setZoom(parseInt(CMMRM_Editor_Settings.defaultZoom));
		}, 500);
		*/
	}
};

CMMRM_Editor.prototype.initPolylinesDivision = function() {
	if (this.routeModel.getWaypointsCoords().length < CMMRM_Map_Settings.editorWaypointsLimit) {
		var that = this;
		jQuery(this.routeRenderer).bind('RouteRenderer:trailRequestSuccess', function(ev, data) {
			that.setPolylinesDivisionListeners();
		});
	}
};

/**
 * Divide polyline
 */
CMMRM_Editor.prototype.setPolylinesDivisionListeners = function() {
	var that = this;
	for (var i=0; i<this.routeRenderer.polylines.length; i++) {
		var p = this.routeRenderer.polylines[i];
		p.addListener('click', function(ev) {
			//console.log('legIndex = '+ this.legIndex);
			that.createWaypoint(ev.latLng, this.legIndex+1);
			//var location = {name: "Waypoint", lat: ev.latLng.lat(), long: ev.latLng.lng(), id: null, type: 'waypoint'};
			//mapObj.addLocation(location, legIndex+1);
			//mapObj.requestTrail();
		});
	}
};

CMMRM_Editor.prototype.initSortableLocations = function() {
	
	//if (jQuery.fn.sortable != 'function') return;
	var $ = jQuery;
	$('#cmmrm-editor-locations .cmmrm-locations-list', this.getWidgetElement).sortable({
		update: function(event, ui) {
			/*
			var obj = $(ui.item[0]);
			var index = mapObj.getLocationIndexByItem(ui.item[0]);
			var newIndex = obj.index()-1;
			console.log('index '+ index +' new '+ newIndex);
			if (index != newIndex) {
				var location = mapObj.locations.splice(index, 1)[0];
				mapObj.locations.splice(newIndex, 0, location);
				mapObj.requestTrail();
			}
			*/
		}
	});

};

CMMRM_Editor.prototype.updateDistance = function(val) {
	jQuery(this.getWidgetElement()).find('input[name=distance]').val(val);
	this.blockRouteParams.updateDistance(val);
	return this;
};

CMMRM_Editor.prototype.updateDuration = function(val, dontUpdateSpeed) {
	jQuery(this.getWidgetElement()).find('input[name=duration]').val(Math.round(val));
	this.blockRouteParams.updateDuration(val, dontUpdateSpeed);
	return this;
};

CMMRM_Editor.prototype.updateAvgSpeed = function(val) {
	jQuery(this.getWidgetElement()).find('input[name="avg-speed"]').val(val);
	var withDurationUpdate = true;
	this.blockRouteParams.updateAvgSpeed(val);
	//var newDuration = this.blockRouteParams.distance / val;
	//var dontUpdateSpeed = true;
	//this.updateDuration(newDuration, dontUpdateSpeed);
	return this;
};

CMMRM_Editor.prototype.updateMaxElevation = function(val) {
	jQuery(this.getWidgetElement()).find('input[name=max-elevation]').val(val);
	this.blockRouteParams.updateMaxElevation(val);
	return this;
};

CMMRM_Editor.prototype.updateMinElevation = function(val) {
	jQuery(this.getWidgetElement()).find('input[name=min-elevation]').val(val);
	this.blockRouteParams.updateMinElevation(val);
	return this;
};

CMMRM_Editor.prototype.updateElevationGain = function(val) {
	jQuery(this.getWidgetElement()).find('input[name=elevation-gain]').val(val);
	this.blockRouteParams.updateElevationGain(val);
	return this;
};

CMMRM_Editor.prototype.updateElevationDescent = function(val) {
	jQuery(this.getWidgetElement()).find('input[name=elevation-descent]').val(val);
	this.blockRouteParams.updateElevationDescent(val);
	return this;
};

CMMRM_Editor.prototype.initParamsEditor = function() {
	
	var $ = jQuery;
	var that = this;
	
	// Change route params
	$('.cmmrm-route-params li', this.getWidgetElement()).each(function() {
		var item = $(this);
		var label = item.find('strong').text();
		var name = item[0].className.replace('cmmrm-editable', '').replace('cmmrm-route-', '').replace('cmmrm-', '').replace(/\s/, '');
		if (name == 'duration') label += ' (format: 1 h 20 min 30 s)';
		item.addClass('cmmrm-editable');
		item.attr('title', 'Edit');
		item.click(function(ev) {
			
			var input = item.parents('form').find('input[name='+ name +']');
			var promptValue = input.val();
			if (name == 'duration') promptValue = CMMRM_BlockRouteParams.prototype.getDurationLabel(promptValue);
			else if (name == 'avg-speed') promptValue = CMMRM_BlockRouteParams.prototype.getSpeedLabel(promptValue);
			else if (name == 'distance') promptValue = CMMRM_BlockRouteParams.prototype.getDistanceLabel(promptValue);
			else promptValue = Math.round(promptValue);
			
			var val = window.prompt(label, promptValue);
			if (val !== false && val !== null) {
				switch (name) {
					case 'distance':
						that.updateDistance(that.parseDistance(val));
						break;
					case 'duration':
						var dontUpdateSpeed = true;
						that.updateDuration(that.parseDuration(val), dontUpdateSpeed);
						break;
					case 'max-elevation':
						val = parseInt(val);
						if (isNaN(val)) return;
						that.updateMaxElevation(val);
						break;
					case 'min-elevation':
						val = parseInt(val);
						if (isNaN(val)) return;
						that.updateMinElevation(val);
						break;
					case 'elevation-gain':
						val = parseInt(val);
						if (isNaN(val)) return;
						that.updateElevationGain(val);
						break;
					case 'elevation-descent':
						val = parseInt(val);
						if (isNaN(val)) return;
						that.updateElevationDescent(val);
						break;
					case 'avg-speed':
						that.updateAvgSpeed(that.parseSpeed(val));
						break;
				}
			}
		});
	});
	
};

CMMRM_Editor.prototype.parseDuration = function(val) {
	val = val.replace(/[^0-9hms]/g, '').match(/([0-9]+h)?([0-9]+m)?([0-9]+s)?/);
	for (var i=1; i<=3; i++) {
		val[i] = parseInt(val[i]);
		if (isNaN(val[i])) val[i] = 0;
	}
	//console.log(val);
	return val[1] * 3600 + val[2] * 60 + val[3];
};

CMMRM_Editor.prototype.parseDistance = function(val) {
	var value = val.match(/[0-9]+/);
	var unit = val.match(/[a-z]+/);
	if (unit == 'm') {
		return value;
	}
	else if (unit == 'km') {
		return value * 1000;
	}
	else if (unit == 'mi' || unit == 'mil' || unit == 'mile' || unit == 'miles') {
		// convert miles to meters
		return value * CMMRM_Map_Settings.feetToMeter * CMMRM_Map_Settings.feetInMile;
	} else {
		// Unknown unit
		return value;
	}
};

/**
 * Returns speed in m/s
 */
CMMRM_Editor.prototype.parseSpeed = function(val) {
	//console.log('parseSpeed', val)
	var value = parseInt(val.replace(/[^0-9]/g, ''));
	
	if (val.match('mph')) {
		//meterPerSec/CMMRM_Map_Settings.feetToMeter/CMMRM_Map_Settings.feetInMile*3600
		// convert mph to m/s
		return value * CMMRM_Map_Settings.feetToMeter * CMMRM_Map_Settings.feetInMile / 3600;
	}
	
	var units = val.match(/(km|m)\/(h|min|s)/);
	//console.log(units);
	
	var lengthUnit = 'm';
	if (units && typeof units[1] != 'undefined') lengthUnit = units[1];
	
	var timeUnit = 's';
	if (units && typeof units[2] != 'undefined') timeUnit = units[2];
	
	var lengthMultipler = 1;
	if (lengthUnit == 'km') lengthMultipler = 1000;
	
	var timeMultipler = 1;
	if (timeUnit == 'min') timeMultipler = 60;
	else if (timeUnit == 'h') timeMultipler = 60*60;
	
	var result = value * lengthMultipler / timeMultipler;
	//console.log(value, lengthMultipler, timeMultipler, result);
	return result;	
};

CMMRM_Editor.prototype.createLocationClusterer = function() {
	// don't
};

CMMRM_Editor.prototype.getDefaultZoom = function() {
	return CMMRM_Map_Settings.editorMapDefaultZoom;
};

jQuery(document).ready(function() {
	
	jQuery('body').on('click', '.btn_calc_loc_distance', function() {
		var r = confirm("Do you want to calculate location markers distance as per route start point?");
		if(r) {
			overviewpathstring = jQuery('input[name=overview-path]').val();
			if(overviewpathstring != '') {
				if(jQuery('ul.cmmrm-locations-list > li').length > 1) {
					
					jQuery(".form-summary").find('input[type=submit]').prop('disabled', true);

					var counter_of_loc = 0;
					jQuery("ul.cmmrm-locations-list > li").each(function( index ) {
						if(counter_of_loc > 0) {
							var lat = jQuery(this).find('.location-lat').val();
							var lng = jQuery(this).find('.location-long').val();
							var location_distance_field_obj = jQuery(this).find('.location-distance');
							var polyline = new google.maps.Polyline({
								path: [],
								strokeColor: '#FF0000',
								strokeWeight: 3
							});
							var waypointsCoords = google.maps.geometry.encoding.decodePath(overviewpathstring);
							var origin = waypointsCoords[0];
							var destination = new google.maps.LatLng(lat,lng);
							var avoidHighways = false;
							if(CMMRM_RequestTrail_Settings.avoidHighways == '1') {
								avoidHighways = true;
							}
							var request = {
								origin: origin,
								destination: destination,
								travelMode: google.maps.DirectionsTravelMode.WALKING,
								optimizeWaypoints: false,
								avoidHighways: avoidHighways,
							};
							var directionsService = new google.maps.DirectionsService();
							directionsService.route(request, function(response, status) {
								if (status == google.maps.DirectionsStatus.OK) {
									var bounds = new google.maps.LatLngBounds();
									var route = response.routes[0];
									startLocation = new Object();
									endLocation = new Object();
									var legs = response.routes[0].legs;
									for (i=0;i<legs.length;i++) {
										if (i == 0) { 
											startLocation.latlng = legs[i].start_location;
											startLocation.address = legs[i].start_address;
										} else {
											waypts[i] = new Object();
											waypts[i].latlng = legs[i].start_location;
											waypts[i].address = legs[i].start_address;
										}
										endLocation.latlng = legs[i].end_location;
										endLocation.address = legs[i].end_address;
										var steps = legs[i].steps;
										for (j=0;j<steps.length;j++) {
											var nextSegment = steps[j].path;
											for(k=0;k<nextSegment.length;k++) {
												polyline.getPath().push(nextSegment[k]);
												bounds.extend(nextSegment[k]);
											}
										}
									}
									location_distance_field_obj.val((polyline.Distance()/1000).toFixed(3));
								} else {
									console.log("(a) directions response "+status);
								}
							});

						}
						counter_of_loc++;
					});
					jQuery(".form-summary").find('input[type=submit]').prop('disabled', false);
				} else {
					alert("Marker is missing on map so please add marker first.");
				}
			} else {
				alert("Route is missing on map so please draw route first.");
			}
		}
	});

	jQuery('body').on('click', '.btn_remove', function() {
		var r = confirm(CMMRM_Map_Route_Editor_Settings.confirmDeleteMsg);
		if(r) {
			var dataId = jQuery(this).attr("data-id");
			jQuery.ajax({
			  type: 'POST',
			  url: CMMRM_Map_Route_Editor_Settings.ajaxurl,
			  data: { action: 'cmmrm_remove_route', id: dataId },
			  success: function(response) {
				if(response.success == '1') {
					window.location.href = CMMRM_Map_Route_Editor_Settings.returnurl;
				}
			  }
			});
		}
	});

	jQuery('body').on('click', '.cmmrm_add_new_category', function() {
		if(jQuery("ul.cmmrm-form-checkbox-tree").length == 0) {
			jQuery('.cmmrm_categories').append('<ul class="cmmrm-form-checkbox-tree"></ul>');
		}
		jQuery("ul.cmmrm-form-checkbox-tree:eq(0)").append('<li><label><input type="checkbox" name="categories[]" value="0" disabled="disabled" /><span><input type="text" data-pid="0" /></span></label><span class="yes dashicons dashicons-yes"></span><span class="dismiss dashicons dashicons-dismiss"></span></li>' );
	});

	jQuery('body').on('click', '.cmmrm-form-checkbox-tree span.plus', function() {
		var datapid = jQuery(this).parent('li').find('input[type="checkbox"]').val();
		if(jQuery(this).parent('li').find('ul').length > 0) {
			jQuery(this).parent('li').find('ul').append('<li><label><input type="checkbox" name="categories[]" value="0" disabled="disabled" /><span><input type="text" data-pid="'+datapid+'" /></span></label><span class="yes dashicons dashicons-yes"></span><span class="dismiss dashicons dashicons-dismiss"></span></li>' );
		} else {
			jQuery(this).parent('li').append('<ul class="cmmrm-form-checkbox-tree"><li><label><input type="checkbox" name="categories[]" value="0" disabled="disabled" /><span><input type="text" data-pid="'+datapid+'" /></span></label><span class="yes dashicons dashicons-yes"></span><span class="dismiss dashicons dashicons-dismiss"></span></li></ul>' );
		}
	});
	
	jQuery('body').on('click', '.cmmrm-form-checkbox-tree span.dismiss', function() {
		jQuery(this).parent('li').remove();
	});

	jQuery('body').on('click', '.cmmrm-form-checkbox-tree span.yes', function() {
		var that = jQuery(this);

		var catName = that.parent('li').find('input[type="text"]').val();
		var catParentId = that.parent('li').find('input[type="text"]').attr('data-pid');
		
		that.parent('li').find('input[type="text"]').css('border-color','#ccc');
		if(catName == '') {
			that.parent('li').find('input[type="text"]').css('border-color','red');
			return false;
		}
		
		that.parent('li').find('input[type="text"]').attr("disabled", "disabled");
		that.removeClass('yes').removeClass('dashicons-yes').addClass('dashicons-update-alt').addClass('update');
		that.parent('li').find('span.dismiss').hide();

		jQuery.ajax({
		  type: 'POST',
		  url: CMMRM_Map_Route_Editor_Settings.ajaxurl,
		  data: { action: 'cmmrm_add_category', catName: catName, catParentId: catParentId },
		  success: function(response) {
			if(response.success == '1') {
				that.removeClass('update').removeClass('dashicons-update-alt').addClass('dashicons-plus-alt').addClass('plus');
				that.parent('li').find('label span').html(catName);
				that.parent('li').find('input[type="checkbox"]').removeAttr('disabled');
				that.parent('li').find('input[type="checkbox"]').val(response.lastid);
			}
		  }
		});

	});

	/*
	if(jQuery("input[name='overview-path']").length == '1' && jQuery("input[name='duration']").length == '1') {
		//if(jQuery("input[name='overview-path']").val() != '' && jQuery("input[name='duration']").val() == '0') {
		if(jQuery("input[name='overview-path']").val() != '') {
			jQuery('.cmmrm-route-params-recalculate-btn').trigger('click');
			setTimeout(function(){
				jQuery('.cmmrm-route-params-recalculate-btn').trigger('click');
				
				var routeid = jQuery("input[name='routeid']").val();
				var distance = jQuery("input[name='distance']").val();
				var duration = jQuery("input[name='duration']").val();
				var avg_speed = jQuery("input[name='avg-speed']").val();
				var overview_path = jQuery("input[name='overview-path']").val();

				jQuery.ajax({
					type: 'POST',
					url: CMMRM_Map_Route_Editor_Settings.ajaxurl,
					data: { action: 'cmmrm_update_distance', routeid:routeid, distance:distance, duration:duration, avg_speed:avg_speed, overview_path:overview_path },
					success: function(response) {
						if(response.success == '1') {
							//alert(response.msg);
						}
					}
				});

			}, 1000);
		}
	}
	*/

});