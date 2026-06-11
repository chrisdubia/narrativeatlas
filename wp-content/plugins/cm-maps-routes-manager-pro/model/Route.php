<?php
namespace com\cminds\mapsroutesmanager\model;

use com\cminds\mapsroutesmanager\helper\Polyline;
use com\cminds\mapsroutesmanager\helper\GpxHelper;
use com\cminds\mapsroutesmanager\helper\KmlHelper;
use com\cminds\mapsroutesmanager\helper\PolylineEncoder;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\controller\DashboardController;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\helper\RemoteConnection;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\model\MapLocationObject;
use com\cminds\mapsroutesmanager\controller\TecController;

class Route extends PostType {
	
	const POST_TYPE = 'cmmrm_route';
	
	const META_RATING_CACHE = '_cmmrm_route_rating_cache'; // overall rating cache
	const META_RATE = '_cmmrm_route_rate'; // single rate
	const META_RATE_USER_ID = '_cmmrm_route_rate_user_id';
	const META_RATE_TIME = '_cmmrm_route_rate_time';
	const META_VIEWS = '_cmmrm_views';
	
	const META_DISTANCE = '_cmmrm_distance'; // in meters
	const META_DURATION = '_cmmrm_duration';
	const META_AVG_SPEED = '_cmmrm_avg_speed';
	const META_MAX_ELEVATION = '_cmmrm_max_elevation';
	const META_MIN_ELEVATION = '_cmmrm_min_elevation';
	const META_ELEVATION_GAIN = '_cmmrm_elevation_gain';
	const META_ELEVATION_DESCENT = '_cmmrm_elevation_descent';
	const META_DIRECTIONS_RESPONSE = '_cmmrm_directions_response';
	const META_ELEVATION_RESPONSE = '_cmmrm_elevation_response';
	const META_TRAVEL_MODE = '_cmmrm_travel_mode';
	const META_USE_BUDDYPRESS_COLLABORATIVE = '_cmmrm_use_buddypress_collaborative';
	const META_USE_MINOR_LENGTH_UNITS = '_cmmrm_use_minor_length_units';
	const META_SHOW_DIRECTIONAL_ARROWS = 'cmmrm_use_directional_arrows';
	const META_SHOW_LOCATIONS_SECTION = 'cmmrm_show_locations_section';
	const META_SHOW_WEATHER_PER_LOCATION = '_cmmrm_show_weather_per_location';
	const META_PATH_COLOR = '_cmmrm_path_color';
	const META_CTA_BUTTON_TEXT = '_cmmrm_cta_button_text';
	const META_CTA_BUTTON_URL = '_cmmrm_cta_button_url';
	const META_OSMTILES = '_cmmrm_osm_tiles';
	const META_OVERVIEW_PATH = '_cmmrm_overview_path';
	const META_WAYPOINTS = '_cmmrm_waypoints';
	const META_WAYPOINTS_STRING = '_cmmrm_waypoints_string';
	const META_MODERATOR_ACCEPTED = '_cmmrm_moderator_accepted';
	const META_SHOW_PATH_OUTLINE = 'cmmrm_show_path_outline';
	const META_HIDE_ON_INDEX = 'cmmrm_hide_on_index';
	const META_APPROX_LATITUDE = 'cmmrm_approx_latitude';
	const META_APPROX_LONGITUDE = 'cmmrm_approx_longitude';
	const META_SLOPES_SHOW = 'cmmrm_slopes_show';
	const META_SLOPE_MIN_VALUE = 'cmmrm_slope_min_value';
	const META_SLOPE_MIN_WIDTH = 'cmmrm_slope_min_width';
	const META_SLOPE_DOWNWARD_COLOR = 'cmmrm_slope_downward_color';
	const META_SLOPE_UPWARD_COLOR = 'cmmrm_slope_upward_color';
	
	const WAYPOINTS_LIMIT = 512;
	
	const DEFAULT_TRAVEL_MODE = 'DIRECT';
	
	const TRANSIENT_GEOLOCATION_BY_ADDR_CACHE = 'cmmrm_geoloc_by_addr_cache';
	
	static $travelModes = array(
		Settings::TRAVEL_MODE_WALKING,
		Settings::TRAVEL_MODE_BICYCLING,
		Settings::TRAVEL_MODE_DRIVING,
		Settings::TRAVEL_MODE_DIRECT,
	);
	
	static protected $postTypeOptions = array(
		'label' => 'Route',
		'public' => true,
		'show_in_rest' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_admin_bar' => true,
		'show_in_menu' => App::PREFIX,
		'hierarchical' => false,
		'supports' => array('title', 'editor', 'author', 'excerpt', 'thumbnail'),
		'has_archive' => true,
		'with_front' => false,
	);
	
	static protected function getPostTypeLabels() {
		$singular = ucfirst(Labels::getLocalized('route'));
		$plural = ucfirst(Labels::getLocalized('routes'));
		return array(
			'name' => $plural,
            'singular_name' => $singular,
            'add_new' => sprintf(__('Add %s', App::SLUG), $singular),
            'add_new_item' => sprintf(__('Add New %s', App::SLUG), $singular),
            'edit_item' => sprintf(__('Edit %s', App::SLUG), $singular),
            'new_item' => sprintf(__('New %s', App::SLUG), $singular),
            'all_items' => $plural,
            'view_item' => sprintf(__('View %s', App::SLUG), $singular),
            'search_items' => sprintf(__('Search %s', App::SLUG), $plural),
            'not_found' => sprintf(__('No %s found', App::SLUG), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', App::SLUG), $plural),
            'menu_name' => App::getPluginName()
		);
	}
	
	static function init() {
		/*
 		if ($pageId = Settings::getOption(Settings::OPTION_PAGE_ROUTE_INDEX) AND $page = get_post($pageId)) {
 			$slug = $page->post_name;
 		} else {
 			$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX);
 		}
		*/
		$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX);
		static::$postTypeOptions['with_front'] = Settings::getOption(Settings::OPTION_REWRITE_WITH_FRONT);
		static::$postTypeOptions['rewrite'] = array(
			'slug' => $slug,
			'with_front' => static::$postTypeOptions['with_front'],
		);
		static::$postTypeOptions['exclude_from_search'] = Settings::getOption(Settings::OPTION_EXCLUDE_FROM_SEARCH);
		if (App::isPro()) {
			static::$postTypeOptions['taxonomies'] = apply_filters('cmmrm_route_post_type_taxonomies', array(Category::TAXONOMY, RouteTag::TAXONOMY));
		}
		parent::init();
		//echo '<pre>';var_dump(get_post_type_object(static::POST_TYPE));exit;
	}
	
