<?php
namespace com\cminds\mapsroutesmanager\helper;

use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Attachment;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Location;

class KmlHelper {
	
	const MIME_TYPE = 'application/vnd.google-earth.kml+xml';
	const KMZ_MIME_TYPE = 'application/vnd.google-earth.kmz';
	
	const UPLOAD_DIR = 'cmmrm';
	const DEBUG_MODE = 0;
	
	static function export(Route $route) {
		
		$locations = $route->getLocations();
		
		/* @var $firstLocation Location */
		$firstLocation = reset($locations);
		/* @var $lastLocation Location */
		$lastLocation = end($locations);

		//mkk
		/*
		$waypoints = array_map(function($val) {
			return $val[1].','.$val[0];
		}, KmlHelper::decodePolylineToArray($route->getOverviewPath()));
		*/
		if (strpos($route->getOverviewPath(), '\\') !== false) {
			$waypoints = array_map(function($val) {
				return $val[1].','.$val[0];
			}, KmlHelper::decodePolylineToArray($route->getOverviewPath()));
		} else {
			$waypoints = array_map(function($val) {
				return $val[1].','.$val[0];
			}, KmlHelper::decodePolylineToArray($route->getWaypointsString()));
		}
		
		$placemarks = array();
		foreach ($locations as $i => $location) {
			/* @var $location Location */
			if ($i == 0) $style = 'Start';
			else if ($i == count($locations)-1) $style = 'End';
			else if ($location->getLocationType() == Location::TYPE_LOCATION) {
				$style = 'Middle';
			} else {
				$style = 'Waypoint';
			}
			$placemarks[] = self::getPlacemark($location, $style);
		}
		
		$tempalte = file_get_contents(App::path('asset/kml-template.xml'));
		return strtr($tempalte, array(
			'{{routeName}}' => htmlspecialchars($route->getTitle()),
			'{{routeDescription}}' => $route->getContent(),
			'{{distance}}' => $route->getDistance(),
			'{{duration}}' => $route->getDuration(),
			'{{speed}}' => $route->getAvgSpeed(),
			'{{minAltitude}}' => $route->getMinElevation(),
			'{{maxAltitude}}' => $route->getMaxElevation(),
			'{{climb}}' => $route->getElevationGain(),
			'{{descent}}' => $route->getElevationDescent(),
			'{{routeExtendedData}}' => self::getRouteExtendedData($route),
			'{{placemarks}}' => implode(PHP_EOL, $placemarks),
			'{{pathCoordinates}}' => implode(PHP_EOL, $waypoints),
		));
		
	}
	
	static function importSingleRoute($path, $fileName, $authorId, $maxWaypoints) {
		set_time_limit(3600);
		if ($kmlSource = self::importReadfile($path, $fileName)) {
			return self::importRouteFile($kmlSource, $authorId, $maxWaypoints, $fileName);
		} else {
			throw new \Exception('Failed to load file.');
		}
	}
	
	static function importRouteFile($kmlSource, $authorId, $maxWaypoints, $altName) {
		set_time_limit(3600);
		
		/* @var $xml SimpleXMLElement */
		$xml = static::getSimpleXmlInstance($kmlSource);
		
		$routeData = self::getRouteData($xml);
		$route = self::importCreateRoute($xml, $routeData, $authorId, $altName);
		$routeId = $route->getId();
		
		//if (!empty($routeData['travelMode'])) {
		// 	self::importLocations($route, $xml, $routeData, $maxWaypoints);
		//} else {
		//	self::importOptimizeLocations($route, $xml, $routeData, $maxWaypoints);
		//}

		// Import route path
		$points = self::importRoutePath($route, $xml, $routeData);

		// Import locations markers
		self::importLocations($route, $xml, $routeData, $points);
		
		self::setRouteParameters($xml, $routeData, $route);
			
		return $route;
	}
	
	/**
	 * 
	 * @param string $kmlSource
	 * @return SimpleXMLElement
	 */
	static function getSimpleXmlInstance($kmlSource) {
		$kmlSource = str_replace('xmlns=', 'ns=', $kmlSource);
		return simplexml_load_string($kmlSource);
	}
	
