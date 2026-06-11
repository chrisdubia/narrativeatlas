<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\helper\KmlHelper;
use com\cminds\mapsroutesmanager\helper\PolylineEncoder;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\App;

class UpdateController extends Controller {
	
	const OPTION_NAME = 'cmmrm_update_methods';
	
	static $actions = array('plugins_loaded');

	static function plugins_loaded() {
		global $wpdb;
		
		if (class_exists('DOING_AJAX') && DOING_AJAX) return;
		
		//static::update_3_2_0_update_route_approx_coords();
		
		$updates = get_option(self::OPTION_NAME);
		if (empty($updates)) $updates = array();
		$count = count($updates);
		
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (preg_match('/^update((_[0-9]+)+)/', $method, $match)) {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		
		//static::update_2_3_0_show_locations_section();
		
		if ($count != count($updates)) {
			update_option(self::OPTION_NAME, $updates);
		}
		
		if ($action = filter_input(INPUT_GET, 'cmmrm-action') AND md5($action . 'cmmrm') == 'd5ef9a1543e2efe7b185135d6220deb2') {
			static::update_2_0_0_optimization();
		}
		
	}
	
	
	static function update_1_0_8() {
		global $wpdb;
	
		// Update Route's postmeta views
		$routesIds = $wpdb->get_col($wpdb->prepare("SELECT route.ID FROM $wpdb->posts route
			LEFT JOIN $wpdb->postmeta m ON m.post_id = route.ID AND m.meta_key = %s
			WHERE route.post_type = %s AND (m.meta_value IS NULL OR m.meta_value = '')",
			Route::META_VIEWS, Route::POST_TYPE));
	
		foreach ($routesIds as $id) {
			if ($route = Route::getInstance($id)) {
				$route->setViews(0);
			}
			unset($route);
			Route::clearInstances();
		}
		
	}
	
	
	static function update_1_0_8_route_comment_status() {
		global $wpdb;
		
		// Update routes comment status
		$routesIds = $wpdb->get_col($wpdb->prepare("SELECT route.ID FROM $wpdb->posts route
			WHERE route.post_type = %s",
			Route::POST_TYPE));
		foreach ($routesIds as $id) {
			if ($route = Route::getInstance($id)) {
				$route->setCommentStatus('open');
				$route->save();
			}
			unset($route);
			Route::clearInstances();
		}
		
	}
	
	
	static function update_1_1_8_instructions() {
		$val = get_option(Settings::OPTION_LABEL_EDITOR_INSTRUCTION);
		if (strpos($val, '161036537') === false) {
			$val = '<iframe src="https://player.vimeo.com/video/161036537" width="500" height="281" frameborder="0" '
					. 'webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' . $val;
			update_option(Settings::OPTION_LABEL_EDITOR_INSTRUCTION, $val);
		}
	}
	
	
	static function update_2_0_0_optimization() {
		global $wpdb;
		$waypoints = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_parent, p.menu_order, lat.meta_value AS lat, lng.meta_value AS lng
			FROM $wpdb->posts p
			JOIN $wpdb->postmeta lat ON p.ID = lat.post_id AND lat.meta_key = %s 
			JOIN $wpdb->postmeta lng ON p.ID = lng.post_id AND lng.meta_key = %s
			WHERE p.post_type = %s
			ORDER BY p.post_parent ASC, p.menu_order ASC",
			Location::META_LAT,
			Location::META_LONG,
			Location::POST_TYPE
		), ARRAY_A);
		//echo '<pre>';var_dump($waypoints);exit;
		$result = array();
		foreach ($waypoints as $waypoint) {
			$result[$waypoint['post_parent']][] = array($waypoint['lat'], $waypoint['lng']);
		}
		//var_dump($result);exit;

