function CMMRM_WidgetIndexMap(containerId, routes) {
	
	var that = this;
	
	this.containerId = containerId;
	this.container = document.getElementById(containerId);
	this.container.cmmrm = this;
	this.widgetElement = jQuery(this.container).parents('.cmmrm-routes-archive').first();
	
	this.map = new CMMRM_GoogleMap(containerId);
	mapIndexObj = this.map;
	this.routesModels = [];
	this.routesRenderers = [];
	
	this.addRoutes(routes);
	
	if (CMMRM_Map_Settings.indexGeolocation == '1') {
		
		if (CMMRM_Map_Settings.indexGeolocationFindmeButton == '1') {
			addYourLocationButton(this.map.map);
		}

		this.geolocation = new CMMRM_GeolocationFeature(this);
		if (CMMRM_Map_Settings.indexCenterMapGeolocation == '1') {
			this.geolocation.initMapCenterToUserPosition();
		}
	}
	
	this.bindActions();
	this.initWheelScrollZoom();
	
	if (!routes || routes.length == 0) {
		this.map.map.panTo(new google.maps.LatLng(CMMRM_Map_Settings.indexMapDefaultLat, CMMRM_Map_Settings.indexMapDefaultLong));
		this.map.map.setZoom(parseInt(CMMRM_Map_Settings.indexMapDefaultZoom));
	} else {
		this.markerCluster = this.createMarkerClusterer();
		
		if (CMMRM_Map_Settings.indexMapInfoWindowMarkerClustering == '1' && typeof this.markerCluster !== 'undefined') {
			google.maps.event.addListener(this.markerCluster, 'clusterclick', function(cluster) {
				var markers = cluster.getMarkers();
				var markerstring = '';
				var num = 0;
				for(i = 0; i < markers.length; i++) {
					num++;
					markerstring += num+'. '+markers[i]._labelOptions.text+'<br>';
				}
				var infowindow = new google.maps.InfoWindow();
				infowindow.setContent('<strong>'+markers.length+" Marker(s)</strong><br><br>"+markerstring);
				infowindow.setPosition(cluster.getCenter());
				infowindow.setZIndex(99999);
				infowindow.open(this.map);
			});
		}

	}
	
	jQuery(this).trigger('CMMRM.widgetReady');
};

CMMRM_WidgetIndexMap.prototype.resolve = function(name) {
	var deps = this.getDependencies();
	if (typeof deps[name] != 'undefined') {
		return deps[name];
	} else {
		throw "Missing dependency: " + name;
	}
};

CMMRM_WidgetIndexMap.prototype.getDependencies = function() {
	return {
		LocationRenderer: CMMRM_LocationRenderer,
		RouteRenderer: CMMRM_RouteIndexRenderer,
	};
};