	/**
	 * Get instance
	 * 
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\mapsroutesmanager\model\Route
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	function getEditUrl() {
		return admin_url(sprintf('post.php?action=edit&post=%d',
			$this->getId()
		));
	}
	
	function getCategories($fields = TaxonomyTerm::FIELDS_MODEL, $params = array()) {
		return Category::getPostTerms($this->getId(), $fields, $params);
	}
	
	function getTags($fields = TaxonomyTerm::FIELDS_MODEL, $params = array()) {
		return RouteTag::getPostTerms($this->getId(), $fields, $params);
	}
	
	function setCategories($categoriesIds) {
		return wp_set_post_terms($this->getId(), $categoriesIds, Category::TAXONOMY, $append = false);
	}
	
	function setCategoriesByNames(array $categoriesNames) {
		$existingCategories = \get_terms(Category::TAXONOMY, array('name' => $categoriesNames, 'fields' => Category::FIELDS_ID_NAME, 'hide_empty' => 0));
		$existingCategoriesIds = array_keys($existingCategories);
		$notExisting = array_diff($categoriesNames, $existingCategories);
		foreach ($notExisting as $name) {
			$term = \wp_create_term($name, Category::TAXONOMY);
			if (!\is_wp_error($term)) {
				$existingCategoriesIds[] = (is_array($term) ? $term['term_id'] : (is_numeric($term) ? $term : 0));
			}
		}
		$existingCategoriesIds = array_filter($existingCategoriesIds);
		return \wp_set_post_terms($this->getId(), $existingCategoriesIds, Category::TAXONOMY, $append = false);
	}
	
	function addDefaultCategory() {
		$term = get_term('General', Category::TAXONOMY);
		if (empty($term)) {
			$terms = get_terms(array(Category::TAXONOMY), array('hide_empty' => false));
			if (!empty($terms)) {
				$term = reset($terms);
			}
		}
		if (!empty($term)) {
			wp_set_post_terms($this->getId(), $term->term_id, Category::TAXONOMY);
		}
	}
	
	function getUserEditUrl() {
		return RouteController::getDashboardUrl('edit', array('id' => $this->getId()));
	}
	
	function getUserDeleteUrl() {
		return RouteController::getDashboardUrl('delete', array(
			'id' => $this->getId(),
			'nonce' => wp_create_nonce(DashboardController::DELETE_NONCE),
		));
	}
	
	function getImages() {
		if ($id = $this->getId()) {
			return array_values(array_filter(Attachment::getForPost($id), function($image) { return ($image->isImage() OR $image->isVideo()); }));
		} else {
			return array();
		}
	}

	function getRouteFirstImageSrc() {
		$images = $this->getImages();
		if ($image = reset($images)) {
			return $image->getImageUrl(Attachment::IMAGE_SIZE_LARGE);
		}
	}
	
	function getRouteFirstImage() {
		if ($src = $this->getRouteFirstImageSrc()) {
			return sprintf('<img src="%s" class="cmmrm-location-image-%d" />', esc_attr($src), $this->getId());
		}
	}
	
	function getMapThumbUrl($size) {
		$color = '0x' . preg_replace('~[^0-9A-F]~i', '', $this->getPathColor());
		//$pathParams = array('weight' => 3, 'color' => $color, 'enc' => $this->getWaypointsString());
		$pathParams = array('weight' => 3, 'color' => $color, 'enc' => $this->getOverviewPath());
		foreach ($pathParams as $name => &$val) {
			$val = $name .':'. $val;
		}
		$pathParams = implode('|', $pathParams);
		return add_query_arg(urlencode_deep(array(
			'size' => $size,
			'maptype' => (Settings::getOption(Settings::OPTION_MAP_TYPE_DEFAULT) !='')?Settings::getOption(Settings::OPTION_MAP_TYPE_DEFAULT):'roadmap',
			'key' => Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY),
			'path' => $pathParams,
		)), 'https://maps.googleapis.com/maps/api/staticmap');
	}
	
	function getImagesIds() {
		if ($id = $this->getId()) {
			return get_posts(array(
				'posts_per_page' => -1,
				'post_type' => Attachment::POST_TYPE,
				'post_status' => 'any',
				'post_parent' => $id,
				'fields' => 'ids',
				'orderby' => 'menu_order',
				'order' => 'asc',
			));
		} else {
			return array();
		}
	}
	
	function setImages($images) {
		global $wpdb;
		
		if (!is_array($images)) {
			$images = array_filter(explode(',', $images));
		}
		
		$currentIds = $this->getImagesIds();
		$postedImagesIds = array_filter(array_map('intval', array_map('trim', $images)));
		
		$toAdd = array_diff($postedImagesIds, $currentIds);
		$toDelete = array_diff($currentIds, $postedImagesIds);
		
		if ($originalImportedFile = $this->getOriginalImportFile()) {
			$toDelete = array_diff($toDelete, array($originalImportedFile->getId()));
		}
		
		if (!empty($toAdd)) {
			$toAdd_sql = "UPDATE $wpdb->posts SET post_parent = ". intval($this->getId()) ." WHERE ID IN (" . implode(',', $toAdd) . ")";
			$wpdb->query($toAdd_sql);
		}
		if (!empty($toDelete)) {
			$toDelete_sql = "UPDATE $wpdb->posts SET post_parent = 0 WHERE ID IN (" . implode(',', $toDelete) . ")";
			$wpdb->query($toDelete_sql);
		}
		
		// Change the sorting order
		foreach ($images as $i => $id) {
			$wpdb->query("UPDATE $wpdb->posts SET menu_order = ". intval($i+1) ." WHERE ID = ". intval($id) ." LIMIT 1");
		}
		
	}
	
	function getLocationsIds() {
		if ($id = $this->getId()) {
			return get_posts(array(
				'fields' => 'ids',
				'post_type' => Location::POST_TYPE,
				'post_parent' => $id,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'asc',
			));
		} else return array();
	}
	
	function getLocations($location_type = Location::POST_TYPE) {
		if ($id = $this->getId()) {
			$posts = get_posts(array(
				'post_type' => $location_type,
				'post_parent' => $id,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'asc',
				'meta_key' => Location::META_LOCATION_TYPE,
				'meta_value' => Location::TYPE_LOCATION,
			));
			return array_map(array(App::namespaced('model\Location'), 'getInstance'), $posts);
		} else return array();
	}

	function getJSRouteData() {
		return apply_filters('cmmrm_modify_js_route_data', array(
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'travelMode' => $this->getTravelMode(),
			'overviewPath' => $this->getOverviewPath(),
			//'waypoints' => $this->getWaypoints(),
			'pathColor' => $this->getPathColor(),
			'slopeDownwardColor' => $this->getSlopeDownwardColor(),
			'slopeUpwardColor' => $this->getSlopeUpwardColor(),
			'showDirectionalArrows' => ($this->showDirectionalArrows() ? true : false),
			//'locations' => $this->getJSLocations(),
			'distance' => $this->getDistance(),
			'duration' => $this->getDuration(),
			'avgSpeed' => $this->getAvgSpeed(),
			'minElevation' => $this->getMinElevation(),
			'maxElevation' => $this->getMaxElevation(),
			'elevationGain' => $this->getElevationGain(),
			'elevationDescent' => $this->getElevationDescent(),
			'showPathOutline' => $this->showPathOutline(),
			'hideOnIndex' => $this->hideOnIndex(),
			'isSlopesShowingEnabled' => $this->isSlopesShowingEnabled(),
			'slopeMinValue' => $this->getSlopeMinValue(),
			'slopeMinWidth' => $this->getSlopeMinWidth(),
		), $this);
	}
	
	function getJSLocations($location_type = Location::POST_TYPE) {
		return apply_filters('cmmrm_modify_js_locations_arr', array_map(function(Location $location) {
			return array(
				'id' => $location->getId(),
				'name' => $location->getTitle(),
				'lat' => $location->getLat(),
				'lng' => $location->getLong(),
				'description' => $location->getContent(),
				'type' => $location->getLocationType(),
				'address' => $location->getAddress(),
				'linktext' => $location->getLinktext(),
				'linkurl' => $location->getLinkurl(),
				'distance' => $location->getDistance(),
				'icon' => $location->getIcon(),
				'iconSize' => $location->getIconSize(),
				'images' => array_map(function(Attachment $image) {
					return array(
						'id' => $image->getId(),
						'url' => $image->getImageUrl(Attachment::IMAGE_SIZE_FULL),
						'thumb' => $image->getImageUrl(Attachment::IMAGE_SIZE_THUMB)
					);
				}, $location->getImages()),
				'certbgimage' => array_map(function(Attachment $image) {
					return array(
						'id' => $image->getId(),
						'url' => $image->getImageUrl(Attachment::IMAGE_SIZE_FULL),
						'thumb' => $image->getImageUrl(Attachment::IMAGE_SIZE_THUMB)
					);
				}, $location->getCertbgimageids()),
				'infoWindowContent' => (App::isPro() ? $location->getInfoWindowContent() : ''),
				'infoWindowOpen' => $location->getInfoWindowOpen(),
				'generateWazeButton' => $location->getGenerateWazeButton(),
			);
		}, $this->getLocations($location_type)));
	}
	
	static function getIndexMapJSLocations(\WP_Query $query) {
		global $wpdb;
		
		$the_events_calendar_integration_enable = Settings::getOption(Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE);
		if($the_events_calendar_integration_enable) {
			$tec = "";
			if (isset($_GET['tec'])) {
				$tec = filter_input(INPUT_GET, 'tec');
			}
		}

		$locQuery = new \WP_Query(array_merge($query->query, array(
			'post_type' => Route::POST_TYPE,
			'fields' => 'ids',
			'posts_per_page' => -1,
		)));
		$postsIds = $locQuery->get_posts();

		if(is_plugin_active('cm-maps-routes-buddypress-integration/cm-maps-routes-buddypress-integration.php') || is_plugin_active('cm-maps-routes-buddypress-addon/cm-maps-routes-buddypress-integration.php')) {
			
			if (function_exists('groups_get_user_groups')) {

				$collaborative_routes = get_posts(array(
					'author' => -get_current_user_id(),
					'post_type' => Route::POST_TYPE,
					'posts_per_page' => -1,
					'post_status' => 'publish',
					'meta_key'   => '_cmmrm_use_buddypress_collaborative',
					'meta_value' => '1'
				));

				$current_user_groups_arr = groups_get_user_groups(get_current_user_id());
				$current_user_groups = $current_user_groups_arr['groups'];
				$current_user_total = $current_user_groups_arr['total'];
				
				$unique_routes = array();
				if($current_user_total > 0) {
					foreach($current_user_groups as $current_user_group_id) {
						foreach($collaborative_routes as $croute) {
							$route_bp_groups = get_post_meta($croute->ID, '_cmmrm_bp_groups', true);
							if($route_bp_groups) {
								if(is_array($route_bp_groups) && count($route_bp_groups) > 0) {
									if(in_array($current_user_group_id, $route_bp_groups)) {
										if(!in_array($croute->ID, $unique_routes)) {
											$postsIds[] = $croute->ID;
											$unique_routes[] = $croute->ID;
										}
									}
								}
							}
						}
					}
				}

			}

		}
		
		if (empty($postsIds)) {
			return array();
		}
		
		//if(Settings::getOption(Settings::OPTION_INDEX_MAP_FULL_ROUTE_ENABLE) == '1') {
			$sql = $wpdb->prepare("SELECT
					# get only some of the columns on the index page
					r.ID,
					r.post_title,
					r.post_date,
					r.post_author,
					r.post_type,
					r.post_status,
					r.post_title AS name,
					lm_lat.meta_value AS lat,
					lm_lon.meta_value AS `long`,
					rm_pc.meta_value AS `pathColor`,
					rm_op.meta_value AS `overviewPath`,
					rm_op.meta_value AS `waypointsString` # waypoints_string is not needed on the index page so replaced with overview_path
				FROM $wpdb->posts r
				LEFT JOIN $wpdb->posts l ON l.post_parent = r.ID AND l.post_type = %s AND l.menu_order = 1
				LEFT JOIN $wpdb->postmeta lm_lat ON lm_lat.post_id = l.ID AND lm_lat.meta_key = %s
				LEFT JOIN $wpdb->postmeta lm_lon ON lm_lon.post_id = l.ID AND lm_lon.meta_key = %s
				LEFT JOIN $wpdb->postmeta rm_pc ON rm_pc.post_id = r.ID AND rm_pc.meta_key = %s
				LEFT JOIN $wpdb->postmeta rm_op ON rm_op.post_id = r.ID AND rm_op.meta_key = %s
				# LEFT JOIN $wpdb->postmeta rm_ws ON rm_ws.post_id = r.ID AND rm_ws.meta_key = %s
				WHERE r.ID IN (" . implode(',', $postsIds) . ")
				",
				Location::POST_TYPE,
				Location::META_LAT,
				Location::META_LONG,
				Route::META_PATH_COLOR,
				Route::META_OVERVIEW_PATH,
				Route::META_WAYPOINTS_STRING
			);
		/*
		} else {
			$sql = $wpdb->prepare("SELECT
					# get only some of the columns on the index page
					r.ID,
					r.post_title,
					r.post_date,
					r.post_author,
					r.post_type,
					r.post_status,
					r.post_title AS name,
					lm_lat.meta_value AS lat,
					lm_lon.meta_value AS `long`
				FROM $wpdb->posts r
				LEFT JOIN $wpdb->posts l ON l.post_parent = r.ID AND l.post_type = %s AND l.menu_order = 1
				LEFT JOIN $wpdb->postmeta lm_lat ON lm_lat.post_id = l.ID AND lm_lat.meta_key = %s
				LEFT JOIN $wpdb->postmeta lm_lon ON lm_lon.post_id = l.ID AND lm_lon.meta_key = %s
				WHERE r.ID IN (" . implode(',', $postsIds) . ")
				",
				Location::POST_TYPE,
				Location::META_LAT,
				Location::META_LONG
			);
		}
		*/
		
