<?php
namespace com\cminds\mapsroutesmanager\helper;

use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;

class ImportHelper {
	
	static function createRoute(array $postdata, array $pathCoords = array()) {
		$postdata = shortcode_atts(array(
			'post_author' => get_current_user_id(),
			'post_status' => 'draft',
		), $postdata);
		$route = new Route($postdata);
		if ($id = $route->save()) {
			
			$route->setTravelMode(Settings::TRAVEL_MODE_DIRECT);
			if (!empty($pathCoords)) {
				static::updateRoutePolyline($route, $pathCoords);
			}
			
		}
		return $route;
	}
	
	static function updateRoutePolyline(Route $route, array $pathCoords) {
		
		$route->setWaypoints($pathCoords);
		
		$polyline = new PolylineEncoder();
		$r = $polyline->encode($pathCoords);
		if (!empty($r->rawPoints)) {
			$route->setWaypointsString($r->rawPoints);
		}
		
		$reducedPoints = KmlHelper::reducePointsNumber($pathCoords, 300);
		$r = $polyline->encode($reducedPoints);
		if (!empty($r->rawPoints)) {
			$route->setOverviewPath($r->rawPoints);
		}
		
		return $route;
		
	}
	
	static function importLocations(Route $route, array $locations) {
		
		if (!empty($locations)) {
		
			$isElevationIncluded = false;
			$route->setShowLocationsSection(true);
			
			foreach ($locations as $i => $loc) {
				
				$postdata = array(
					'post_title' => empty($loc['name']) ? 'Location #' . ($i+1) : $loc['name'],
					'menu_order' => ($i+1),
				);
				$locationId = static::createLocation($route, $postdata, $loc['lat'], $loc['long'], $loc['elevation']);
				if (!empty($loc['elevation'])) {
					$isElevationIncluded = true;
				}
			}
			
			if (!$isElevationIncluded) {
				$route->updateLocationsAltitudes();
			}
			
		}
		
		return $route;
		
	}
	
	static function createLocation(Route $route, array $postdata, $lat, $long, $altitude = null) {
		
		$postdata = shortcode_atts(array(
			'post_title' => 'Location',
			'post_author' => get_current_user_id(),
			'post_parent' => $route->getId(),
			'post_type' => Location::POST_TYPE,
			'post_status' => 'inherit',
			'ping_status' => 'closed',
			'comment_status' => 'closed',
		), $postdata);
		
		$location = new Location($postdata);
		if ($id = $location->save()) {
			$location->setLat($lat);
			$location->setLong($long);
			$location->setLatLong($lat.','.$long);
			if (!empty($altitude)) {
				$location->setAltitude($altitude);
			}
		}
		
		return $location;
		
	}
	
	static function updateRouteParamsFromPathCoords(Route $route, array $coords) {
		
		$params = static::calculateRouteParamsFromPathCoords($route, $coords);
		$isElevationIncluded = false;
		
		if (isset($params['distance'])) {
			$route->setDistance($params['distance']);
		}
		if (!empty($params['climb'])) {
			$route->setElevationGain($params['climb']);
			$isElevationIncluded = true;
		}
		if (!empty($params['descent'])) {
			$route->setElevationDescent($params['descent']);
			$isElevationIncluded = true;
		}
		if (!empty($params['speed'])) {
			$route->setAvgSpeed($params['speed']);
		}
		
		if (!$isElevationIncluded) {
			$route->determineElevationParams();
		}
		
		return $route;
		
	}
	
	static function calculateRouteParamsFromPathCoords(Route $route, array $coords) {
		
		$distance = $time = $climb = $descent = $speed = 0;
		
		if (!empty($coords)) {
		
			$prev = $first = null;
			foreach ($coords as $i => $coord) {
				if (!$first) $first = $coord;
				if ($prev) {
					$alt = (isset($coord[2]) ? $coord[2] : 0);
					$prevAlt = (isset($prev[2]) ? $prev[2] : 0);
					$distance += Route::calculateDistance($coord[1], $coord[0], $prev[1], $prev[0], $alt, $prevAlt);
					$climb += ($alt > $prevAlt ? $alt - $prevAlt : 0);
					$descent += ($alt < $prevAlt ? $prevAlt - $alt : 0);
				}
				$prev = $coord;
			}
			
			$time = (isset($prev[3]) ? strtotime($prev[3]) : 0) - (isset($first[3]) ? strtotime($first[3]) : 0);
			if ($time != 0) {
				$speed = $distance / $time;
			}
			
		}
		
		return compact('distance', 'time', 'climb', 'descent', 'speed');
	}
	
}