CMMRM_WidgetIndexMap.prototype.addRoutes = function(routes) {

	var usertrackCoordinates = [];

	for (var i=0; i<routes.length; i++) {

		shapeFillColor = routes[i].shape_fill_color;
		shapeFillOpacity = routes[i].shape_fill_opacity;
		shapeStrokeColor = routes[i].shape_stroke_color;
		shapeStrokeOpacity = routes[i].shape_stroke_opacity;
		shapeStrokeWeight = routes[i].shape_stroke_weight;
		editableFlag = false;
		draggableFlag = false;

		shapeTooltip = true;

		if(routes[i].shape_type == 'polygon' && routes[i].shape_polygon_coords != '') {
			
			var polygonCoordinates = [];
			var shape_polygon_coords = routes[i].shape_polygon_coords;
			var shape_polygon_coordsRowArr = shape_polygon_coords.split(',(');
			jQuery.each( shape_polygon_coordsRowArr, function(k, coordSet) {
				coordSet = coordSet.replace(/\(/gi,'');
				coordSet = coordSet.replace(/\)/gi,'');
				var coordSetArr = coordSet.split(', ');
				polygonCoordinates.push(new google.maps.LatLng(coordSetArr[0], coordSetArr[1]));
			});
			
			cmlocPolygon = new google.maps.Polygon({
				id: routes[i].ID,
				fillColor: shapeFillColor,
				fillOpacity: shapeFillOpacity,
				strokeColor: shapeStrokeColor,
				strokeOpacity: shapeStrokeOpacity,
				strokeWeight: shapeStrokeWeight,
				editable: editableFlag,
				draggable: draggableFlag,
				zIndex: 1,
				paths: polygonCoordinates,
			});

			cmlocPolygon.setMap(this.map.map);
			
			if(shapeTooltip == true) {
				cmlocPolygon.addListener('click', showPolygonTooltip);
				infoWindow = new google.maps.InfoWindow;
				function showPolygonTooltip(event) {
					var contentString = '<div class="cmmrm-ajax-loader"></div>';
					infoWindow.setContent(contentString);
					infoWindow.setPosition(event.latLng);

					if (typeof infowindow !== 'undefined') {
						infowindow.close();
					}
					if (typeof infoWindow !== 'undefined') {
						infoWindow.close();
					}

					infoWindow.open(mapIndexObj.map);
					
					var location_id = this.id;
					jQuery.post({
						url: CMMRM_Map_Settings.ajaxurl,
						data: {action: 'cmloc_get_route_infowindow', id: location_id, infowindow_type: 'shape'},
						success: function(response) {
							infoWindow.setContent(response);
						}
					});

				}
			}

		}
		
		if(routes[i].shape_type == 'circle' && routes[i].shape_circle_center != '' && routes[i].shape_circle_radius != '') {
			
			var shape_circle_latlong = routes[i].shape_circle_center;
			shape_circle_latlong = shape_circle_latlong.replace('(','');
			shape_circle_latlong = shape_circle_latlong.replace(')','');
			var shape_circle_latlongArr = shape_circle_latlong.split(', ');
			var shape_circle_latitude = parseFloat(shape_circle_latlongArr[0]);
			var shape_circle_longitude = parseFloat(shape_circle_latlongArr[1]);

			var cmlocCircle = new google.maps.Circle({
				id: routes[i].ID,
				fillColor: shapeFillColor,
				fillOpacity: shapeFillOpacity,
				strokeColor: shapeStrokeColor,
				strokeOpacity: shapeStrokeOpacity,
				strokeWeight: shapeStrokeWeight,
				editable: editableFlag,
				draggable: draggableFlag,
				zIndex: 1,
				map: this.map.map,
				center: {lat:shape_circle_latitude, lng:shape_circle_longitude},
				radius: parseFloat(routes[i].shape_circle_radius)
			});
			
			if(shapeTooltip == true) {
				cmlocCircle.addListener('click', showCircleTooltip);
				infoWindow = new google.maps.InfoWindow;
				function showCircleTooltip(event) {
					
					var contentString = '<div class="cmmrm-ajax-loader"></div>';
					infoWindow.setContent(contentString);
					infoWindow.setPosition(event.latLng);

					if (typeof infowindow !== 'undefined') {
						infowindow.close();
					}
					if (typeof infoWindow !== 'undefined') {
						infoWindow.close();
					}

					infoWindow.open(mapIndexObj.map);
					
					var location_id = this.id;
					jQuery.post({
						url: CMMRM_Map_Settings.ajaxurl,
						data: {action: 'cmloc_get_route_infowindow', id: location_id, infowindow_type: 'shape'},
						success: function(response) {
							infoWindow.setContent(response);
						}
					});

				}
			}

		}
		
		if(routes[i].shape_type == 'rectangle' && routes[i].shape_rectangle_bounds != '') {
			
			var shape_rectangle_bounds = routes[i].shape_rectangle_bounds;
			shape_rectangle_bounds = shape_rectangle_bounds.replace(/\(/gi,'');
			shape_rectangle_bounds = shape_rectangle_bounds.replace(/\)/gi,'');
			var shape_rectangle_boundsArr = shape_rectangle_bounds.split(', ');
			var shape_rectangle_north = parseFloat(shape_rectangle_boundsArr[0]);
			var shape_rectangle_west = parseFloat(shape_rectangle_boundsArr[1]);
			var shape_rectangle_south = parseFloat(shape_rectangle_boundsArr[2]);
			var shape_rectangle_east = parseFloat(shape_rectangle_boundsArr[3]);

			var cmlocRectangle = new google.maps.Rectangle({
				id: routes[i].ID,
				fillColor: shapeFillColor,
				fillOpacity: shapeFillOpacity,
				strokeColor: shapeStrokeColor,
				strokeOpacity: shapeStrokeOpacity,
				strokeWeight: shapeStrokeWeight,
				editable: editableFlag,
				draggable: draggableFlag,
				zIndex: 1,
				map: this.map.map,
				bounds: {north:shape_rectangle_north, south:shape_rectangle_south, east:shape_rectangle_east, west:shape_rectangle_west}
			});
			
			if(shapeTooltip == true) {
				cmlocRectangle.addListener('click', showRectangleTooltip);
				infoWindow = new google.maps.InfoWindow;
				function showRectangleTooltip(event) {

					var contentString = '<div class="cmmrm-ajax-loader"></div>';
					infoWindow.setContent(contentString);
					infoWindow.setPosition(event.latLng);

					if (typeof infowindow !== 'undefined') {
						infowindow.close();
					}
					if (typeof infoWindow !== 'undefined') {
						infoWindow.close();
					}

					infoWindow.open(mapIndexObj.map);
					
					var location_id = this.id;
					jQuery.post({
						url: CMMRM_Map_Settings.ajaxurl,
						data: {action: 'cmloc_get_route_infowindow', id: location_id, infowindow_type: 'shape'},
						success: function(response) {
							infoWindow.setContent(response);
						}
					});
				}
			}

		}

		/*
		if(CMMRM_Map_Settings.cmloc_usertrack_user_path_enable == '1') {
			if(routes[i].user_track != '') {
				var user_track_coordinates = routes[i].user_track;
				var user_track_coordinates_split = user_track_coordinates.split(',');
				var obj = {};
				obj['lat'] = parseFloat(user_track_coordinates_split[0]);
				obj['lng'] = parseFloat(user_track_coordinates_split[1]);
				usertrackCoordinates.push(obj);
			}
		}
		*/

		if(CMMRM_Map_Settings.cmloc_usertrack_user_path_enable > 0 && CMMRM_Map_Settings.cmloc_usertrack_user_last_position_only == 0) {
			if(routes[i].user_track != '') {
				var usertrackCoordinates = routes[i].user_track_all;	
			}
		}
		
		//mkk
		if(CMMRM_Map_Settings.indexRouteCurve == '1') {
			var routeModel = new CMMRM_RouteModel(routes[i], routes[i].overviewPath, [], routes[i].icon, '');
		} else {
			var routeModel = new CMMRM_RouteModel(routes[i], routes[i].waypointsString, routes[i]['markers'], routes[i].icon, '');
		}

		this.routesModels.push(routeModel);
		this.routesRenderers.push(new CMMRM_RouteIndexRenderer(this, routeModel, routes, i, routes[i]['markers']));
	}

	/*
	if(usertrackCoordinates.length > 0) {
		var userTrack = new google.maps.Polyline({
			path: usertrackCoordinates,
			strokeColor: "#ff0000",
			strokeOpacity: 1,
			strokeWeight: 0,
			icons: [{
				icon: {
					path: "M 0,-1 0,1",
					strokeOpacity: 1,
					scale: 3,
				},
				offset: "0",
				repeat: "15px",
			}],
		});
		userTrack.setMap(mapIndexObj.map);
	}
	*/

	if(usertrackCoordinates.length > 0) {

		for (var ic=0; ic<usertrackCoordinates.length; ic++) {

			if(CMMRM_Map_Settings.cmloc_usertrack_user_path_enable == 2) {
				var userTrack = new google.maps.Polyline({
					path: usertrackCoordinates[ic],
					strokeColor: CMMRM_Map_Settings.cmloc_usertrack_user_path_color,
					strokeOpacity: 0,
					icons: [{
							icon: {
								path: 'M 0,-1 0,1',
								strokeOpacity: 1,
								scale: 3
							},
							offset: "0",
							repeat: "15px",
						}, {
							icon: {
								path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
								offset: '100%',
								fillColor: CMMRM_Map_Settings.cmloc_usertrack_user_path_color,
								fillOpacity: 1,
								strokeOpacity: 1,
							}
						}],
				});
			} else {
				var userTrack = new google.maps.Polyline({
					path: usertrackCoordinates[ic],
					strokeColor: CMMRM_Map_Settings.cmloc_usertrack_user_path_color,
					strokeOpacity: 1,
					strokeWeight: 2,
					icons: [{
							icon: {
								path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
								offset: '100%',
								fillColor: CMMRM_Map_Settings.cmloc_usertrack_user_path_color,
								fillOpacity: 1,
								strokeOpacity: 1,
							}
						}],
				});
			}
			userTrack.setMap(mapIndexObj.map);

		}

	}

	return this;
};