		//var_dump($sql);
		
		$routes = $wpdb->get_results($sql, ARRAY_A);
		
		foreach ($routes as $i => &$row) {
			/* @var $route Route */
			$route = new Route($row);

			if($the_events_calendar_integration_enable) {
				if($tec == '1') {
					$saved_events = TecController::getSavedEvents($route->getId());
					if(count($saved_events) == 0) {
						unset($routes[$i]);
						continue;
					}
				}
			}

			if($route->hideOnIndex() == '1') {
				unset($routes[$i]);
				continue;
			}

			//if(Settings::getOption(Settings::OPTION_INDEX_MAP_FULL_ROUTE_ENABLE) == '1') {
				$routes[$i]['waypointsString'] = $route->getWaypointsString();
			//} else {
			//	$routes[$i]['waypointsString'] = '';
			//}
			$routes[$i]['permalink'] = $route->getPermalink();
			$routes[$i]['type'] = Location::TYPE_LOCATION;
			$routes[$i]['icon'] = $route->getFirstLocationIcon();
			//if(Settings::getIndexMapMarkerClick() == 'tooltip') {
				$routes[$i]['infoContent'] = $route->getFirstLocationInfoN();
			//} else {
			//	$routes[$i]['infoContent'] = '';
			//}
			$routes[$i]['shape_fill_color'] = '';
			$routes[$i]['shape_fill_opacity'] = '';
			$routes[$i]['shape_stroke_color'] = '';
			$routes[$i]['shape_stroke_opacity'] = '';
			$routes[$i]['shape_stroke_weight'] = '';
			$routes[$i]['shape_type'] = '';
			$routes[$i]['shape_polygon_coords'] = '';
			$routes[$i]['shape_circle_center'] = '';
			$routes[$i]['shape_circle_radius'] = '';
			$routes[$i]['shape_rectangle_bounds'] = '';
			$routes[$i]['user_track'] = '';
			$routes[$i]['user_track_all'] = '';

			$startingPoint = $route->getStartingPointCoords();
			if ($startingPoint) {
				$row['lat'] = $startingPoint[0];
				$row['long'] = $startingPoint[1];
			}
			//if (Settings::getOption(Settings::OPTION_INDEX_MAP_STARTING_POINT_MARKER == Settings::STARTING_POINT_LOCATIONS)) {
				$routes[$i]['markers'] = $route->getJSLocations();
			//} else {
			//	$routes[$i]['markers'] = '';
			//}
			//unset($row['waypointsString']);
		}
		//var_dump($routes);
		//echo "<pre>"; print_r($routes); echo "</pre>";

