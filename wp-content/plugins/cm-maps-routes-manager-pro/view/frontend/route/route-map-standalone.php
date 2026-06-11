<?php
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Route;
if (isset($atts['map']) AND $atts['map'] == 0) echo '<div class="cmmrm-map-canvas-hide">'; ?>
<div class="cmmrm-route-map-canvas-outer">
	<div class="cmmrm-directions-steps-wrapper">
		<ul>
			<li class="cmmrm-template">
				<span class="cmmrm-step-distance">Distance</span>
				<span class="cmmrm-step-instructions">Instructions</span>
			</li>
		</ul>
	</div>
	<?php
	global $post;
	$osm_tiles = '';
	$post_id = 0;
	if($post) {
		if($post->ID) {
			$post_id = $post->ID;
		}
	}
	if($post_id > 0) {
		$osm_tiles_arr = get_post_meta($post_id, '_cmmrm_osm_tiles', true);
		if($osm_tiles_arr) {
			$osm_tiles = implode(",", $osm_tiles_arr);
		}
	}
	?>
	<div id="cmmrm-route-<?php echo $atts['mapId']; ?>"
         osm_tiles="<?php echo $osm_tiles; ?>"
         data-map-style='<?php echo isset($mapStyle)?$mapStyle:'[]'; ?>'
         class="cmmrm-route-map-canvas cmmrm-theme-style"
         style="<?php
         if (!empty($atts['mapwidth'])) echo 'width:'. intval($atts['mapwidth']) .'px;';
         if (!empty($atts['mapheight'])) echo 'height:'. intval($atts['mapheight']) .'px;';
         ?>"></div>
</div>
<?php if (isset($atts['map']) AND $atts['map'] == 0) echo '</div>'; ?>