CMMRM_WidgetIndexMap.prototype.getWidgetElement = function() {
	return this.widgetElement;
};

CMMRM_WidgetIndexMap.prototype.bindActions = function() {
	
	var $ = jQuery;
	var that = this;
	var widget = jQuery(this.getWidgetElement());
	
	// Center map button
	$('.cmmrm-map-center-btn', widget).click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		that.map.center();
	});
	new CMMRM_FullscreenFeature(this);
};

CMMRM_WidgetIndexMap.prototype.prepareMapSnippetThumbTrail = function() {
	
	// Display map thumbs on the routes list
	for (var i=0; i<locations.length; i++) {
		break;
		var location = locations[i];
		var image = this.containerElement.find('.cmmrm-route-snippet[data-route-id='+ location.id +'] .cmmrm-route-featured-image img');
		if (image.length == 1) {
			var pathParams = {weight: 3, color: location.pathColor, enc: location.path};
			var pathParamsVal = [];
			for (var name in pathParams) {
				pathParamsVal.push(name +':'+ pathParams[name]);
			}
			pathParamsVal = pathParamsVal.join('|');
			//console.log(pathParamsVal);
			var url = 'https://maps.googleapis.com/maps/api/staticmap?path='+ encodeURIComponent(pathParamsVal)
				+'&size='+ image.width() +'x'+ image.height() +'&maptype=roadmap&key='+ CMMRM_Map_Settings.googleMapAppKey;
			image.attr('src', url);
		}
	}
};