		$routes = array_values($routes);
		return $routes;
		
	}
	
	function getPathStartingCoords() {
		$overviewPath = $this->getOverviewPath();
		if (!empty($overviewPath)) {
			$encoder = new PolylineEncoder();
			$points = $encoder->decodePolylineToArray($overviewPath);
			if (is_array($points) AND count($points) > 0) {
			return reset($points);
			}
		}
	}
	
	function getStartingPointCoords() {
		
		if (Settings::STARTING_POINT_PATH == Settings::getOption(Settings::OPTION_INDEX_MAP_STARTING_POINT_MARKER)) {
			$lat = $this->getApproxLatitude();
			$long = $this->getApproxLongitude();
			if (is_numeric($lat) AND is_numeric($long)) {
				return array(
					$this->getApproxLatitude(),
					$this->getApproxLongitude(),
				);
			}
		} else {
			$location = $this->getFirstLocation();
			if($location)
			{
				$lat = $location->getLat();
				$long = $location->getLong();
				if (is_numeric($lat) AND is_numeric($long)) {
					return array(
						$lat,
						$long,
					);
				}
			} else {
				return $this->getPathStartingCoords();
			}
		}			
	}

	function getFirstLocationIcon() {
		$location = $this->getFirstLocation();
		if($location) {
			return $location->getIcon();
		} else {
			// if location not exist and then get icon from cat if exist
			$cat_icon = '';
			$route_cats = $this->getCategories();
			if($route_cats) {
				foreach($route_cats as $rcatkey=>$rcatval) {
					if($rcatval->getIcon() != '') {
						$cat_icon = $rcatval->getIcon();
						break;
					}
				}
			}
			return $cat_icon;
		}
		return '';
	}
	
	function getFirstLocationInfoN($id = '', $pid = '') {
		if($id != '') {
			
			$content = wpautop(get_option('cmloc_map_location_info_window_template', ''));
			$loc_data = get_post($id);
			
			$attachment_post_id = '';
			$attachment_posts = get_posts(array(
				'posts_per_page' => -1,
				'post_type' => 'attachment',
				'post_status' => 'any',
				'post_parent' => $id,
				'orderby' => 'menu_order',
				'order' => 'asc',
			));
			if(count($attachment_posts) > 0) {
				foreach($attachment_posts as $attach) {
					if(strpos($attach->post_mime_type, 'image') !== false) {
						$attachment_post_id = $attach->ID;
						break;
					}
				}
			}

			$content = str_replace('[title]', $loc_data->post_title, $content);
			$content = str_replace('[description]', $loc_data->post_content, $content);
			$content = str_replace('[address]', get_post_meta($pid, '_cmloc_address', true), $content);
			$content = str_replace('[city]', get_post_meta($pid, '_cmloc_city', true), $content);
			$content = str_replace('[latitude]', get_post_meta($pid, '_cmloc_latitude', true), $content);
			$content = str_replace('[longitude]', get_post_meta($pid, '_cmloc_longitude', true), $content);
			$content = str_replace('[postalcode]', get_post_meta($pid, '_cmloc_postal_code', true), $content);
			$content = str_replace('[phone]', get_post_meta($pid, '_cmloc_phone_number', true), $content);
			$content = str_replace('[website]', get_post_meta($pid, '_cmloc_website', true), $content);
			$content = str_replace('[email]', get_post_meta($pid, '_cmloc_email', true), $content);
			$content = str_replace('[url]', get_post_meta($pid, '_cmloc_url', true), $content);
			$content = str_replace('[permalink]', get_permalink($loc_data->ID), $content);
			$content = str_replace('[imagesrc]', ($attachment_post_id != '')?wp_get_attachment_url($attachment_post_id):'', $content);
			$content = str_replace('[image]', ($attachment_post_id != '')?'<img src="'.wp_get_attachment_url($attachment_post_id).'" class="cmloc-location-image-'.$attachment_post_id.'" />':'', $content);
			
			$createddate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $loc_data->ID));
			$content = str_replace('[createddate]', $createddate, $content);

			$updatedate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_modified_time('U', true, $loc_data->ID));
			$content = str_replace('[updatedate]', $updatedate, $content);

			$editlink = '';
			if(is_user_logged_in() && current_user_can('administrator')) {
				$editlink = '<a href="'.get_edit_post_link($loc_data->ID).'" target="_blank">'.Labels::getLocalized('dashboard_edit').'</a>';
			}
			$content = str_replace('[editlink]', $editlink, $content);
			
			$deletelink = '';
			if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
				
				$href = RouteController::getDashboardUrl('delete', array('id' => $loc_data->ID, 'nonce' => wp_create_nonce(DashboardController::DELETE_NONCE),));

				$deletelink = '<a href="'.$href.'" onClick="return confirm(\''.Labels::getLocalized('confirm_delete_msg').'\')">' . Labels::getLocalized( 'dashboard_delete' ) . '</a>';
			}
			$content = str_replace('[deletelink]', $deletelink, $content);

			$closelink = '<a href="javascript:void(0);" class="rinfowindow_closelink">'.Labels::getLocalized('close').'</a>';
			$content = str_replace( '[closelink]', $closelink, $content );

			$ctabutton = '';
			$content = str_replace( '[ctabutton]', $ctabutton, $content );
			
			$coordinates = '';
			if(get_post_meta($pid, '_cmloc_shape_type', true) != '') {
				$coordinates .= '<span class="heading">'.Labels::getLocalized('coordinates').'<br></span>';
				if(get_post_meta($pid, '_cmloc_shape_type', true) == 'polygon') {
					$shapePolygonCoords = get_post_meta($pid, '_cmloc_shape_polygon_coords', true);
					$shapePolygonCoords_arr = explode("),(", $shapePolygonCoords);
					if(count($shapePolygonCoords_arr) > 0) {
						$coords_count = 1;
						foreach($shapePolygonCoords_arr as $coords) {
							$coords = str_replace('(', '', $coords);
							$coords = str_replace(')', '', $coords);
							$coordinates .= '<span class="rows">'.$coords_count.'. '.$coords.'<br></span>';
							$coords_count++;
						}
					}
				}
				else if(get_post_meta($pid, '_cmloc_shape_type', true) == 'circle') {
					$shapeCircleCenter = get_post_meta($pid, '_cmloc_shape_circle_center', true);
					$shapeCircleCenter = str_replace('(', '', $shapeCircleCenter);
					$shapeCircleCenter = str_replace(')', '', $shapeCircleCenter);
					$coordinates .= '<span class="rows">1. '.$shapeCircleCenter.'</span>';
				}
				else if(get_post_meta($pid, '_cmloc_shape_type', true) == 'rectangle') {
					$shapeRectangleBounds = get_post_meta($pid, '_cmloc_shape_rectangle_bounds', true);
					$shapeRectangleBounds_arr = explode("), (", $shapeRectangleBounds);
					$coordinates .= '<span class="rows">1. '.str_replace('((', '', $shapeRectangleBounds_arr[0]).'<br></span>';
					$coordinates .= '<span class="rows">2. '.str_replace('))', '', $shapeRectangleBounds_arr[1]).'</span>';
				}
			}
			$content = str_replace( '[coordinates]', $coordinates, $content );

			return $content;
		} else {
			$location = $this->getFirstLocation();
			if($location) {
				// route when location exist
				return $location->getInfoWindowContent();
			} else {
				// route when location not exist
				$content = wpautop(Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_TEMPLATE));
				$route_data = get_post($this->getId());
				$content = str_replace('[title]', $route_data->post_title, $content);
				$content = str_replace('[description]', $route_data->post_content, $content);
				$content = str_replace('[permalink]', get_permalink($this->getId()), $content);
				$content = str_replace('[address]', '', $content); // not possible
				$content = str_replace('[linktext]', '', $content); // not possible
				$content = str_replace('[linkurl]', '', $content); // not possible
				$content = str_replace('[distance_from_start]', '', $content); // not possible
				$content = str_replace('[latitude]', get_post_meta($this->getId(), 'cmmrm_approx_latitude', true), $content);
				$content = str_replace('[longitude]', get_post_meta($this->getId(), 'cmmrm_approx_longitude', true), $content);
				$content = str_replace('[altitude]', '', $content); // not possible
				$content = str_replace('[imagesrc]', $this->getRouteFirstImageSrc(), $content);
				$content = str_replace('[image]', $this->getRouteFirstImage(), $content);
				
				$createddate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $this->getId()));
				$content = str_replace('[createddate]', $createddate, $content);

				$updatedate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_modified_time('U', true, $this->getId()));
				$content = str_replace('[updatedate]', $updatedate, $content);
				
				$editlink = '';
				if(is_user_logged_in() && current_user_can('administrator')) {
					$editlink = '<a href="'.$this->getUserEditUrl().'" target="_blank">'.Labels::getLocalized('dashboard_edit').'</a>';
				}
				$content = str_replace('[editlink]', $editlink, $content);

				$deletelink = '';
				if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
					
					$href = RouteController::getDashboardUrl('delete', array('id' => $this->getId(), 'nonce' => wp_create_nonce(DashboardController::DELETE_NONCE),));

					$deletelink = '<a href="'.$href.'" onClick="return confirm(\''.Labels::getLocalized('confirm_delete_msg').'\')">' . Labels::getLocalized( 'dashboard_delete' ) . '</a>';
				}
				$content = str_replace('[deletelink]', $deletelink, $content);

				$closelink = '<a href="javascript:void(0);" class="rinfowindow_closelink">'.Labels::getLocalized('close').'</a>';
				$content = str_replace( '[closelink]', $closelink, $content );

				$ctaButtonText = get_post_meta($this->getId(), '_cmmrm_cta_button_text', true);
				$ctaButtonUrl = get_post_meta($this->getId(), '_cmmrm_cta_button_url', true);
				if($ctaButtonText != '' && $ctaButtonUrl != '') {
					if($ctaButtonUrl == '#') {
						$ctabutton = '<a href="'.$ctaButtonUrl.'" class="cmmrm-cta-button-a-tooltip">'.$ctaButtonText.'</a>';
					} else {
						$ctabutton = '<a href="'.$ctaButtonUrl.'" class="cmmrm-cta-button-a-tooltip" target="_blank">'.$ctaButtonText.'</a>';
					}
				} else {
					$ctabutton = '';
				}
				$content = str_replace( '[ctabutton]', $ctabutton, $content );

				return $content;
			}
		}
	}

	static function getFirstLocationInfo($id = '', $pid = '') {
		if($id != '') {
			// location

			/*
			$content = wpautop(Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_TEMPLATE));
			$loc_data = get_post($id);

			$attachment_posts = get_posts(array(
				'posts_per_page' => 1,
				'post_type' => 'attachment',
				'post_status' => 'any',
				'post_parent' => $id,
				'orderby' => 'menu_order',
				'order' => 'asc',
			));

			$content = str_replace('[title]', $loc_data->post_title, $content);
			$content = str_replace('[description]', $loc_data->post_content, $content);
			$content = str_replace('[address]', get_post_meta($pid, '_cmloc_address', true), $content);
			$content = str_replace('[city]', get_post_meta($pid, '_cmloc_city', true), $content);
			$content = str_replace('[linktext]', '', $content); // not possible
			$content = str_replace('[linkurl]', '', $content); // not possible
			$content = str_replace('[distance_from_start]', '', $content); // not possible
			$content = str_replace('[latitude]', get_post_meta($pid, '_cmloc_latitude', true), $content);
			$content = str_replace('[longitude]', get_post_meta($pid, '_cmloc_longitude', true), $content);
			$content = str_replace('[permalink]', get_permalink($loc_data->post_parent), $content);
			$content = str_replace('[altitude]', '', $content); // not possible
			$content = str_replace('[imagesrc]', (isset($attachment_posts[0]->ID))?wp_get_attachment_url($attachment_posts[0]->ID):'', $content);
			$content = str_replace('[image]', (isset($attachment_posts[0]->ID))?'<img src="'.wp_get_attachment_url($attachment_posts[0]->ID).'" class="cmloc-location-image-'.$attachment_posts[0]->ID.'" />':'', $content);
			$content = str_replace('[createddate]', '', $content);
			$content = str_replace('[updatedate]', '', $content);
			
			$editlink = '';
			if(is_user_logged_in() && current_user_can('administrator')) {
				$editlink = '<a href="'.get_edit_post_link($loc_data->ID).'" target="_blank">'.Labels::getLocalized('dashboard_edit').'</a>';
			}
			$content = str_replace('[editlink]', $editlink, $content);

			$content = str_replace('[deletelink]', '', $content);

			return $content;
			*/

			$content = wpautop(get_option('cmloc_map_location_info_window_template', ''));
			$loc_data = get_post($id);
			
			$attachment_post_id = '';
			$attachment_posts = get_posts(array(
				'posts_per_page' => -1,
				'post_type' => 'attachment',
				'post_status' => 'any',
				'post_parent' => $id,
				'orderby' => 'menu_order',
				'order' => 'asc',
			));
			if(count($attachment_posts) > 0) {
				foreach($attachment_posts as $attach) {
					if(strpos($attach->post_mime_type, 'image') !== false) {
						$attachment_post_id = $attach->ID;
						break;
					}
				}
			}

			$content = str_replace('[title]', $loc_data->post_title, $content);
			$content = str_replace('[description]', $loc_data->post_content, $content);
			$content = str_replace('[address]', get_post_meta($pid, '_cmloc_address', true), $content);
			$content = str_replace('[city]', get_post_meta($pid, '_cmloc_city', true), $content);
			$content = str_replace('[latitude]', get_post_meta($pid, '_cmloc_latitude', true), $content);
			$content = str_replace('[longitude]', get_post_meta($pid, '_cmloc_longitude', true), $content);
			$content = str_replace('[postalcode]', get_post_meta($pid, '_cmloc_postal_code', true), $content);
			$content = str_replace('[phone]', get_post_meta($pid, '_cmloc_phone_number', true), $content);
			$content = str_replace('[website]', get_post_meta($pid, '_cmloc_website', true), $content);
			$content = str_replace('[email]', get_post_meta($pid, '_cmloc_email', true), $content);
			$content = str_replace('[url]', get_post_meta($pid, '_cmloc_url', true), $content);
			$content = str_replace('[permalink]', get_permalink($loc_data->ID), $content);
			$content = str_replace('[imagesrc]', ($attachment_post_id != '')?wp_get_attachment_url($attachment_post_id):'', $content);
			$content = str_replace('[image]', ($attachment_post_id != '')?'<img src="'.wp_get_attachment_url($attachment_post_id).'" class="cmloc-location-image-'.$attachment_post_id.'" />':'', $content);
			
			$createddate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $loc_data->ID));
			$content = str_replace('[createddate]', $createddate, $content);

			$updatedate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_modified_time('U', true, $loc_data->ID));
			$content = str_replace('[updatedate]', $updatedate, $content);

			$editlink = '';
			if(is_user_logged_in() && current_user_can('administrator')) {
				$editlink = '<a href="'.get_edit_post_link($loc_data->ID).'" target="_blank">'.Labels::getLocalized('dashboard_edit').'</a>';
			}
			$content = str_replace('[editlink]', $editlink, $content);
			
			$deletelink = '';
			if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
				
				$href = RouteController::getDashboardUrl('delete', array('id' => $loc_data->ID, 'nonce' => wp_create_nonce(DashboardController::DELETE_NONCE),));

				$deletelink = '<a href="'.$href.'" onClick="return confirm(\''.Labels::getLocalized('confirm_delete_msg').'\')">' . Labels::getLocalized( 'dashboard_delete' ) . '</a>';
			}
			$content = str_replace('[deletelink]', $deletelink, $content);

			$closelink = '<a href="javascript:void(0);" class="rinfowindow_closelink">'.Labels::getLocalized('close').'</a>';
			$content = str_replace( '[closelink]', $closelink, $content );

			$ctabutton = '';
			$content = str_replace( '[ctabutton]', $ctabutton, $content );
			
			$coordinates = '';
			if(get_post_meta($pid, '_cmloc_shape_type', true) != '') {
				$coordinates .= '<span class="heading">'.Labels::getLocalized('coordinates').'<br></span>';
				if(get_post_meta($pid, '_cmloc_shape_type', true) == 'polygon') {
					$shapePolygonCoords = get_post_meta($pid, '_cmloc_shape_polygon_coords', true);
					$shapePolygonCoords_arr = explode("),(", $shapePolygonCoords);
					if(count($shapePolygonCoords_arr) > 0) {
						$coords_count = 1;
						foreach($shapePolygonCoords_arr as $coords) {
							$coords = str_replace('(', '', $coords);
							$coords = str_replace(')', '', $coords);
							$coordinates .= '<span class="rows">'.$coords_count.'. '.$coords.'<br></span>';
							$coords_count++;
						}
					}
				}
				else if(get_post_meta($pid, '_cmloc_shape_type', true) == 'circle') {
					$shapeCircleCenter = get_post_meta($pid, '_cmloc_shape_circle_center', true);
					$shapeCircleCenter = str_replace('(', '', $shapeCircleCenter);
					$shapeCircleCenter = str_replace(')', '', $shapeCircleCenter);
					$coordinates .= '<span class="rows">1. '.$shapeCircleCenter.'</span>';
				}
				else if(get_post_meta($pid, '_cmloc_shape_type', true) == 'rectangle') {
					$shapeRectangleBounds = get_post_meta($pid, '_cmloc_shape_rectangle_bounds', true);
					$shapeRectangleBounds_arr = explode("), (", $shapeRectangleBounds);
					$coordinates .= '<span class="rows">1. '.str_replace('((', '', $shapeRectangleBounds_arr[0]).'<br></span>';
					$coordinates .= '<span class="rows">2. '.str_replace('))', '', $shapeRectangleBounds_arr[1]).'</span>';
				}
			}
			$content = str_replace( '[coordinates]', $coordinates, $content );

			return $content;
		} else {
			$location = $this->getFirstLocation();
			if($location) {
				// route when location exist
				return $location->getInfoWindowContent();
			} else {
				// route when location not exist
				$content = wpautop(Settings::getOption(Settings::OPTION_ROUTE_MAP_LOCATION_INFO_WINDOW_TEMPLATE));
				$route_data = get_post($this->getId());
				$content = str_replace('[title]', $route_data->post_title, $content);
				$content = str_replace('[description]', $route_data->post_content, $content);
				$content = str_replace('[permalink]', get_permalink($this->getId()), $content);
				$content = str_replace('[address]', '', $content); // not possible
				$content = str_replace('[linktext]', '', $content); // not possible
				$content = str_replace('[linkurl]', '', $content); // not possible
				$content = str_replace('[distance_from_start]', '', $content); // not possible
				$content = str_replace('[latitude]', get_post_meta($this->getId(), 'cmmrm_approx_latitude', true), $content);
				$content = str_replace('[longitude]', get_post_meta($this->getId(), 'cmmrm_approx_longitude', true), $content);
				$content = str_replace('[altitude]', '', $content); // not possible
				$content = str_replace('[imagesrc]', $this->getRouteFirstImageSrc(), $content);
				$content = str_replace('[image]', $this->getRouteFirstImage(), $content);
				
				$createddate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $this->getId()));
				$content = str_replace('[createddate]', $createddate, $content);

				$updatedate = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_modified_time('U', true, $this->getId()));
				$content = str_replace('[updatedate]', $updatedate, $content);
				
				$editlink = '';
				if(is_user_logged_in() && current_user_can('administrator')) {
					$editlink = '<a href="'.$this->getUserEditUrl().'" target="_blank">'.Labels::getLocalized('dashboard_edit').'</a>';
				}
				$content = str_replace('[editlink]', $editlink, $content);

				$deletelink = '';
				if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {
					
					$href = RouteController::getDashboardUrl('delete', array('id' => $this->getId(), 'nonce' => wp_create_nonce(DashboardController::DELETE_NONCE),));

					$deletelink = '<a href="'.$href.'" onClick="return confirm(\''.Labels::getLocalized('confirm_delete_msg').'\')">' . Labels::getLocalized( 'dashboard_delete' ) . '</a>';
				}
				$content = str_replace('[deletelink]', $deletelink, $content);

				$closelink = '<a href="javascript:void(0);" class="rinfowindow_closelink">'.Labels::getLocalized('close').'</a>';
				$content = str_replace( '[closelink]', $closelink, $content );

				$ctaButtonText = get_post_meta($this->getId(), '_cmmrm_cta_button_text', true);
				$ctaButtonUrl = get_post_meta($this->getId(), '_cmmrm_cta_button_url', true);
				if($ctaButtonText != '' && $ctaButtonUrl != '') {
					if($ctaButtonUrl == '#') {
						$ctabutton = '<a href="'.$ctaButtonUrl.'" class="cmmrm-cta-button-a-tooltip">'.$ctaButtonText.'</a>';
					} else {
						$ctabutton = '<a href="'.$ctaButtonUrl.'" class="cmmrm-cta-button-a-tooltip" target="_blank">'.$ctaButtonText.'</a>';
					}
				} else {
					$ctabutton = '';
				}
				$content = str_replace( '[ctabutton]', $ctabutton, $content );

				return $content;
			}
		}
	}

	/**
	 * 
	 * @return Location
	 */
	function getFirstLocation() {
		$locations = $this->getLocations();
		return reset($locations);
	}
	
	/*
 	function canEdit($userId = null) {
 		if (is_null($userId)) $userId = get_current_user_id();
 		$result = (user_can($userId, 'manage_options') OR ($userId == $this->getAuthorId() AND self::canCreate($userId)));
 		return apply_filters('cmmrm_route_can_edit', $result, $this->getId(), $userId);
 	}
	*/
	
	static function canEditOwnMaps($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_EDIT);
		if (empty($access)) $access = Settings::ACCESS_USER;
		$result = self::checkAccess(
				$access,
				$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_EDIT_CAP),
				$userId
			);
		if (user_can($userId, 'manage_options')) $result = true;
		return $result;
	}
	
	static function canEditCollaborativeMaps($routeId = null, $userId = null) {
		$result = false;
		if (function_exists('groups_get_user_groups')) {
			$current_user_groups_arr = groups_get_user_groups($userId);
			$current_user_groups = $current_user_groups_arr['groups'];
			$current_user_total = $current_user_groups_arr['total'];
			if($current_user_total > 0) {
				foreach($current_user_groups as $current_user_group_id) {
					$route_bp_groups = get_post_meta($routeId, '_cmmrm_bp_groups', true);
					if($route_bp_groups) {
						if(is_array($route_bp_groups) && count($route_bp_groups) > 0) {
							if(in_array($current_user_group_id, $route_bp_groups)) {
								$result = true;
							}
						}
					}
				}
			}
		}
		return $result;
	}

	function canEdit($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		if (user_can($userId, 'manage_options')) {
			$result = true;
		} else if (static::canEditOwnMaps($userId) AND $userId == $this->getAuthorId()) {
			$result = true;
		} else if (static::canEditCollaborativeMaps($this->getId(), $userId)) {
			$result = true;
		} else {
			$result = false;
		}
		//$result = static::canEditOwnMaps($userId);
		//$result = (user_can($userId, 'manage_options') OR ($userId == $this->getAuthorId() AND $result));
		return apply_filters('cmmrm_route_can_edit', $result, $userId);
	}
	
	static function canCreate($userId = null) {
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_CREATE);
		if (empty($access)) $access = Settings::ACCESS_USER;
		$result = self::checkAccess(
			$access,
			$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_CREATE_CAP),
			$userId
		);
		return apply_filters('cmmrm_route_can_create', $result, $userId);
	}
	
	function canView($userId = null) {
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_VIEW);
		if (empty($access)) $access = Settings::ACCESS_GUEST;
		$result = self::checkAccess(
			$access,
			$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_VIEW_CAP),
			$userId
		);
		return apply_filters('cmmrm_route_can_view', $result, $this->getId(), $userId);
	}
	
	static function canViewIndex($userId = null) {
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_INDEX);
		if (empty($access)) $access = Settings::ACCESS_GUEST;
		$result = self::checkAccess(
			$access,
			$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_INDEX_CAP),
			$userId
		);
		return apply_filters('cmmrm_route_can_view_index', $result, $userId);
	}
	
	function canDelete($userId = null) {
		$result = $this->canEdit($userId);
		return apply_filters('cmmrm_route_can_delete', $result, $this->getId(), $userId);
	}
	
	function getRate() {
		return intval($this->getPostMeta(static::META_RATING_CACHE));
	}
	
	function updateRatingCache() {
		global $wpdb;
		$rating = $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value)/COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
			$this->getId(),
			self::META_RATE
		));
		$rating = intval($rating);
		return $this->setPostMeta(static::META_RATING_CACHE, $rating);
	}
	
	function canRate($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		$result = !empty($userId);
		return apply_filters('cmmrm_route_can_rate', $result, $this->getId(), $userId);
	}
	
	function didUserRate() {
		global $wpdb;
		$userId = get_current_user_id();
		if (empty($userId)) return null;
		$sql = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s AND meta_value = %d",
			$this->getId(),
			self::META_RATE_USER_ID .'%',
			$userId
		);
		$count = $wpdb->get_var($sql);
		return ($count > 0);
	}
	
	function rate($rate) {
		$id = add_post_meta($this->getId(), self::META_RATE, $rate, $unique= false);
		if ($id) {
			add_post_meta($this->getId(), self::META_RATE_TIME .'_'. $id, time());
			add_post_meta($this->getId(), self::META_RATE_USER_ID .'_'. $id, get_current_user_id());
			$this->updateRatingCache();
			return $id;
		}
	}
	
	function getVotesNumber() {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
			$this->getId(),
			self::META_RATE
		));
	}
	
	function getRelatedRoutes($limit = 5) {
		return array_map(array(get_called_class(), 'getInstance'), get_posts(array(
			'posts_per_page' => $limit,
			'post_type' => static::POST_TYPE,
			'post_status' => 'publish',
			'orderby' => 'id',
			'order' => 'desc',
			//'suppress_filters' => true,
			//'category' => implode(',', $this->getCategories(Category::FIELDS_ID_SLUG)),
			//'post__not_in' => get_option( 'sticky_posts' ),
			'exclude' => $this->getId(),
			'tax_query' => array(
				array(
					'taxonomy' => Category::TAXONOMY,
					'field' => 'id',
					'terms' => $this->getCategories(Category::FIELDS_IDS),
					'include_children' => false,
				),
				array(
					'taxonomy' => Tag::TAXONOMY,
					'field' => 'id',
					'terms' => $this->getTags(Tag::FIELDS_IDS),
				),
				'relation' => 'OR',
			),
		)));
	}
	
	function updateLocationsAltitudes() {
		$locations = $this->getLocations();
		if (!empty($locations)) {
			$result = Location::downloadEvelations(array_map(function(Location $location) {
				return array($location->getLat(), $location->getLong());
			}, $locations));
			foreach ($locations as $i => $location) {
				if (isset($result['results'][$i]) AND $location->getAltitude() != $result['results'][$i]['elevation']) {
					$location->setAltitude($result['results'][$i]['elevation']);
				}
			}
		}
	}
	
	function determineElevationParams() {
		
		$path = $this->getOverviewPath();
		$encoder = new PolylineEncoder();
		$coords = $encoder->decodePolylineToArray($path);
		
		$elevationResult = Location::downloadEvelations($coords);
		if (empty($elevationResult) OR !is_array($elevationResult)) {
			return $this;
		}
		
		$maxElevation = null;
		$minElevation = null;
		$gain = 0;
		$descent = 0;
		$lastElevation = null;
		foreach ($elevationResult['results'] as $row) {
			if (is_null($maxElevation) OR $row['elevation'] > $maxElevation) {
				$maxElevation = $row['elevation'];
			}
			if (is_null($minElevation) OR $row['elevation'] < $minElevation) {
				$minElevation = $row['elevation'];
			}
			if (!is_null($lastElevation)) {
				$gain += ((($row['elevation'] - $lastElevation) > 0) ? ($row['elevation'] - $lastElevation) : 0);
				$descent += ((($lastElevation - $row['elevation']) > 0) ? ($lastElevation - $row['elevation']) : 0);
			}
			$lastElevation = $row['elevation'];
		}
		
		if (!is_null($maxElevation)) {
			$this->setMaxElevation($maxElevation);
		}
		if (!is_null($minElevation)) {
			$this->setMinElevation($minElevation);
		}
		$this->setElevationGain($gain);
		$this->setElevationDescent($descent);
		
		return $this;
		
	}
	
	static function checkAccess($access, $capability, $userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		
		if (user_can($userId, 'manage_options')) {
			return true;
		}
		
		switch ($access) {
			case Settings::ACCESS_GUEST:
				return true;
				break;
			case Settings::ACCESS_USER:
				return !empty($userId);
				break;
			case Settings::ACCESS_CAPABILITY:
				return (!empty($userId) AND user_can($userId, $capability));
			default:
				if (!empty($userId) AND $user = get_userdata($userId)) {
					return in_array($access, $user->roles);
				}
				break;
		}
		return false;
	}
	
	/**
	 * Return distance calculated along the path or entered manually by user.
	 * 
	 * @return number
	 */
	function getDistance() {
		return intval(get_post_meta($this->getId(), self::META_DISTANCE, $single = true));
	}
	
	function getFormattedDistance() {
		
		$dist = $this->getDistance();
		$useMinor = $this->useMinorLengthUnits();
		
        $unit_length = apply_filters('cmmrm_change_unit_length', Settings::getOption(Settings::OPTION_UNIT_LENGTH));
        $unit_length_dec = Settings::getOption(Settings::OPTION_UNIT_LENGTH_DEC);
		if($unit_length_dec == '') {
			$unit_length_dec = 0;
		}

		if (Settings::UNIT_FEET == $unit_length) {
			$num = $dist/Settings::FEET_TO_METER;
			if (!$useMinor AND $num > Settings::FEET_IN_MILE) {
				return number_format(($num/Settings::FEET_IN_MILE), $unit_length_dec, '.', '') .' miles';
			} else {
				return number_format($num, $unit_length_dec, '.', '') .' ft';
			}
		} else {
			if (!$useMinor AND $dist > 1000) {
				return number_format(($dist/1000), $unit_length_dec, '.', '') .' km';
			} else {
				return number_format($dist, $unit_length_dec, '.', '') .' m';
			}
		}
		
	}
	
	static function formatLength($dist) {
		$unit_length_dec = Settings::getOption(Settings::OPTION_UNIT_LENGTH_DEC);
		if (Settings::UNIT_FEET == Settings::getOption(Settings::OPTION_UNIT_LENGTH)) {
			$num = $dist/Settings::FEET_TO_METER;
			if ($num > Settings::FEET_IN_MILE) {
				return number_format(($num/Settings::FEET_IN_MILE), $unit_length_dec, '.', '') .' miles';
			} else {
				return number_format($num, $unit_length_dec, '.', '') .' ft';
			}
		} else {
			if ($dist > 1000) {
				return number_format(($dist/1000), $unit_length_dec, '.', '') .' km';
			} else {
				return number_format($dist, $unit_length_dec, '.', '') .' m';
			}
		}
	}
	
	static function formatElevation($dist) {
        $unit_length = apply_filters('cmmrm_change_unit_length', Settings::getOption(Settings::OPTION_UNIT_LENGTH));
        if (Settings::UNIT_FEET == $unit_length) {
			return number_format(($dist/Settings::FEET_TO_METER), 0, '.', '') .' ft';
		} else {
			return number_format($dist, 0, '.', '') .' m';
		}
	}
	
	static function formatSpeed($meterPerSec) {
        $unit_length = apply_filters('cmmrm_change_unit_length', Settings::getOption(Settings::OPTION_UNIT_LENGTH));
        if (Settings::UNIT_FEET == $unit_length) {
			if($meterPerSec != '' && is_numeric($meterPerSec)) {
				return round($meterPerSec/Settings::FEET_TO_METER/Settings::FEET_IN_MILE*3600) . ' mph';
			} else {
				return '0.0 mph';
			}
		} else {
			if($meterPerSec != '' && is_numeric($meterPerSec)) {
				return number_format($meterPerSec * 3.6, 1, '.', '') . ' km/h';
			} else {
				return '0.0 km/h';
			}
		}
	}
	
	static function formatTime($sec) {
		$num = $sec;
		$label = round($num) .' s';
		if ($num > 60) {
			$num /= 60;
			$label = round($num) .' min';
		}
		if ($num > 60) {
			$label = floor($num/60) .' h '. ($num%60) .' min ';
		}
		return $label;
	}
	
	function setDistance($distMeters) {
		return update_post_meta($this->getId(), self::META_DISTANCE, $distMeters);
	}
	
	/*
 	function determineDistance() {
		
 		$dist = 0;
 		$path = $this->getOverviewPath();
 		$encoder = new PolylineEncoder();
 		$points = $encoder->decodePolylineToArray($path);
 		if (is_array($points) AND count($points) > 0) {
 			$last = null;
 			foreach ($points as $point) {
 				if ($last) {
 					$dist += Route::calculateDistance($point[0], $point[1], $last[0], $last[1]);
 				}
 				$last = $point;
 			}
 		}
	
 		return $this->setDistance($dist);
 	}
	*/
	
	function getDuration() {
		return intval(get_post_meta($this->getId(), self::META_DURATION, $single = true));
	}
	
	function setDuration($durationSec) {
		return update_post_meta($this->getId(), self::META_DURATION, $durationSec);
	}
	
	function determineDuration() {
		$sec = $this->getDistance() / $this->getAvgSpeed();
		return $this->setDuration($sec);
	}
	
	function getWaypoints() {
		return get_post_meta($this->getId(), self::META_WAYPOINTS, $single = true) ?: array();
	}
	
	function setWaypoints(array $waypoints) {
		return update_post_meta($this->getId(), self::META_WAYPOINTS, $waypoints);
	}
	
	function getWaypointsString() {
		$str = '';
		if(get_post_meta($this->getId(), self::META_WAYPOINTS_STRING, $single = true) != '') {
			$str = get_post_meta($this->getId(), self::META_WAYPOINTS_STRING, $single = true);
			
			//mkk
			
			if (strpos($str, ',') !== false) {
				$str = stripslashes($str);
			}
			
			//if (strpos($str, ',') !== false || strpos($str, '\\\\') !== false) {
			//	$str = stripslashes($str);
			//}

		}
		return $str;
		//return get_post_meta($this->getId(), self::META_WAYPOINTS_STRING, $single = true) ?: '';
	}
	
	function setWaypointsString($val) {
		return update_post_meta($this->getId(), self::META_WAYPOINTS_STRING, addslashes($val));
	}
	
	function getOverviewPath() {
		return (string)get_post_meta($this->getId(), self::META_OVERVIEW_PATH, $single = true);
	}
	
	function setOverviewPath($path) {
		return update_post_meta($this->getId(), self::META_OVERVIEW_PATH, addslashes($path));
	}
	
	function recalculateOverviewPath() {
		global $wpdb;
		$result = '';
		$points = $wpdb->get_results($wpdb->prepare("SELECT lat.meta_value AS latitude, lon.meta_value AS longitude
			FROM $wpdb->posts loc
			JOIN $wpdb->postmeta lat ON loc.ID = lat.post_id AND lat.meta_key = %s
			JOIN $wpdb->postmeta lon ON loc.ID = lon.post_id AND lon.meta_key = %s
			WHERE loc.post_parent = %d
				AND loc.post_type = %s
			ORDER BY loc.menu_order ASC
		", Location::META_LAT, Location::META_LONG, $this->getId(), Location::POST_TYPE), ARRAY_N);
		if (!empty($points)) {
			
			//$result = Polyline::encodePoints($points);
			$polyline = new PolylineEncoder();
			$r = $polyline->encode($points);
			if (!empty($r->points)) {
				$result = $r->points;
			}
			
			if (strlen($result) > 0) {
				$this->setOverviewPath(stripslashes($result));
			}
		}
	}
	
	/**
	 * Get average speed in meters per second.
	 * 
	 * @return number
	 */
	function getAvgSpeed() {
		return get_post_meta($this->getId(), self::META_AVG_SPEED, $single = true);
	}
	
	/**
	 * Set average speed in meters per second.
	 * 
	 * @param float $speed AVG speed in meters per second.
	 */
	function setAvgSpeed($meterPerSec) {
		return update_post_meta($this->getId(), self::META_AVG_SPEED, $meterPerSec);
	}
	
	function determineAvgSpeed() {
		switch ($this->getTravelMode()) {
			case 'BICYCLING':
				$speed = 12; // km/h
				break;
			case 'DRIVING':
				$speed = 70; // km/h
				break;
			default:
				$speed = 4; // km/h
		}
		return $this->setAvgSpeed($speed * 1000/3600);
	}
	
	function getMaxElevation() {
		return intval(get_post_meta($this->getId(), self::META_MAX_ELEVATION, $single = true));
	}
	
	function setMaxElevation($maxElevation) {
		return update_post_meta($this->getId(), self::META_MAX_ELEVATION, $maxElevation);
	}
	
	function determineMaxElevation() {
		global $wpdb;
		$max = $wpdb->get_var($wpdb->prepare("SELECT MAX(al.meta_value)
			FROM $wpdb->posts loc
			JOIN $wpdb->postmeta al ON loc.ID = al.post_id AND al.meta_key = %s
			WHERE loc.post_parent = %d
				AND loc.post_type = %s
			", Location::META_ALTITUDE, $this->getId(), Location::POST_TYPE));
		return $this->setMaxElevation($max);
	}
	
	function getMinElevation() {
		return intval(get_post_meta($this->getId(), self::META_MIN_ELEVATION, $single = true));
	}
	
	function determineMinElevation() {
		global $wpdb;
		$min = $wpdb->get_var($wpdb->prepare("SELECT MIN(al.meta_value)
			FROM $wpdb->posts loc
			JOIN $wpdb->postmeta al ON loc.ID = al.post_id AND al.meta_key = %s
			WHERE loc.post_parent = %d
			AND loc.post_type = %s
			", Location::META_ALTITUDE, $this->getId(), Location::POST_TYPE));
			return $this->setMinElevation($min);
	}
	
	function setMinElevation($minElevation) {
		return update_post_meta($this->getId(), self::META_MIN_ELEVATION, $minElevation);
	}
	
	function getElevationGain() {
		return intval(get_post_meta($this->getId(), self::META_ELEVATION_GAIN, $single = true));
	}
	
	function setElevationGain($elevationGain) {
		return update_post_meta($this->getId(), self::META_ELEVATION_GAIN, $elevationGain);
	}
	
	function determineElevationGain() {
		global $wpdb;
		$altitude = $wpdb->get_results($wpdb->prepare("SELECT al.meta_value AS altitude
			FROM $wpdb->posts loc
			JOIN $wpdb->postmeta al ON loc.ID = al.post_id AND al.meta_key = %s
			WHERE loc.post_parent = %d
			AND loc.post_type = %s
			ORDER BY loc.menu_order ASC
			", Location::META_ALTITUDE, $this->getId(), Location::POST_TYPE), ARRAY_N);
		$gain = 0;
		$last = null;
		foreach ($altitude as $alt) {
			if (!is_null($last)) {
				$gain += ((($alt[0] - $last[0]) > 0) ? ($alt[0] - $last[0]) : 0);
			}
			$last = $alt;
		}
		return $this->setElevationGain($gain);
	}
	
	function determineElevationDescent() {
		global $wpdb;
		$descent = $wpdb->get_results($wpdb->prepare("SELECT al.meta_value AS altitude
			FROM $wpdb->posts loc
			JOIN $wpdb->postmeta al ON loc.ID = al.post_id AND al.meta_key = %s
			WHERE loc.post_parent = %d
			AND loc.post_type = %s
			ORDER BY loc.menu_order ASC
			", Location::META_ALTITUDE, $this->getId(), Location::POST_TYPE), ARRAY_N);
		$val = 0;
		$last = null;
		foreach ($descent as $desc) {
			if (!is_null($last)) {
				$val += ((($last[0] - $desc[0]) > 0) ? ($last[0] - $desc[0]) : 0);
			}
			$last = $desc;
		}
		return $this->setElevationDescent($val);
	}
	
	function getElevationDescent() {
		return intval(get_post_meta($this->getId(), self::META_ELEVATION_DESCENT, $single = true));
	}
	
	function setElevationDescent($elevationDescent) {
		return update_post_meta($this->getId(), self::META_ELEVATION_DESCENT, $elevationDescent);
	}
	
	function setDirectionResponse($response) {
		$val = array('json' => $response, 'time' => time());
		return add_post_meta($this->getId(), self::META_DIRECTIONS_RESPONSE, $val, $unique = false);
	}
	
	function setElevationResponse($response) {
		$val = array('json' => $response, 'time' => time());
		return add_post_meta($this->getId(), self::META_ELEVATION_RESPONSE, $val, $unique = false);
	}
	
	function getTravelMode() {
		$val = '';
		if($this->getId())
		{
			$val = get_post_meta($this->getId(), self::META_TRAVEL_MODE, $single = true);
		}
		else
		{
			if (empty($val)) $val = Settings::getOption(Settings::OPTION_DEFAULT_TRAVEL_MODE);
			if (empty($val)) $val = Settings::TRAVEL_MODE_DIRECT;
		}
		return $val;
	}
	
	function setTravelMode($mode) {
		return update_post_meta($this->getId(), self::META_TRAVEL_MODE, $mode);
	}
	
	function useBuddypressCollaborative() {
		return (1 == $this->getPostMeta(self::META_USE_BUDDYPRESS_COLLABORATIVE));
	}
	
	function setBuddypressCollaborative($use) {
		return $this->setPostMeta(self::META_USE_BUDDYPRESS_COLLABORATIVE, intval($use));
	}

	function useMinorLengthUnits() {
		return (1 == $this->getPostMeta(self::META_USE_MINOR_LENGTH_UNITS));
	}
	
	function setMinorLengthUnits($use) {
		return $this->setPostMeta(self::META_USE_MINOR_LENGTH_UNITS, intval($use));
	}
	
	function showDirectionalArrows() {
		$val = $this->getPostMeta(self::META_SHOW_DIRECTIONAL_ARROWS);
		if ($val === '' OR is_null($val)) {
			$val = Settings::getOption(Settings::OPTION_SINGLE_ROUTE_DIRECTIONAL_ARROWS);
		}
		return $val;
	}
	
	function setShowDirectionalArrows($use) {
		return $this->setPostMeta(self::META_SHOW_DIRECTIONAL_ARROWS, !empty($use) ? 1 : 0);
	}
	
	function showLocationsSection() {
		if (!App::isPro()) return true;
		$val = $this->getPostMeta(self::META_SHOW_LOCATIONS_SECTION);
		//if ($val === '' OR is_null($val)) {
		//	$val = false;
		//}
		return $val;
	}
	
	function setShowLocationsSection($val) {
		return $this->setPostMeta(self::META_SHOW_LOCATIONS_SECTION, !empty($val) ? 1 : 0);
	}

	function getPathColor() {
		$val = $this->getPostMeta(self::META_PATH_COLOR);
		return ((!is_null($val) AND strlen($val) > 0) ? $val : '#3377FF');
	}

	function getSlopeDownwardColor() {
		$val = $this->getPostMeta(self::META_SLOPE_DOWNWARD_COLOR);
		return ((!is_null($val) AND strlen($val) > 0) ? $val : Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_DOWNWARD_BGCOLOR));
	}

	function getSlopeUpwardColor() {
		$val = $this->getPostMeta(self::META_SLOPE_UPWARD_COLOR);
		return ((!is_null($val) AND strlen($val) > 0) ? $val : Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_UPWARD_BGCOLOR));
	}

	function setSlopeDownwardColor($value) {
		return $this->setPostMeta(self::META_SLOPE_DOWNWARD_COLOR, $value);
	}

	function setSlopeUpwardColor($value) {
		return $this->setPostMeta(self::META_SLOPE_UPWARD_COLOR, $value);
	}
	
	function setPathColor($value) {
		return $this->setPostMeta(self::META_PATH_COLOR, $value);
	}
	
	function setCtaButtonText($value) {
		return $this->setPostMeta(self::META_CTA_BUTTON_TEXT, $value);
	}
	
	function getCtaButtonText() {
		return get_post_meta($this->getId(), self::META_CTA_BUTTON_TEXT, $single = true);
	}

	function setCtaButtonUrl($value) {
		return $this->setPostMeta(self::META_CTA_BUTTON_URL, $value);
	}

	function getCtaButtonUrl() {
		return get_post_meta($this->getId(), self::META_CTA_BUTTON_URL, $single = true);
	}

	function showWeatherPerLocation() {
		return (1 == $this->getPostMeta(self::META_SHOW_WEATHER_PER_LOCATION));
	}
	
	function setWeatherPerLocation($val) {
		return $this->setPostMeta(self::META_SHOW_WEATHER_PER_LOCATION, intval($val));
	}
	
	static function getPaginationLimit() {
		return Settings::getOption(Settings::OPTION_PAGINATION_LIMIT);
	}
	
	function getPostMetaKey($name) {
		return $name;
	}
	
	static function getRouteParamsNames() {
		return array(
			self::META_DISTANCE => 'Distance',
			self::META_DURATION => 'Duration',
			self::META_MAX_ELEVATION => 'Max elevation',
			self::META_MIN_ELEVATION => 'Min elevation',
			self::META_ELEVATION_GAIN => 'Climb',
			self::META_ELEVATION_DESCENT => 'Descent',
			self::META_AVG_SPEED => 'AVG Speed',
		);
	}
	
	static function registerQueryOrder(\WP_Query $query, $orderby = null, $order = null) {
		$orderby = Settings::getIndexOrderBy();
		$order = Settings::getIndexOrder();
		switch ($orderby) {
			case Settings::ORDERBY_VIEWS:
				$query->set('meta_key', self::META_VIEWS);
				$orderby = 'meta_value_num';
				break;
			case Settings::ORDERBY_RATING:
				$query->set('meta_key', self::META_RATING_CACHE);
				$orderby = 'meta_value_num';
				break;
			case Settings::ORDERBY_DISTANCE:
				$query->set('meta_key', self::META_DISTANCE);
				$orderby = 'meta_value_num';
				break;
		}
		$query->set('orderby', $orderby);
		$query->set('order', $order);
	}
	
	function setViews($val) {
		update_post_meta($this->getId(), self::META_VIEWS, $val);
		return $this;
	}
	
	function getViews() {
		return get_post_meta($this->getId(), self::META_VIEWS, $single = true);
	}
	
	function incrementViews() {
		$this->setViews((int)$this->getViews() + 1);
	}

	function save() {
		$id = $this->getId();
		$result = parent::save();
		if ($result) {
			if (!$id) {
				$this->setViews(0);
			}
			$this->updateRatingCache();
			$this->updateApproxCoords();
		}
		return $result;
	}
	
	function updateApproxCoords() {
		$coords = $this->getPathStartingCoords();
		if (is_array($coords)) {
			if (count($coords) == 2) {
				$this->setApproxLatitude($coords[0]);
				$this->setApproxLongitude($coords[1]);
			}
		}
		else if ($location = $this->getFirstLocation()) {
			$this->setApproxLatitude($location->getLat());
			$this->setApproxLongitude($location->getLong());
		}
		return $this;
	}
	
	function setApproxLatitude($lat) {
		$this->setPostMeta('geo_latitude', $lat);
		return $this->setPostMeta(static::META_APPROX_LATITUDE, $lat);
	}
	
	function getApproxLatitude() {
		return $this->getPostMeta(static::META_APPROX_LATITUDE);
	}
	
	function setApproxLongitude($long) {
		$this->setPostMeta('geo_longitude', $long);
		return $this->setPostMeta(static::META_APPROX_LONGITUDE, $long);
	}
	
	function getApproxLongitude() {
		return $this->getPostMeta(static::META_APPROX_LONGITUDE);
	}
	
	/**
	 * 
	 * @return \com\cminds\mapsroutesmanager\model\Attachment
	 */
	function getOriginalImportFile() {
		global $wpdb;
		if (App::isPro()) {
			$attachId = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = %s AND post_mime_type IN (%s, %s)",
				$this->getId(), Attachment::POST_TYPE, KmlHelper::MIME_TYPE, GpxHelper::MIME_TYPE));
			if ($attachId) {
				return Attachment::getInstance($attachId);
			}
		}
	}
	
	function acceptByModerator() {
		$this->setStatus('publish')->save();
		do_action('cmmrm_route_accepted_by_moderator', $this);
		return $this->setPostMeta(static::META_MODERATOR_ACCEPTED, 1);
	}
	
	function trashByModerator($routeId) {
		do_action('cmmrm_route_trashed_by_moderator', $this);
		wp_trash_post($routeId);
	}
	
	function isAcceptedByModerator() {
		return ($this->getPostMeta(static::META_MODERATOR_ACCEPTED) == 1);
	}
	
	function showPathOutline() {
		return $this->getPostMeta(static::META_SHOW_PATH_OUTLINE);
	}
	
	function setShowPathOutline($val) {
		return $this->setPostMeta(static::META_SHOW_PATH_OUTLINE, $val);
	}

	function hideOnIndex() {
		return $this->getPostMeta(static::META_HIDE_ON_INDEX);
	}
	
	function sethideOnIndex($val) {
		return $this->setPostMeta(static::META_HIDE_ON_INDEX, $val);
	}

	static function calculateDistance2d($p1Lat, $p1Long, $p2Lat, $p2Long) {
	
		$p1Lat = floatval($p1Lat);
		$p1Long= floatval($p1Long);
		$p2Lat= floatval($p2Lat);
		$p2Long= floatval($p2Long);
		
		$earthRadius = 6371000; // Radius of the earth 63,71,000 metres
		$k = deg2rad($p1Lat);
		$l = deg2rad($p2Lat);
		$m = deg2rad($p2Lat - $p1Lat);
		$n = deg2rad($p2Long - $p1Long);
	
		$a = sin($m/2) * sin($m/2) + cos($k) * cos($l) * sin($n/2) * sin($n/2);
		$b = 2 * atan2(sqrt($a), sqrt(1-$a));
	
		return $earthRadius * $b;
	
	}
	
	static function calculateDistance($lat1, $lon1, $lat2, $lon2, $el1 = 0, $el2 = 0) {
		
		$lat1 = floatval($lat1);
		$lon1 = floatval($lon1);
		$lat2 = floatval($lat2);
		$lon2 = floatval($lon2);
		$el1 = floatval($el1);
		$el2 = floatval($el2);
		
		$earthRadius = 6371; // Radius of the earth 6,371 km
		
		$latDistance = deg2rad($lat2 - $lat1);
		$lonDistance = deg2rad($lon2 - $lon1);
		$a = sin($latDistance / 2) * sin($latDistance / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDistance / 2) * sin($lonDistance / 2);
		$b = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$distance = $earthRadius * $b * 1000; // convert to meters
		
		$height = $el1 - $el2;
		
		$distance = pow($distance, 2) + pow($height, 2);
		
		//return sqrt($distance);
		return sqrt($distance)/1.60934;
		
	}
	
	static function getShortcodeTokensFuncMap() {
		return array(
			'[name]' => 'getTitle',
			'[description]' => 'getContent',
			'[author]' => 'getAuthorDisplayName',
			'[permalink]' => 'getPermalink',
		);
	}
	
	function getAuthorRoutesPermalink() {
		if ($user = $this->getAuthor()) {
			return FrontendController::getUrl('', array(FrontendController::PARAM_FILTER_AUTHOR => $user->user_nicename));
		}
	}

	static function findLocationByAddress($address) {
	
		if (empty($address)) return array();
	
		$cache = get_transient(static::TRANSIENT_GEOLOCATION_BY_ADDR_CACHE);
		if (is_array($cache) AND isset($cache[$address])) {
			return $cache[$address];
		}
	
		/*
		$url = 'http://maps.googleapis.com/maps/api/geocode/json';
		$url = add_query_arg(urlencode_deep(array(
			'address' => $address,
		)), $url);
		*/
		
		$url = 'https://maps.googleapis.com/maps/api/geocode/json';
		$url = add_query_arg(urlencode_deep(array(
			'address' => $address,
			'key' => Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY)
		)), $url);
	
		$result = RemoteConnection::getRemoteJson($url);
		if (is_array($result) AND !empty($result['results']) AND !empty($result['status']) AND $result['status'] == 'OK') {
			$coords = array($result['results'][0]['geometry']['location']['lat'], $result['results'][0]['geometry']['location']['lng']);
			$cache[$address] = $coords;
			set_transient(static::TRANSIENT_GEOLOCATION_BY_ADDR_CACHE, $cache);
			return $coords;
		}
	
	}
	
	function getDistanceFromCoords($lat, $long) {
		return Route::calculateDistance($lat, $long, $this->getApproxLatitude(), $this->getApproxLongitude());
	}
	
	function isSlopesShowingEnabled() {
		if (Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_ENABLE)) {
			if (Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_ALLOW_USER_EDIT)) {
				if ($this->getId()) {
					return $this->getSlopesShowingEnabled();
				} else {
					return true;
				}
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	function getSlopesShowingEnabled() {
		$val = $this->getPostMeta(static::META_SLOPES_SHOW);
		if (is_null($val) OR $val === '') $val = true;
		return $val;
	}
	
	function setSlopesShowingEnabled($val) {
		return $this->setPostMeta(static::META_SLOPES_SHOW, intval($val));
	}
	
	function getSlopeMinValue() {
		$val = $this->getPostMeta(static::META_SLOPE_MIN_VALUE);
		if (empty($val) OR !Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_ALLOW_USER_EDIT)) {
			$val = Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_MIN_VALUE);
		}
		return $val;
	}
	
	function setSlopeMinValue($val) {
		return $this->setPostMeta(static::META_SLOPE_MIN_VALUE, $val);
	}
	
	function getSlopeMinWidth() {
		$val = $this->getPostMeta(static::META_SLOPE_MIN_WIDTH);
		if (empty($val) OR !Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_ALLOW_USER_EDIT)) {
			$val = Settings::getOption(Settings::OPTION_ELEVATION_GRAPH_SLOPES_MIN_WIDTH);
		}
		return $val;
	}
	
	function setSlopeMinWidth($val) {
		return $this->setPostMeta(static::META_SLOPE_MIN_WIDTH, $val);
	}
	
	function getOsmtiles() {
		$val = $this->getPostMeta(static::META_OSMTILES);
		if (empty($val)) $val = array();
		return $val;
	}
	
	function setOsmtiles($value) {
		return $this->setPostMeta(static::META_OSMTILES, $value);
	}

}