		foreach ($result as $routeId => $coords) {
			if ($route = Route::getInstance($routeId)) {
				//$route->setWaypoints($coords);
				
				// Set waypoints
				$polyline = new PolylineEncoder();
				$r = $polyline->encode($coords);
				if (!empty($r->rawPoints)) {
					$route->setWaypointsString($r->rawPoints);
				}
				
				// Reduce points and set overview path
				$overviewPath = $route->getOverviewPath();
				if (empty($overviewPath)) {
					$reducedPoints = KmlHelper::reducePointsNumber($coords, 300);
					$r = $polyline->encode($reducedPoints);
					if (!empty($r->rawPoints)) {
						$route->setOverviewPath($r->rawPoints);
					}
				}
				
			}
		}
	}
	
	
	static function update_2_0_7() {
		// Force to use new defaults
		update_option(Settings::OPTION_LABEL_EDITOR_INSTRUCTION, null);
	}
	
	
	static function update_2_1_2_instructions() {
		static::update_1_1_8_instructions();
	}
	
	
	static function update_2_3_0_show_locations_section() {
		global $wpdb;
		$ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", Route::POST_TYPE));
		foreach ($ids as $id) {
			add_post_meta($id, Route::META_SHOW_LOCATIONS_SECTION, '1', $unique = true);
		}
	}
	
	
	static function update_3_2_0_update_route_rating_cache() {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", Route::POST_TYPE);
		$ids = $wpdb->get_col($sql);
		foreach ($ids as $id) {
			$rating = $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value)/COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
				$id,
				Route::META_RATE
			));
			$rating = intval($rating);
			update_post_meta($id, Route::META_RATING_CACHE, $rating);
		}
	}
	
	
	static function update_3_2_0_update_route_approx_coords() {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT route.ID AS id, rpath.meta_value AS overview_path, llat.meta_value AS location_lat, llong.meta_value AS location_long
			FROM $wpdb->posts route
			LEFT JOIN $wpdb->postmeta rpath ON rpath.post_id = route.ID AND rpath.meta_key = %s
			LEFT JOIN $wpdb->posts rloc ON rloc.post_parent = route.ID AND rloc.post_type = %s AND rloc.menu_order = 1
			LEFT JOIN $wpdb->postmeta llat ON llat.post_id = rloc.ID AND llat.meta_key = %s
			LEFT JOIN $wpdb->postmeta llong ON llong.post_id = rloc.ID AND llong.meta_key = %s
				WHERE route.post_type = %s", Route::META_OVERVIEW_PATH, Location::POST_TYPE, Location::META_LAT, Location::META_LONG, Route::POST_TYPE);
		$routes = $wpdb->get_results($sql, ARRAY_A);
		//var_dump($routes);exit;
		foreach ($routes as $route) {
			$id = $route['id'];
			if (!is_null($route['location_lat']) AND !is_null($route['location_long'])) {
				//$wpdb->insert($wpdb->postmeta, array('post_id' => $id, 'meta_key' => Route::META_APPROX_LATITUDE, 'meta_value' => $route['location_lat']));
				//$wpdb->insert($wpdb->postmeta, array('post_id' => $id, 'meta_key' => Route::META_APPROX_LONGITUDE, 'meta_value' => $route['location_long']));
				//var_dump('updated ' . $id . ' with location');
				update_post_meta($id, Route::META_APPROX_LATITUDE, $route['location_lat']);
				update_post_meta($id, Route::META_APPROX_LONGITUDE, $route['location_long']);
				update_post_meta($id, 'geo_latitude', $route['location_lat']);
				update_post_meta($id, 'geo_longitude', $route['location_long']);
			}
			else if (!empty($route['overview_path'])) {
				$encoder = new PolylineEncoder();
				$points = $encoder->decodePolylineToArray($route['overview_path']);
				if (is_array($points) AND count($points) > 0) {
					$coords = reset($points);
					//$wpdb->insert($wpdb->postmeta, array('post_id' => $id, 'meta_key' => Route::META_APPROX_LATITUDE, 'meta_value' => $coords[0]));
					//$wpdb->insert($wpdb->postmeta, array('post_id' => $id, 'meta_key' => Route::META_APPROX_LONGITUDE, 'meta_value' => $coords[1]));
					update_post_meta($id, Route::META_APPROX_LATITUDE, $coords[0]);
					update_post_meta($id, Route::META_APPROX_LONGITUDE, $coords[1]);
					update_post_meta($id, 'geo_latitude', $coords[0]);
					update_post_meta($id, 'geo_longitude', $coords[1]);
					//var_dump('updated ' . $id . ' with path');
				}
			} else {
				//var_dump('no coords  ' . $id);
			}
		}
	}
	
}