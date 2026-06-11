<?php
namespace com\cminds\mapsroutesmanager\helper\import;

use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\PolylineEncoder;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\helper\KmlHelper;

class RouteImportHelper {
	
	protected $route;
	
	function __construct(Route $route) {
		$this->route = $route;
	}
	
	function setPathCoords(RouteImportSourceInterface $source) {
		$pathCoords = $source->getPathCoords();
		if (!empty($pathCoords)) {
			
			$this->route->setWaypoints($pathCoords);
			
			$polyline = new PolylineEncoder();
			$r = $polyline->encode($pathCoords);
			if (!empty($r->rawPoints)) {
				$this->route->setWaypointsString($r->rawPoints);
			}
			
			$reducedPoints = KmlHelper::reducePointsNumber($pathCoords, 300);
			$r = $polyline->encode($reducedPoints);
			if (!empty($r->rawPoints)) {
				$this->route->setOverviewPath($r->rawPoints);
			}
			
		}
		
		return $this;
	}
	
	function getRoute() {
		return $this->route;
	}
	
	static function createRoute(RouteImportSourceInterface $source, $postdata = array()) {
		
		$postdata = shortcode_atts(array(
			'post_title' => $source->getName(),
			'post_author' => get_current_user_id(),
			'post_status' => 'draft',
		), $postdata);
		
		$route = new Route($postdata);
		if ($id = $route->save()) {
			
			$route->setTravelMode(Settings::TRAVEL_MODE_DIRECT);
			
		} else {
			throw new \Exception('Cannot create route.');
		}
		
		return $route;
		
	}
	
	static function createRouteCSVWithFile(RouteImportSourceInterface $source, $postdata = array()) {
		
		$postdata = shortcode_atts(array(
			'post_title' => '',
			'post_name' => '',
			'post_content' => '',
			'post_author' => get_current_user_id(),
			'post_status' => 'draft'
		), $postdata);
		
		$route = new Route($postdata);
		if ($id = $route->save()) {
			
			$route->setTravelMode(Settings::TRAVEL_MODE_DIRECT);
			
		} else {
			throw new \Exception('Cannot create route.');
		}
		
		return $route;
		
	}

	static function createRouteCsv($postdata = array()) {
		
		$postdata = shortcode_atts(array(
			'post_title' => '',
			'post_name' => '',
			'post_content' => '',
			'post_author' => get_current_user_id(),
			'post_status' => 'draft'
		), $postdata);

		$route = new Route($postdata);
		if ($id = $route->save()) {
			
			$route->setTravelMode(Settings::TRAVEL_MODE_DIRECT);
			
		} else {
			throw new \Exception('Cannot create route.');
		}
		
		return $route;
	}
	
	function importLocations(RouteImportSourceInterface $source) {
		
		$locations = $source->getLocations();

		$routeId = $this->route->getId();
		$authorId = $this->route->getAuthorId();
		$points = $source->getPathCoords();

		if (!empty($locations)) {
			
			$isElevationIncluded = false;
			$this->route->setShowLocationsSection(true);
			
			foreach ($locations as $i => $loc) {
				
				$postdata = array(
					'post_title' => empty($loc['name']) ? 'Location #' . ($i+1) : $loc['name'],
					'menu_order' => ($i+1),
				);
				$locationId = $this->createLocation($postdata, $loc['lat'], $loc['long'], $loc['elevation']);
				if (!empty($loc['elevation'])) {
					$isElevationIncluded = true;
				}
			}
			
			if (!$isElevationIncluded) {
				$this->route->updateLocationsAltitudes();
			}
			
		}

		// Create starting location if no locations markers available
		if (Settings::getOption(Settings::OPTION_IMPORT_CREATE_START_LOCATION)) {
			$title = 'Start';
			$type = Location::TYPE_LOCATION;
			$menuOrder = 1;
			try {
				$location = KmlHelper::importCreateLocation($authorId, $routeId, $title, $type,
					$points[0][0], $points[0][1], $menuOrder);
				unset($location);
			} catch (\Exception $e) {
				self::debug($e->getMessage());
			}
		}
		
		if (Settings::getOption(Settings::OPTION_IMPORT_CREATE_END_LOCATION)) {
			// Create ending location
			$title = 'End';
			$type = Location::TYPE_LOCATION;
			$menuOrder = 2;
			$lastCoord = end($points);
			try {
				$location = KmlHelper::importCreateLocation($authorId, $routeId, $title, $type,
					$lastCoord[0], $lastCoord[1], $menuOrder);
				unset($location);
			} catch (\Exception $e) {
				self::debug($e->getMessage());
			}
		}
		
		return $this;
	}
	