<?php
$writeScript = function() use ($route, $atts) {
	?>
	<script type="text/javascript">

	jQuery(document).ready(function() {
		var mapId = <?php echo json_encode($atts['mapId']); ?>;
		var routeData = <?php echo json_encode($route->getJSRouteData()); ?>;
		var waypointsString = <?php echo json_encode($route->getWaypointsString()); ?>;
			
		var locations = <?php echo json_encode($route->getJSLocations()); ?>;

		<?php
		if(is_plugin_active('cm-maps-virtual-races-addon/plugin.php')) {
			?>
			var containerId;
			var widget;
			var polylinef = new google.maps.Polyline({
				path: [],
				strokeColor: '#FF0000',
				strokeWeight: 3
			});
			var waypointsCoordsf = google.maps.geometry.encoding.decodePath(waypointsString);
			var originf = waypointsCoordsf[0];
			
			var requestWaypointsf = [];
			/*
			for (var i=1; i<waypointsCoordsf.length-1; i++) {
				requestWaypointsf.push({
					location: waypointsCoordsf[i],
					stopover: false,
				});
			}
			*/
			for (var i=0; i<locations.length; i++) {
				requestWaypointsf.push({
					location:new google.maps.LatLng(locations[i].lat,locations[i].lng),
					stopover:false
				});
			}
			var destinationf = waypointsCoordsf[waypointsCoordsf.length-1];
			var avoidHighwaysf = false;
			if(CMMRM_RequestTrail_Settings.avoidHighways == '1') {
				avoidHighwaysf = true;
			}
			var requestf = {
				origin: originf,
				destination: destinationf,
				waypoints: requestWaypointsf,
				travelMode: google.maps.DirectionsTravelMode.WALKING,
				optimizeWaypoints: false,
				avoidHighways: avoidHighwaysf,
			};
			var directionsService = new google.maps.DirectionsService();
			directionsService.route(requestf, function(response, status) {
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
								polylinef.getPath().push(nextSegment[k]);
								bounds.extend(nextSegment[k]);
							}
						}
					}

					containerId = 'cmmrm-route-' + mapId;
					if (!document.getElementById(containerId)) return;
					widget = new CMMRM_WidgetSingleRoute(containerId, routeData, waypointsString, locations, polylinef);

				} else {
					console.log("(b) directions response "+status);
					containerId = 'cmmrm-route-' + mapId;
					if (!document.getElementById(containerId)) return;
					widget = new CMMRM_WidgetSingleRoute(containerId, routeData, waypointsString, locations, '');
				}
			});
			<?php
		} else {
			?>
			var containerId = 'cmmrm-route-' + mapId;
			if (!document.getElementById(containerId)) return;
			var widget = new CMMRM_WidgetSingleRoute(containerId, routeData, waypointsString, locations, '');
			<?php
		}
		
		if (!empty($atts['cmlocations'])) {
			?>
			new CMMRM_IntegrationMapLocations(widget, <?php echo json_encode($atts['cmlocations']); ?>, "<?php echo $atts['cmlocations_marker_click']; ?>");
			<?php
		}		
		if (!empty($atts['zoom']) AND is_numeric($atts['zoom']) AND $atts['zoom'] > 0) {
			?>
			setTimeout(function() {
				widget.map.map.setZoom(<?php echo intval($atts['zoom']); ?>);
			}, 500);
			<?php
		}
		?>
		
		<?php
		if(is_plugin_active('cm-maps-virtual-races-addon/plugin.php')) {
			?>

			var polyline = new google.maps.Polyline({
				path: [],
				strokeColor: '#FF0000',
				strokeWeight: 3
			});
			var waypointsCoords = google.maps.geometry.encoding.decodePath(waypointsString);
			var origin = waypointsCoords[0];
			var requestWaypoints = [];
			/*
			for (var i=1; i<waypointsCoords.length-1; i++) {
				requestWaypoints.push({
					location: waypointsCoords[i],
					stopover: false,
				});
			}
			*/
			for (var i=0; i<locations.length; i++) {
				requestWaypoints.push({
					location:new google.maps.LatLng(locations[i].lat,locations[i].lng),
					stopover:false
				});
			}
			var destination = waypointsCoords[waypointsCoords.length-1];
			var avoidHighways = false;
			if(CMMRM_RequestTrail_Settings.avoidHighways == '1') {
				avoidHighways = true;
			}
			var request = {
				origin: origin,
				destination: destination,
				waypoints: requestWaypoints,
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
					<?php
					do_action('cmmrm_route_map_js_after_init', $route, $atts);
					?>
				} else {
					console.log("(c) directions response "+status);
					<?php
					do_action('cmmrm_route_map_js_after_init', $route, $atts);
					?>
				}
			});
			
			<?php
		} else {
			do_action('cmmrm_route_map_js_after_init', $route, $atts);
		}
		?>

	});

	jQuery(window).load(function() {
		if(jQuery('.cmmrm-elevation-graph').length > 1) {
			setInterval(function() {
				var mapId = <?php echo json_encode($atts['mapId']); ?>;
				var routeData = <?php echo json_encode($route->getJSRouteData()); ?>;
				var waypointsString = <?php echo json_encode($route->getWaypointsString()); ?>;
				var locations = <?php echo json_encode($route->getJSLocations()); ?>;
				var containerId = 'cmmrm-route-' + mapId;
				if (!document.getElementById(containerId)) return;
				if (jQuery("#"+containerId).parent().next().find(".cmmrm-elevation-graph-canvas").html() == "<div class='cmmrm-graph-loader'></div>") {
					var widget = new CMMRM_WidgetSingleRoute(containerId, routeData, waypointsString, locations, '');
					<?php
					if (!empty($atts['cmlocations'])) {
						?>
						new CMMRM_IntegrationMapLocations(widget, <?php echo json_encode($atts['cmlocations']); ?>, "<?php echo $atts['cmlocations_marker_click']; ?>");
						<?php
					}
					if (!empty($atts['zoom']) AND is_numeric($atts['zoom']) AND $atts['zoom'] > 0) {
						?>
						setTimeout(function() {
							widget.map.map.setZoom(<?php echo intval($atts['zoom']); ?>);
						}, 500);
						<?php
					}
					?>
				}
			}, 1000);
		} else {
			jQuery(".cmmrm-graph-loader").remove();
		}
	});
	</script>
	<?php
};
if (Settings::getOption(Settings::OPTION_SINGLE_ROUTE_MAP_SCRIPT_IN_FOOTER)):
	add_action('wp_footer', $writeScript, 20);
else:
	$writeScript();
endif;
?>