	static function importRoutePath(Route $route, $xml, $routeData) {

		$points = self::getDocPath($xml);
		
		// Transpose points y-x to x-y
		foreach ($points as &$point) {
			$temp = $point[0];
			$point[0] = $point[1];
			$point[1] = $temp;
		}

		$route->setWaypoints($points);
		
		$lines = 1;
		//echo "<pre>"; print_r($xml); echo "</pre>";
		if(isset($xml->Document->Folder->Placemark->MultiGeometry->LineString) && count($xml->Document->Folder->Placemark) == 1) {
			$lines = count($xml->Document->Folder->Placemark->MultiGeometry->LineString);
		} else if(isset($xml->Document->Folder->Placemark)) {
			$lines = count($xml->Document->Folder->Placemark);
		} else if(isset($xml->Document->Placemark->MultiGeometry->LineString)) {
			$lines = count($xml->Document->Placemark->MultiGeometry->LineString);
		}

		if($lines > 1) {
			$multiplepoints = self::getMultipleDocPath($xml);
			if($multiplepoints['type'] == 'Point') {
				$polyline = new PolylineEncoder();
				$r = $polyline->encode($points);
				if (!empty($r->rawPoints)) {
					$route->setWaypointsString($r->rawPoints);
				}
			} else {
				$encodedstr = '';
				foreach ($multiplepoints['data'] as $pointset) {
					$polyline = new PolylineEncoder();
					$r = $polyline->encode($pointset);
					if(!empty($r->points)) {
						$encodedstr .= ','.$r->points;
					}
				}
				$encodedstr = substr($encodedstr, 1);
				if (!empty($encodedstr)) {
					$route->setWaypointsString($encodedstr);
				}
			}
		} else {
			$polyline = new PolylineEncoder();
			$r = $polyline->encode($points);
			if (!empty($r->rawPoints)) {
				$route->setWaypointsString($r->rawPoints);
			}
		}

		// Reduce waypoints polyline to create an overview path
		$reducedPoints = self::reducePointsNumber($points, 300);

		$polyline = new PolylineEncoder();
		$r = $polyline->encode($reducedPoints);
		if (!empty($r->rawPoints)) {
			$route->setOverviewPath($r->rawPoints);
		}
		
		return $points;
	}
	
	static function reducePointsNumber($points, $max) {
		$count = count($points);
		if ($max >= $count) {
			$reducedPoints = $points;
		} else {
			$step = $count / $max;
			for ($i=0; $i<$count; $i += $step) {
				$k = round($i);
				if ($k < $count AND isset($points[$k])) {
					$reducedPoints[$k] = $points[$k];
				} else {

				}
			}
			$reducedPoints = array_values($reducedPoints);
		}
		return $reducedPoints;
	}
	