CMMRM_WidgetIndexMap.prototype.createMarkerClusterer = function() {
	if (CMMRM_Map_Settings.indexMapMarkerClustering == '1') {
		var renderers = this.routesRenderers;
		var markers = [];
		for (var i=0; i<renderers.length; i++) {
			var marker = renderers[i].getMarker();
			if (marker) markers.push(marker);
		}
		var zoomOnClickVal = true;
		if (CMMRM_Map_Settings.indexMapInfoWindowMarkerClustering == '1') {
			zoomOnClickVal = false;
		}
		return new MarkerClusterer(this.map.map, markers, {
			imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
			zoomOnClick: zoomOnClickVal,
			maxZoom: 17, // last 14, highest zoom level is 21
		});
	}
};

CMMRM_WidgetIndexMap.prototype.initWheelScrollZoom = function() {
	var that = this;
	if (CMMRM_Map_Settings.scrollZoom == 'after_click') {
		this.map.map.set('scrollwheel', false);
		google.maps.event.addListener(this.map.map, 'click', function(ev) {
			this.set('scrollwheel', true);
		});
	} else {
		this.map.map.set('scrollwheel', (CMMRM_Map_Settings.scrollZoom == 'enable'));
	}
};

CMMRM_WidgetIndexMap.prototype.getDefaultZoom = function() {
	return CMMRM_Map_Settings.indexMapDefaultZoom;
};