	protected function createLocation(array $postdata, $lat, $long, $altitude = null) {
		
		$postdata = shortcode_atts(array(
			'post_title' => 'Location',
			'post_author' => get_current_user_id(),
			'post_parent' => $this->route->getId(),
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
	
	function updateRouteParamsFromPathCoords(RouteImportSourceInterface $source) {		

		/*
		[Distance] => 5011				// Meters
		[TotalElapsedTime] => 10634		// Time - Seconds to hours
		[MovingTime] => 2193
		[StoppedTime] => 8429
		[MovingSpeed] => 2
		[MaxSpeed] => 2
		[MaxElevation] => 930
		[MinElevation] => 596
		[Ascent] => 92
		[Descent] => 367
		[AvgAscentRate] => 0
		[MaxAscentRate] => 0
		[AvgDescentRate] => 0
		[MaxDescentRate] => -0
		*/

		$params = $this->calculateRouteParamsFromPathCoords($source);
		
		// Distance - distance
		if(isset($source->getXml()->trk->extensions->TrackStatsExtension->Distance) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->Distance != '')
		{
			$Distance = (string)$source->getXml()->trk->extensions->TrackStatsExtension->Distance;
			$params['distance'] = $Distance;
		}
		
		// Ascent - climb
		if(isset($source->getXml()->trk->extensions->TrackStatsExtension->Ascent) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->Ascent != '')
		{
			$Ascent = (string)$source->getXml()->trk->extensions->TrackStatsExtension->Ascent;
			$params['climb'] = $Ascent;
		}

		// Descent - descent
		if(isset($source->getXml()->trk->extensions->TrackStatsExtension->Descent) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->Descent != '')
		{
			$Descent = (string)$source->getXml()->trk->extensions->TrackStatsExtension->Descent;
			$params['descent'] = $Descent;
		}
		
		// MinElevation
		if(isset($source->getXml()->trk->extensions->TrackStatsExtension->MinElevation) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->MinElevation != '')
		{
			$MinElevation = (string)$source->getXml()->trk->extensions->TrackStatsExtension->MinElevation;
			$this->route->setMinElevation($MinElevation);
		}

		// MaxElevation
		if(isset($source->getXml()->trk->extensions->TrackStatsExtension->MaxElevation) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->MaxElevation != '')
		{
			$MaxElevation = (string)$source->getXml()->trk->extensions->TrackStatsExtension->MaxElevation;
			$this->route->setMaxElevation($MaxElevation);
		}

		// TotalElapsedTime
		if(isset($source->getXml()->trk->extensions->TrackStatsExtension->TotalElapsedTime) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->TotalElapsedTime != '')
		{
			$TotalElapsedTime = (string)$source->getXml()->trk->extensions->TrackStatsExtension->TotalElapsedTime;
			$this->route->setDuration($TotalElapsedTime);
		}

		// Distance / TotalElapsedTime
		if(isset($source->getXml()->trk->extensions->TrackStatsExtension->Distance) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->Distance != '' && isset($source->getXml()->trk->extensions->TrackStatsExtension->TotalElapsedTime) && (string)$source->getXml()->trk->extensions->TrackStatsExtension->TotalElapsedTime != '')
		{
			$SDistance = (int)$source->getXml()->trk->extensions->TrackStatsExtension->Distance;
			$STotalElapsedTime = (int)$source->getXml()->trk->extensions->TrackStatsExtension->TotalElapsedTime;
			$params['speed'] = $SDistance/$STotalElapsedTime;
		}

		$isElevationIncluded = false;
		
		if (isset($params['distance'])) {
			$this->route->setDistance($params['distance']);
		}
		if (!empty($params['climb'])) {
			$this->route->setElevationGain($params['climb']);
			$isElevationIncluded = true;
		}
		if (!empty($params['descent'])) {
			$this->route->setElevationDescent($params['descent']);
			$isElevationIncluded = true;
		}
		if (!empty($params['speed'])) {
			$this->route->setAvgSpeed($params['speed']);
		}
		
		if (!$isElevationIncluded) {
			$this->route->determineElevationParams();
		}
		
		return $this;
	}
	
	function calculateRouteParamsFromPathCoords(RouteImportSourceInterface $source) {
		
		$distance = $time = $climb = $descent = $speed = 0;
		
		$coords = $source->getPathCoords();
		if (!empty($coords)) {
			
			$coords = $this->requestAltitudeDataIfNeeded($coords);
			
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
		
		$return = compact('distance', 'time', 'climb', 'descent', 'speed');
		//echo "<pre>"; print_r($return); echo "</pre>"; die;
		return $return;
	}
	
	protected function requestAltitudeDataIfNeeded($coords) {
		
		$altitude = 0;
		foreach ($coords as $coord) {
			$alt = (isset($coord[2]) ? $coord[2] : 0);
			$altitude += $alt;
		}
		
		if ($altitude == 0) {
			
			$reducedCoords = KmlHelper::reducePointsNumber($coords, 200);
			$reducedCoords = array_map(function($c) { return array($c[0], $c[1]); }, $coords);
			
			$elevations = Location::downloadEvelations($reducedCoords);
			
			if (!empty($elevations['results'])) {
				$lastAlt = $elevations['results'][0]['elevation'];;
				$resultIndex = 0;
				foreach ($coords as &$coord) {
					$currentResultRow = $elevations['results'][$resultIndex];
					if ($coord[0] == $currentResultRow['location']['lat']
							AND $coord[1] == $currentResultRow['location']['lng']) {
						
						$lastAlt = $currentResultRow['elevation'];
						$resultIndex++;
						
					}
					$coord[2] = $lastAlt;
				}
			}
			
		}
		
		return $coords;
	}

}