	static function importLocations(Route $route, $xml, $routeData, $points = array()) {
		
		set_time_limit(3600);
		$routeId = $route->getId();
		$authorId = $route->getAuthorId();
		
		$placemarks = self::getDocPlacemarks($xml);
		
		if (!empty($placemarks)) {
			foreach ($placemarks as $i => $placemark) {
				$title = (string)$placemark['node']->name;
				if ($placemark['node']->styleUrl AND (string)$placemark['node']->styleUrl == '#cmRouteLocationWaypointStyle') {
					$type = Location::TYPE_WAYPOINT;
				} else {
					$type = Location::TYPE_LOCATION;
				}
				$menuOrder = $i + 1;
				try {
					$latitude = $placemark['data']['latitude'];
					if($latitude == '') {
						$latitude = isset($placemark['coords']['1'])?$placemark['coords']['1']:'';
					}
					
					$longitude = $placemark['data']['longitude'];
					if($longitude == '') {
						$longitude = isset($placemark['coords']['0'])?$placemark['coords']['0']:'';
					}
					
					if($latitude == '' && $longitude =='') {
						if(isset($placemark['node']->MultiGeometry->LineString->coordinates))
						{
							$coordinates = trim($placemark['node']->MultiGeometry->LineString->coordinates);
							$coordinates_arr = explode(",",$coordinates);
							$lat_arr = preg_split('/(\s|\n)/', $coordinates_arr[1]);
							$long_arr = preg_split('/(\s|\n)/', $coordinates_arr[0]);
							$latitude = trim($lat_arr[0]);
							$longitude = trim($long_arr[0]);
						}
					}
					
					if($latitude != '' && $longitude != '')
					{
						$location = self::importCreateLocation($authorId, $routeId, $title, $type, $latitude, $longitude, $menuOrder, $placemark);
						unset($location);
					}
				} catch (\Exception $e) {
					self::debug($e->getMessage());
				}
				Location::clearInstances();
			}
		}
		else if (!empty($points)) {
			
			// Create starting location if no locations markers available
			if (Settings::getOption(Settings::OPTION_IMPORT_CREATE_START_LOCATION)) {
				$title = 'Start';
				$type = Location::TYPE_LOCATION;
				$menuOrder = 1;
				try {
					$location = self::importCreateLocation($authorId, $routeId, $title, $type,
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
					$location = self::importCreateLocation($authorId, $routeId, $title, $type,
						$lastCoord[0], $lastCoord[1], $menuOrder);
					unset($location);
				} catch (\Exception $e) {
					self::debug($e->getMessage());
				}
			}
			
		}
		
		self::setRouteParameters($xml, $routeData, $route);
		
	}
		
	static function debug($msg) {
		if (self::DEBUG_MODE) {
			var_dump($msg);
		}
	}
	
	static function debugFlush() {
		if (self::DEBUG_MODE) {
			ob_flush();
			flush();
		}
	}
	
	static protected function getDocPlacemarks(\SimpleXMLElement $xml) {
		$placemarks = $xml->xpath('//Placemark/styleUrl[contains(., "cmRouteLocation")]/..');
		if(empty($placemarks)) {
			$placemarks = $xml->xpath('//Placemark/styleUrl/..');
		}
		if(empty($placemarks)) {
			$placemarks = $xml->xpath('//Folder/Placemark');
		}
		foreach ($placemarks as &$placemark) {
			$ppc = trim($placemark->Point->coordinates);
			$placemark = array(
				'node' => $placemark,
				'data' => self::getPlacemarkData($placemark),
				'coords' => explode(',', $ppc),
			);
		}
		return $placemarks;
	}	
	
	static protected function getMultipleDocPath(\SimpleXMLElement $xml) {
		$result = array('type'=>'', 'data' => array());

		$lines = $xml->xpath('//Point');
		if(count($lines) > 0) {
			$result['type'] = 'Point';
		} else {
			$lines = $xml->xpath('//LineString');
			$result['type'] = 'LineString';
		}
	
		$outercounter = 0;
		foreach ($lines as $line) {
			if ($line->coordinates) {
				$row = trim($line->coordinates);
				$row_arr = preg_split('/\s/', (string)$row);
				$innercounter = 0;
				foreach ($row_arr as $rowline) {
					$coords_arr = explode(',', $rowline);
					if(isset($coords_arr[1])) {
						$result['data'][$outercounter][$innercounter][0] = $coords_arr[1];
						$result['data'][$outercounter][$innercounter][1] = $coords_arr[0];
						$innercounter++;
					}
				}
			}
			$outercounter++;
		}
		return $result;
	}

	static protected function getDocPath(\SimpleXMLElement $xml) {
		$result = array();
		$lines = $xml->xpath('//LineString');
		$counter = 0;
		foreach ($lines as $line) {
			if ($line->coordinates) {
				$result = array_merge($result, array_values(array_filter(array_map(function($row)
				{
					return explode(',', $row);
				}, array_values(array_filter(preg_split('/\s/', (string)$line->coordinates)))))));
			}
			$counter++;
		}
		return $result;
	}

	static protected function importCreateRoute($xml, array $routeData, $authorId, $altName) {
		
		$route = new Route();
		$name = (string)$xml->Document->name;
		$route->setTitle($name ? $name : $altName);
		$route->setContent((string)$xml->Document->description);
		$route->setStatus(empty($routeData['status']) ? 'draft' : $routeData['status']);
		$route->setAuthor($authorId);
		if (!empty($data['created'])) $route->setCreated($data['created']);
		if (!$route->save()) {
			throw new \Exception('Failed to save route.');
		}
			
		$route->setTravelMode(!empty($routeData['travelMode']) ? $routeData['travelMode'] : 'DIRECT');
		
		if (!empty($routeData['categories'])) {
			$route->setCategoriesByNames(explode(',', $routeData['categories']));
		}
		if (!empty($routeData['tags'])) {
			$route->setTags($routeData['tags']);
		}
		if (!empty($routeData['images'])) {
			self::importImages($route->getId(), $routeData['images']);
		}
		
		return $route;
	}
	
	static function setRouteParameters(\SimpleXMLElement $xml, array $routeData, Route $route) {
		
		$route->updateLocationsAltitudes();
		$route->determineElevationParams();
		
		if (!empty($routeData['distance'])) {
			$route->setDistance($routeData['distance']);
		} else {
			$dist = self::calculateDistance($xml);
			$route->setDistance($dist);
		}
		if (!empty($routeData['avgSpeed'])) {
			$route->setAvgSpeed($routeData['avgSpeed']);
		} else {
			$route->determineAvgSpeed();
		}
		if (!empty($routeData['duration'])) {
			$route->setDuration($routeData['duration']);
		} else {
			$route->determineDuration();
		}
		if (!empty($routeData['maxAltitude'])) {
			$route->setMaxElevation($routeData['maxAltitude']);
		} else {
			//$route->determineMaxElevation();
			$maxElevation = static::findElevationExtreme($xml, 'max');
			if (is_numeric($maxElevation)) {
				$route->setMaxElevation($maxElevation);
			}
		}
		if (!empty($routeData['minAltitude'])) {
			$route->setMinElevation($routeData['minAltitude']);
		} else {
			//$route->determineMinElevation();
			$minElevation = static::findElevationExtreme($xml, 'min');
			if (is_numeric($minElevation)) {
				$route->setMinElevation($minElevation);
			}
		}
		if (!empty($routeData['climb'])) {
			$route->setElevationGain($routeData['climb']);
		} else {
			//$route->determineElevationGain();
			$gain = static::findElevationDiff($xml, 'gain');
			if (is_numeric($gain)) {
				$route->setElevationGain($gain);
			}
		}
		if (!empty($routeData['descent'])) {
			$route->setElevationDescent($routeData['descent']);
		} else {
			//$route->determineElevationDescent();
			$descent = static::findElevationDiff($xml, 'descent');
			if (is_numeric($descent)) {
				$route->setElevationDescent($descent);
			}
		}
		if (!empty($routeData['overviewPath'])) {
			$route->setOverviewPath($routeData['overviewPath']);
		} else {
			//$route->recalculateOverviewPath();
		}
		if (!empty($routeData['pathColor'])) {
			$route->setPathColor($routeData['pathColor']);
		}
		if (!empty($routeData['slopeDownwardColor'])) {
			$route->setSlopeDownwardColor($routeData['slopeDownwardColor']);
		}
		if (!empty($routeData['slopeUpwardColor'])) {
			$route->setSlopeUpwardColor($routeData['slopeUpwardColor']);
		}
		if (!empty($routeData['useMinorLengthUnits'])) {
			$route->setMinorLengthUnits($routeData['useMinorLengthUnits']);
		}
		if (!empty($routeData['showWeatherPerLocation'])) {
			$route->setWeatherPerLocation($routeData['showWeatherPerLocation']);
		}
		
	}
	
	/**
	 * Calculate route's distance including elevation data if exists.
	 * 
	 * @param \SimpleXMLElement $xml
	 * @return number
	 */
	static function calculateDistance(\SimpleXMLElement $xml) {
		$coords = static::getDocPath($xml);
		$distance = 0;
		foreach ($coords as $i => $coord) {
			if ($i == 0) continue;
			$prev = $coords[$i-1];
			$elevation1 = $elevation2 = 0;
			if (isset($coord[2])) {
				$elevation1 = $coord[2];
			}
			if (isset($prev[2])) {
				$elevation2 = $prev[2];
			}
			$distance += Route::calculateDistance($coord[1], $coord[0], $prev[1], $prev[0], $elevation1, $elevation2);
		}
		return $distance;
	}
	
	static function findElevationExtreme(\SimpleXMLElement $xml, $find = 'max') {
		$coords = static::getDocPath($xml);
		$result = null;
		foreach ($coords as $i => $coord) {
			if (isset($coord[2])) {
				$elevation = $coord[2];
				if (is_null($result)) {
					$result = $elevation;
				}
				else if ($find == 'max' AND $result < $elevation) {
					$result = $elevation;
				}
				else if ($find == 'min' AND $result > $elevation) {
					$result = $elevation;
				}
			}
		}
		return $result;
	}
	
	static function findElevationDiff(\SimpleXMLElement $xml, $find = 'gain') {
		$coords = static::getDocPath($xml);
		$result = 0;
		foreach ($coords as $i => $coord) {
			if ($i == 0) continue;
			$prev = $coords[$i-1];
			if (isset($coord[2]) AND isset($prev[2])) {
				$diff = $coord[2] - $prev[2];
				if ($find == 'gain' AND $diff > 0) {
					$result += $diff;
				}
				else if ($find == 'descent' AND $diff < 0) {
					$result += $diff;
				}
			}
			//var_dump($diff .' --- ' . $result);
		}
		return abs($result);
	}
	
	static function importImages($postId, $images) {
		if (!is_array($images)) $images = explode(PHP_EOL, $images);
		$images = array_filter(array_map('trim', $images));
		foreach ($images as $imageUrl) {
			$fileName = explode('/', $imageUrl);
			$fileName = end($fileName);
			$fileTargetPath = self::getUploadDir(self::UPLOAD_DIR) . floor(microtime(true)*1000) . $fileName;
			$result = @file_put_contents($fileTargetPath, RemoteConnection::getRemoteFile($imageUrl));
			$ext = substr($fileName, strrpos($fileName, '.')+1, 5);
			$mimeType = 'image/'. $ext;
			if ($result) {
				$attachment = array(
					'guid'           => $fileTargetPath,
					'post_mime_type' => $mimeType,
					'post_title'     => sanitize_title($fileName),
					'post_content'   => '',
					'post_status'    => 'inherit',
					'post_type'		 => Attachment::POST_TYPE,
				);
				$attach_id = wp_insert_attachment($attachment, $fileTargetPath, $postId);
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				require_once(ABSPATH . 'wp-admin/includes/media.php');
				$attach_data = wp_generate_attachment_metadata($attach_id, $fileTargetPath);
				wp_update_attachment_metadata($attach_id, $attach_data);
			}
		}
	}
	
	public static function getUploadDir($name) {
		$uploadDir = wp_upload_dir();
		if ($uploadDir['error']) {
			throw new \Exception(__('Error while getting wp_upload_dir():' . $uploadDir['error']));
		} else {
			$dir = $uploadDir['basedir'] . '/' . static::UPLOAD_DIR . '/' . $name . '/';
			if(!is_dir($dir)) {
				if(!wp_mkdir_p($dir)) {
					throw new \Exception(__('Script couldn\'t create the upload folder:' . $dir));
				}
			}
			return $dir;
		}
	}
	
	static function getRouteData(\SimpleXMLElement $xml) {
		if ($xml->Document AND $xml->Document->ExtendedData AND $xml->Document->ExtendedData->Data) {
			return self::getExtendedData($xml->Document->ExtendedData->Data);
		} else {
			return array();
		}
	}

	static function getPlacemarkData(\SimpleXMLElement $placemark) {
		if ($placemark->ExtendedData AND $placemark->ExtendedData->Data) {
			return self::getExtendedData($placemark->ExtendedData->Data);
		} else {
			return array();
		}
	}
	
	static function getExtendedData(\SimpleXMLElement $xml) {
		$data = array();
		foreach ($xml as $node) {
			$data[(string)$node['name']] = (string)$node;
		}
		return $data;
	}
	
	static function importCreateLocation($authorId, $routeId, $title, $type, $lat, $long, $menuOrder, $placemark = null) {
		$location = new Location(array(
			'post_parent' => $routeId,
			'post_author' => $authorId,
			'post_type' => Location::POST_TYPE,
			'post_status' => 'inherit',
			'ping_status' => 'closed',
			'comment_status' => 'closed',
		));
		//var_dump($menuOrder);
		$location->setTitle($title);
		$location->setMenuOrder($menuOrder);
		if ($placemark AND $placemark['node']->description) {
			$location->setContent((string)$placemark['node']->description);
		}
		if ($locationId = $location->save()) {
			$location->setLat($lat);
			$location->setLong($long);
			$location->setLatLong($lat.','.$long);
			$location->setLocationType($type);
			
			if ($placemark AND !empty($placemark['data'])) {
				if (!empty($placemark['data']['address'])) $location->setAddress($placemark['data']['address']);
				if (!empty($placemark['data']['latitude'])) $location->setLat($placemark['data']['latitude']);
				if (!empty($placemark['data']['longitude'])) $location->setLong($placemark['data']['longitude']);

				if (!empty($placemark['data']['latitude']) && !empty($placemark['data']['longitude'])) {
					$location->setLatLong($placemark['data']['latitude'].','.$placemark['data']['longitude']);
				}
				
				if (!empty($placemark['data']['altitude'])) $location->setAltitude($placemark['data']['altitude']);
			}
			
		} else {
			return false;
		}
		return $location;
	}

	static function importReadfile($path, $fileName) {
		$content = '';
		if ('.kmz' == substr($fileName, -4, 4)) {
			$zip = new \ZipArchive();
			if ($zip->open($path)) {
				
				$fileName = null;
				for ($i=0; $i<$zip->numFiles; $i++) {
					$name = $zip->getNameIndex($i);
					if ('.kml' == substr($name, -4, 4)) {
						$fileName = $name;
						break;
					}
				}
				if ($fileName AND $fp = $zip->getStream($fileName)) {
					while (!feof($fp)) {
						$content .= fread($fp, 2);
					}
					fclose($fp);
				}
				
			}
			$zip->close();
			
		} else {
			$content = file_get_contents($path);
		}
		
		if (!empty($content)) {
			return $content;
		}
		
	}
	
	static protected function getPlacemark(Location $location, $style) {
		
		$imagesUrls = array_map(function(Attachment $image) {
			if ($image->isImage()) {
				return $image->getImageUrl(Attachment::IMAGE_SIZE_FULL);
			} else {
				return $image->getUrl();
			}
		}, $location->getImages());
		
		return '<Placemark>
			<styleUrl>#cmRouteLocation'. $style .'Style</styleUrl>
			<name>'. htmlspecialchars($location->getTitle()) .'</name>
			<description><![CDATA['. $location->getContent() .']]></description>
			<ExtendedData>
				<Data name="address">'. htmlspecialchars($location->getAddress()) .'</Data>
				<Data name="created">'. htmlspecialchars($location->getCreatedDate()) .'</Data>
				<Data name="modified">'. htmlspecialchars($location->getModifiedDate()) .'</Data>
				<Data name="authorId">'. htmlspecialchars($location->getAuthorId()) .'</Data>
				<Data name="authorEmail">'. htmlspecialchars($location->getAuthorEmail()) .'</Data>
				<Data name="authorName">'. htmlspecialchars($location->getAuthorDisplayName()) .'</Data>
				<Data name="locationType">'. htmlspecialchars($location->getLocationType()) .'</Data>
				<Data name="status">'. htmlspecialchars($location->getStatus()) .'</Data>
				<Data name="menuOrder">'. htmlspecialchars($location->getMenuOrder()) .'</Data>
				<Data name="longitude">'. htmlspecialchars($location->getLong()) .'</Data>
				<Data name="latitude">'. htmlspecialchars($location->getLat()) .'</Data>
				<Data name="altitude">'. htmlspecialchars($location->getAltitude()) .'</Data>
				<Data name="images"><![CDATA['. implode(PHP_EOL, $imagesUrls) .']]></Data>
			</ExtendedData>
			<Point>
				<coordinates>'. $location->getLong() .','. $location->getLat() .','. $location->getAltitude() .'</coordinates>
			</Point>
		</Placemark>';
	}
	
	static protected function getRouteExtendedData(Route $route) {
		$out = '';
		$data = array(
			'distance' => $route->getDistance(),
			'duration' => $route->getDuration(),
			'avgSpeed' => $route->getAvgSpeed(),
			'minAltitude' => $route->getMinElevation(),
			'maxAltitude' => $route->getMaxElevation(),
			'climb' => $route->getElevationGain(),
			'descent' => $route->getElevationDescent(),
			'categories' => implode(',', $route->getCategories(Category::FIELDS_NAMES)),
			'tags' => implode(',', $route->getTags(Category::FIELDS_NAMES)),
			'created' => $route->getCreatedDate(),
			'modified' => $route->getModifiedDate(),
			'overviewPath' => $route->getOverviewPath(),
			'pathColor' => $route->getPathColor(),
			'slopeDownwardColor' => $route->getSlopeDownwardColor(),
			'slopeUpwardColor' => $route->getSlopeUpwardColor(),
			'permalink' => $route->getPermalink(),
			'status' => $route->getStatus(),
			'travelMode' => $route->getTravelMode(),
			'slug' => $route->getSLug(),
			'useMinorLengthUnits' => $route->useMinorLengthUnits() ? 1 : 0,
			'showWeatherPerLocation' => $route->showWeatherPerLocation() ? 1 : 0,
		);
		foreach ($data as $key => $val) {
			$out .= '<Data name="'. htmlspecialchars($key) .'">'. htmlspecialchars($val) .'</Data>' . PHP_EOL;
		}
		
		$imagesUrls = array_map(function(Attachment $image) {
			return $image->getImageUrl(Attachment::IMAGE_SIZE_FULL);
		}, $route->getImages());
		$out .= '<Data name="images"><![CDATA['. implode(PHP_EOL, $imagesUrls) .']]></Data>';
		
		return $out;
		
	}
	
	static function decodePolylineToArray($encoded) {
		$length = strlen($encoded);
		$index = 0;
		$points = array();
		$lat = 0;
		$lng = 0;

		while ($index < $length) {
			$b = 0;
			$shift = 0;
			$result = 0;
			do {
				$b = ord(substr($encoded, $index++)) - 63;
				$result |= ($b & 0x1f) << $shift;
				$shift += 5;
			} while ($b >= 0x20);
			$dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
			$lat += $dlat;
			$shift = 0;
			$result = 0;
			do {
				$b = ord(substr($encoded, $index++)) - 63;
				$result |= ($b & 0x1f) << $shift;
				$shift += 5;
			} while ($b >= 0x20);
			$dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
			$lng += $dlng;
			$points[] = array($lat * 1e-5, $lng * 1e-5);
		}
		return $points;
	}
	
	static function kmzListFiles(\ZipArchive $zip, $ext = 'kml') {
		$files = array();
		$c = strlen($ext)+1;
		for ($i=0; $i<$zip->numFiles; $i++) {
			$name = $zip->getNameIndex($i);
			if (is_null($ext) OR '.'. $ext == substr($name, -$c, $c)) {
				$files[] = $name;
			}
		}
		return $files;
	}
	
	static function kmzGetSource(\ZipArchive $zip, $fileName) {
		$content = '';
		if ($fp = $zip->getStream($fileName)) {
			while (!feof($fp)) {
				$content .= fread($fp, 2);
			}
			fclose($fp);
		}
		return $content;
	}

}