<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\shortcode\RouteIndexShortcode;
use com\cminds\mapsroutesmanager\model\MapLocationObject;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\shortcode\SearchShortcode;
use com\cminds\mapsroutesmanager\model\Location;

class RouteController extends Controller {
	
	static $filters = array(
		'cmmrm_route_index_single' => array('args' => 2),
		'posts_search' => array('args' => 2),
		'manage_cmmrm_route_posts_columns',
		'post_row_actions' => array('args' => 2),
	);
	
	static $actions = array(
		array('name' => 'get_template_part_cmmrm', 'args' => 2),
		'cmmrm_route_index_filter',
		'cmmrm_display_index_map' => array('args' => 1),
		'cmmrm_route_single_before' => array('args' => 1),
		'cmmrm_route_single_map',
		'cmmrm_route_single_details',
		'cmmrm_route_single_locations',
		'wp_enqueue_scripts',
		'cmmrm_load_single_page_scripts',
		'before_delete_post' => array('args' => 1),
		'manage_cmmrm_route_posts_custom_column' => array('args' => 2),
		'wp_before_admin_bar_render',
	);
	
	const PARAM_PAGE = 'page';
	const ADMIN_COLUMN_ID = 'cmmrm_col_route_id';
	
	static $mapId = null;
	
	static function addHooks() {
		parent::addHooks();
		add_action('pre_get_posts', array(__CLASS__, 'pre_get_posts'), PHP_INT_MAX-5, 1);
	}
	
	static function indexView(\WP_Query $query) {
		global $wp_query;
		if (Route::canViewIndex()) {
			$out = static::getIndexView($query);
		} else {
			$out = Labels::getLocalized('route_index_access_denied');
		}
		$wp_query->reset_postdata();
		return $out;
	}

	static function getStyle( $setting = false ) {
		if ( ! $setting ) {
			$setting = Settings::getOption( 'cmmrm_map_themes' );
		}
		switch ( $setting ) {
			case "silver":
				return "[{\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#f5f5f5\"}]},{\"elementType\":\"labels.icon\",\"stylers\":[{\"visibility\":\"off\"}]},{\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#616161\"}]},{\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#f5f5f5\"}]},{\"featureType\":\"administrative.land_parcel\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#bdbdbd\"}]},{\"featureType\":\"poi\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#eeeeee\"}]},{\"featureType\":\"poi\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#757575\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#e5e5e5\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#9e9e9e\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#ffffff\"}]},{\"featureType\":\"road.arterial\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#757575\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dadada\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#616161\"}]},{\"featureType\":\"road.local\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#9e9e9e\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#e5e5e5\"}]},{\"featureType\":\"transit.station\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#eeeeee\"}]},{\"featureType\":\"water\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#c9c9c9\"}]},{\"featureType\":\"water\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#9e9e9e\"}]}]";
				break;
			case "retro":
				return "[{\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#ebe3cd\"}]},{\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#523735\"}]},{\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#f5f1e6\"}]},{\"featureType\":\"administrative\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#c9b2a6\"}]},{\"featureType\":\"administrative.land_parcel\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#dcd2be\"}]},{\"featureType\":\"administrative.land_parcel\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#ae9e90\"}]},{\"featureType\":\"landscape.natural\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"poi\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"poi\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#93817c\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#a5b076\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#447530\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#f5f1e6\"}]},{\"featureType\":\"road.arterial\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#fdfcf8\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#f8c967\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#e9bc62\"}]},{\"featureType\":\"road.highway.controlled_access\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#e98d58\"}]},{\"featureType\":\"road.highway.controlled_access\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#db8555\"}]},{\"featureType\":\"road.local\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#806b63\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#8f7d77\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#ebe3cd\"}]},{\"featureType\":\"transit.station\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#dfd2ae\"}]},{\"featureType\":\"water\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#b9d3c2\"}]},{\"featureType\":\"water\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#92998d\"}]}]";
				break;
			case "dark":
				return "[{\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#212121\"}]},{\"elementType\":\"labels.icon\",\"stylers\":[{\"visibility\":\"off\"}]},{\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#757575\"}]},{\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#212121\"}]},{\"featureType\":\"administrative\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#757575\"}]},{\"featureType\":\"administrative.country\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#9e9e9e\"}]},{\"featureType\":\"administrative.land_parcel\",\"stylers\":[{\"visibility\":\"off\"}]},{\"featureType\":\"administrative.locality\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#bdbdbd\"}]},{\"featureType\":\"poi\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#757575\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#181818\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#616161\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#1b1b1b\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#2c2c2c\"}]},{\"featureType\":\"road\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#8a8a8a\"}]},{\"featureType\":\"road.arterial\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#373737\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#3c3c3c\"}]},{\"featureType\":\"road.highway.controlled_access\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#4e4e4e\"}]},{\"featureType\":\"road.local\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#616161\"}]},{\"featureType\":\"transit\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#757575\"}]},{\"featureType\":\"water\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#000000\"}]},{\"featureType\":\"water\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#3d3d3d\"}]}]";
				break;
			case "night":
				return "[{\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#242f3e\"}]},{\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#746855\"}]},{\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#242f3e\"}]},{\"featureType\":\"administrative.locality\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#d59563\"}]},{\"featureType\":\"poi\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#d59563\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#263c3f\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#6b9a76\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#38414e\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#212a37\"}]},{\"featureType\":\"road\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#9ca5b3\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#746855\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#1f2835\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#f3d19c\"}]},{\"featureType\":\"transit\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#2f3948\"}]},{\"featureType\":\"transit.station\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#d59563\"}]},{\"featureType\":\"water\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#17263c\"}]},{\"featureType\":\"water\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#515c6d\"}]},{\"featureType\":\"water\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#17263c\"}]}]";
				break;
			case "aubergine":
				return "[{\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#1d2c4d\"}]},{\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#8ec3b9\"}]},{\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#1a3646\"}]},{\"featureType\":\"administrative.country\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#4b6878\"}]},{\"featureType\":\"administrative.land_parcel\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#64779e\"}]},{\"featureType\":\"administrative.province\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#4b6878\"}]},{\"featureType\":\"landscape.man_made\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#334e87\"}]},{\"featureType\":\"landscape.natural\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#023e58\"}]},{\"featureType\":\"poi\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#283d6a\"}]},{\"featureType\":\"poi\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#6f9ba5\"}]},{\"featureType\":\"poi\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#1d2c4d\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#023e58\"}]},{\"featureType\":\"poi.park\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#3C7680\"}]},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#304a7d\"}]},{\"featureType\":\"road\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#98a5be\"}]},{\"featureType\":\"road\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#1d2c4d\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#2c6675\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#255763\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#b0d5ce\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#023e58\"}]},{\"featureType\":\"transit\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#98a5be\"}]},{\"featureType\":\"transit\",\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#1d2c4d\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#283d6a\"}]},{\"featureType\":\"transit.station\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#3a4762\"}]},{\"featureType\":\"water\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#0e1626\"}]},{\"featureType\":\"water\",\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#4e6d70\"}]}]";
				break;
			default:
				return "[]";
		}
	}

	static function getIndexView(\WP_Query $query) {
		
		wp_enqueue_script('cmmrm-index-geolocation');
		
		$atts = array('query' => $query, 'ajax' => 0);
		if ($limit = filter_input(INPUT_GET, FrontendController::PARAM_LIMIT)) {
			$atts['limit'] = $limit;
		}
		return RouteIndexShortcode::shortcode($atts);
		
		/*
		$routes = array_map(array(App::namespaced('model\Route'), 'getInstance'), $query->posts);
		$totalRoutesNumber = FrontendController::$query->found_posts;
		$displayParams = Settings::getOption(Settings::OPTION_INDEX_ROUTE_PARAMS);
		return self::loadFrontendView('index', compact('routes', 'totalRoutesNumber', 'displayParams'));
		*/

	}
	
	static function singleView(\WP_Query $query) {
		global $id, $post, $withcomments;
		
		$withcomments = true;
		$post = null;
		
		if (!empty($query->posts[0]) AND $route = Route::getInstance($query->posts[0])) {
			if ($route->canView()) {
				
				$atts = array();
				
				$post = $query->posts[0];
				$atts['mapId'] = static::$mapId = mt_rand();
				$atts['toolbar'] = 1;
				
				$id = $route->getId();
				$route->incrementViews();
				$displayParams = Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PARAMS);
				$mapStyle      = self::getStyle();

				return self::loadFrontendView('single', compact('mapStyle','route', 'atts', 'displayParams'));
				
			} else {
				return Labels::getLocalized('route_access_denied');
			}
			
		} else {
			return Labels::getLocalized('route_not_found');
		}
	}
	
	static function wp_enqueue_scripts() {
		if (FrontendController::isRoutePostType()) {
			FrontendController::enqueueStyle();
		}
		if (FrontendController::isRouteSinglePage()) {
			//wp_enqueue_style('thickbox');
			do_action('cmmrm_load_single_page_scripts');
		} else {
			wp_enqueue_script('cmmrm-index-filter');
		}
	}
	
	static function cmmrm_load_single_page_scripts() {
		//wp_enqueue_script('cmmrm-route-map');
		wp_enqueue_script('cmmrm-widget-single-route');
	}
	
	static function get_template_part_cmmrm($slug, $name) {
		switch ($name) {
			case 'route-index-filter':
				self::displayIndexTop();
				do_action('cmmrm_route_index_filter');
				break;
			case 'route-single-map':
				do_action('cmmrm_route_single_map');
				break;
			case 'route-single-details':
				do_action('cmmrm_route_single_details');
				break;
			case 'route-single-locations':
				do_action('cmmrm_route_single_locations');
				break;
		}
	}
	
	static function displayIndexTop() {
		
		$text = '';
		$category_id = 0;
		$files_list = array();

		if(trim(Settings::getOption(Settings::OPTION_INDEX_TEXT_TOP)) != '') {
			$text .= '<div class="cmmrm_index_top_text">'.do_shortcode(wpautop(Settings::getOption(Settings::OPTION_INDEX_TEXT_TOP))).'</div>';
		}
		
		if(Settings::getOption(Settings::OPTION_INDEX_TEXT_TOP_SHOW_CATEGORY_DESC)) {
			$category = FrontendController::getCategory();
			if($category) {
				$category_id = $category->getID();
				if (trim($category->getDescription()) != '') {
					$text .= '<div class="cmmrm_index_top_cat_text">'.do_shortcode(wpautop($category->getDescription())).'</div>';
				}
				$files_list = $category->getRouteFileList();
			}
		}

		//if ($text == '' && count($files_list) == 0) { return; }

		echo self::loadFrontendView('index-top', compact('text', 'files_list', 'category_id'));
		
	}

	static function cmmrm_display_index_map($atts) {

		global $wpdb;

		$routes = Route::getIndexMapJSLocations(FrontendController::$query);
		//echo "<pre>"; print_r($routes); echo "</pre>";
		if (!empty($atts['cmlocations'])) {
			$atts['cmlocations'] = MapLocationObject::getIndexMapJSLocations();
			if(count($atts['cmlocations']) > 0) {
				foreach($atts['cmlocations'] as $loc) {
					$loc_data = get_post($loc['ID']);
					//echo "<pre>"; print_r($loc_data); echo "</pre>";
					$location['ID'] = $loc['ID'];
					$location['post_title'] = $loc['name'];
					$location['post_date'] = $loc_data->post_date;
					$location['post_author'] = $loc_data->post_author;
					$location['post_type'] = $loc_data->post_type;
					$location['post_status'] = $loc_data->post_status;
					$location['name'] = $loc['name'];
					$location['lat'] = $loc['lat'];
					$location['long'] = $loc['lng'];
					$location['pathColor'] = '#3377ff';
					$location['overviewPath'] = '';
					$location['waypointsString'] = '';
					$location['permalink'] = $loc['permalink'];
					$location['type'] = 'location';
					$location['icon'] = $loc['icon'];
					$location['infoContent'] = Route::getFirstLocationInfo($loc['ID'], $loc['parent_id']);
					
					$shape_fill_color = get_post_meta($loc['parent_id'], '_cmloc_shape_fill_color', true);
					if($shape_fill_color == '') {
						$shape_fill_color = get_option('cmloc_location_shape_fill_opacity', '#000000');
					}
					$location['shape_fill_color'] = $shape_fill_color;

					$shape_fill_opacity = get_post_meta($loc['parent_id'], '_cmloc_shape_fill_opacity', true);
					if($shape_fill_opacity == '') {
						$shape_fill_opacity = get_option('cmloc_location_shape_fill_opacity', '0.2');
					}
					$location['shape_fill_opacity'] = $shape_fill_opacity;
					
					$shape_stroke_color = get_post_meta($loc['parent_id'], '_cmloc_shape_stroke_color', true);
					if($shape_stroke_color == '') {
						$shape_stroke_color = get_option('cmloc_location_shape_stroke_color', '#000000');
					}
					$location['shape_stroke_color'] = $shape_stroke_color;
					
					$shape_stroke_opacity = get_post_meta($loc['parent_id'], '_cmloc_shape_stroke_opacity', true);
					if($shape_stroke_opacity == '') {
						$shape_stroke_opacity = get_option('cmloc_location_shape_stroke_opacity', '1');
					}
					$location['shape_stroke_opacity'] = $shape_stroke_opacity;

					$shape_stroke_weight = get_post_meta($loc['parent_id'], '_cmloc_shape_stroke_weight', true);
					if($shape_stroke_weight == '') {
						$shape_stroke_weight = get_option('cmloc_location_shape_stroke_weight', '2');
					}
					$location['shape_stroke_weight'] = $shape_stroke_weight;

					$location['shape_type'] = get_post_meta($loc['parent_id'], '_cmloc_shape_type', true);
					$location['shape_polygon_coords'] = get_post_meta($loc['parent_id'], '_cmloc_shape_polygon_coords', true);
					$location['shape_circle_center'] = get_post_meta($loc['parent_id'], '_cmloc_shape_circle_center', true);
					$location['shape_circle_radius'] = get_post_meta($loc['parent_id'], '_cmloc_shape_circle_radius', true);
					$location['shape_rectangle_bounds'] = get_post_meta($loc['parent_id'], '_cmloc_shape_rectangle_bounds', true);
					$location['user_track'] = '';
					$location['user_track_all'] = '';
					$routes[] = $location;
				}
			}
			
			if((is_plugin_active('cm-map-locations/cm-map-locations-pro.php') || is_plugin_active('cm-map-locations-pro/cm-map-locations-pro.php')) && $atts['usertracking'] == 1 && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION) == '1') {

				$cmloc_usertrack_user_last_position_only = intval(Settings::getOption('cmloc_usertrack_user_last_position_only'));

				$cmloc_usertrack_user_path_time = get_option('cmloc_usertrack_user_path_time', 0);
				
				if($cmloc_usertrack_user_path_time != '' && $cmloc_usertrack_user_path_time != '0') {
					$cmloc_usertrack_user_path_time_arr = explode(':', $cmloc_usertrack_user_path_time);
					$cmloc_usertrack_user_path_time = $cmloc_usertrack_user_path_time_arr[0] * 60;
					if(isset($cmloc_usertrack_user_path_time_arr[1])) {
						$cmloc_usertrack_user_path_time = $cmloc_usertrack_user_path_time + $cmloc_usertrack_user_path_time_arr[1];
					}
				}

				if($cmloc_usertrack_user_last_position_only == 1) {
					if($cmloc_usertrack_user_path_time == '' || $cmloc_usertrack_user_path_time == '0') {
						$sql = "SELECT * from ".$wpdb->prefix."cmloc_current_locations group by identifier order by created_at desc";
					} else {
						$sql = "SELECT * from ".$wpdb->prefix."cmloc_current_locations where created_at >= DATE_SUB(NOW(), INTERVAL ".$cmloc_usertrack_user_path_time." MINUTE) group by identifier order by created_at desc";
					}
				} else {
					if($cmloc_usertrack_user_path_time == '' || $cmloc_usertrack_user_path_time == '0') {
						$sql = "SELECT * from ".$wpdb->prefix."cmloc_current_locations order by created_at,identifier asc";
					} else {
						$sql = "SELECT * from ".$wpdb->prefix."cmloc_current_locations where created_at >= DATE_SUB(NOW(), INTERVAL ".$cmloc_usertrack_user_path_time." MINUTE) order by created_at,identifier asc";
					}
				}
				
				$clocs = $wpdb->get_results($sql, ARRAY_A);
				
				$identifier_coordinates = array();
				$ic = 0;
				$fidentifier_array = array();
				foreach ($clocs as $frow) {
					$fidentifier = $frow['identifier'];
					
					if(count($fidentifier_array) == 0) {
						$fidentifier_array[] = $fidentifier;
					} else {
						if(!in_array($fidentifier, $fidentifier_array)) {
							$fidentifier_array[] = $fidentifier;
							$ic++;
						}
					}

					$fcoordinates = $frow['coordinates'];
					$fcoordinates_arr = explode(',', $fcoordinates);
					
					$obj = [];
					$obj['lat'] = (float)$fcoordinates_arr[0];
					$obj['lng'] = (float)$fcoordinates_arr[1];

					$identifier_coordinates[$ic][] = $obj;

				}

				if(count($clocs) > 0) {
					foreach ($clocs as $row) {
						
						$identifier = $row['identifier'];
						$user_id = $row['user_id'];
						$username = $row['username'];
						$ip = $row['ip'];

						$name = $user_id;
						
						if($user_id && $user_id > 0 && $user_info = get_userdata($user_id)) {
							$name = $user_info->display_name;
						} else {
							$name = $username;
						}
						
						$description = $row['description'];

						$coordinates = $row['coordinates'];
						$coordinates_arr = explode(',', $coordinates);

						$cmloc_usertrack_tooltip_content = get_option('cmloc_usertrack_tooltip_content', '');

						$infoContent = '';

						if($cmloc_usertrack_tooltip_content == '') {
							if($user_id && $user_id > 0 && $user_info = get_userdata($user_id)) {
								$infoContent .= $user_info->display_name;
							} else {
								$infoContent .= $username;
							}

							if($description != '') {
								$out .= '<br>';
								$out .= $description;
							}

							$out .= '<br><br>';

							$infoContent .= 'Coordinates: '.$coordinates.'<br>';

							$time_format = 'g:i A';
							if(get_option('cmloc_usertrack_timeformat', '0') == '1') {
								$time_format = 'H:i';
							}
							
							$updatedate = date_i18n(get_option('date_format').' '.$time_format, strtotime($created_at));
							$minutes = 0;
							$plusminus = '+';
							
							$timezone = get_option('cmloc_usertrack_timezone', '+ 0:0|UTC+0');
							$timezone_arr = explode('|', $timezone);

							$timezonefirst_arr = explode(' ', $timezone_arr[0]);
							$timezonesecond_arr = explode(':', $timezonefirst_arr[1]);
							$plusminus = $timezonefirst_arr[0];
							$minutes = (((int)$timezonesecond_arr[0] * 60) + (int)$timezonesecond_arr[1]);
							if($minutes > 0) {
								$created_at = date('Y-m-d H:i:s',strtotime($plusminus.$minutes.' minutes',strtotime($created_at)));
								$updatedate = date_i18n(get_option('date_format').' '.$time_format, strtotime($created_at));
							}

							$infoContent .= 'Last Seen: '.$updatedate.' '.$timezone_arr[1].'<br><br>';
							$infoContent .= 'GPX: <a href="?clocid='.$row['id'].'">Download</a>';
						} else {
							if($user_id && $user_id > 0 && $user_info = get_userdata($user_id)) {
								$cmloc_usertrack_tooltip_content  = str_replace( '[name]', $user_info->display_name, $cmloc_usertrack_tooltip_content );
							} else {
								$cmloc_usertrack_tooltip_content  = str_replace( '[name]', $username, $cmloc_usertrack_tooltip_content );
							}

							$cmloc_usertrack_tooltip_content  = str_replace( '[description]', $description, $cmloc_usertrack_tooltip_content );

							$cmloc_usertrack_tooltip_content  = str_replace( '[coordinates]', $coordinates, $cmloc_usertrack_tooltip_content );

							$time_format = 'g:i A';
							if(get_option('cmloc_usertrack_timeformat', '0') == '1') {
								$time_format = 'H:i';
							}

							$updatedate = date_i18n(get_option('date_format').' '.$time_format, strtotime($created_at));
							$minutes = 0;
							$plusminus = '+';

							$timezone = get_option('cmloc_usertrack_timezone', '+ 0:0|UTC+0');
							$timezone_arr = explode('|', $timezone);

							$timezonefirst_arr = explode(' ', $timezone_arr[0]);
							$timezonesecond_arr = explode(':', $timezonefirst_arr[1]);
							$plusminus = $timezonefirst_arr[0];
							$minutes = (((int)$timezonesecond_arr[0] * 60) + (int)$timezonesecond_arr[1]);
							if($minutes > 0) {
								$created_at = date('Y-m-d H:i:s',strtotime($plusminus.$minutes.' minutes',strtotime($created_at)));
								$updatedate = date_i18n(get_option('date_format').' '.$time_format, strtotime($created_at));
							}
							
							$cmloc_usertrack_tooltip_content  = str_replace( '[updatedate]', $updatedate, $cmloc_usertrack_tooltip_content );
							$cmloc_usertrack_tooltip_content  = str_replace( '[timezone]', $timezone_arr[1], $cmloc_usertrack_tooltip_content );
							$cmloc_usertrack_tooltip_content  = str_replace( '[downloadlink]', '<a href="?clocid='.$row['id'].'">Download</a>', $cmloc_usertrack_tooltip_content );

							$infoContent = $cmloc_usertrack_tooltip_content;
						}

						$location['ID'] = $row['id'];
						$location['post_title'] = $name;
						$location['post_date'] = $row['created_at'];
						$location['post_author'] = '';
						$location['post_type'] = '';
						$location['post_status'] = '';
						$location['name'] = $name;
						$location['lat'] = $coordinates_arr[0];
						$location['long'] = $coordinates_arr[1];
						$location['pathColor'] = '#3377ff';
						$location['overviewPath'] = '';
						$location['waypointsString'] = '';
						$location['permalink'] = '';
						$location['type'] = 'location';
						$location['icon'] = get_option('cmloc_usertrack_icon_url', '');
						$location['infoContent'] = $infoContent;
						$location['shape_fill_color'] = '';
						$location['shape_fill_opacity'] = '';
						$location['shape_stroke_color'] = '';
						$location['shape_stroke_opacity'] = '';
						$location['shape_stroke_weight'] = '';
						$location['shape_type'] = '';
						$location['shape_polygon_coords'] = '';
						$location['shape_circle_center'] = '';
						$location['shape_circle_radius'] = '';
						$location['shape_rectangle_bounds'] = '';
						$location['user_track'] = $coordinates;
						$location['user_track_all'] = $identifier_coordinates;
						$routes[] = $location;

					}
				}

			}

		}
		
		//echo "<pre>";
		//print_r($routes);
		//print_r($atts['cmlocations']);
		//echo "</pre>";
		//die;
		
		//if (!empty($routes)) {
			//wp_enqueue_script('cmmrm-index-map');

		$mapStyle = self::getStyle($atts['theme']);
			wp_enqueue_script('cmmrm-widget-index-map');
			echo self::loadFrontendView('index-map', compact('mapStyle', 'routes', 'atts'));
		//}
	}
	
	
	static function getPagination($atts = array()) {
		$query = FrontendController::$query;
		if (FrontendController::isRoutePostType() AND $query->is_archive()) {
			$limit = $atts['limit'];
			if ($query->found_posts > $limit) {
				$total_pages = $query->max_num_pages;
				$page = $query->get('paged');
				if (empty($page)) $page = 1;
				$base_url = static::getPaginationBaseUrl();
				if ($atts['limit'] != Settings::getOption(Settings::OPTION_PAGINATION_LIMIT)) {
					$base_url = add_query_arg(FrontendController::PARAM_LIMIT, $atts['limit'], $base_url);
				}
				return self::loadView('frontend/common/pagination', compact('total_pages', 'page', 'base_url'));
			}
		}
	}
	
	static function getPaginationBaseUrl() {
		$url = FrontendController::getFilterUrl($includeCategory = true);
		return preg_replace('~/page/[0-9]+/~', '/', $url);
	}
	
	static function cmmrm_route_index_filter() {
		if ($category = FrontendController::getCategory()) {
			$searchFormUrl = $category->getPermalink();
		} else {
			$searchFormUrl = FrontendController::getUrl();
		}
		if (App::isPro()) {
			echo SearchShortcode::shortcode(array(
				'searchformurl' => $searchFormUrl,
				'searchstring' => filter_input(INPUT_GET, 's'),
			));
			//echo self::loadFrontendView('index-filter', compact('searchFormUrl', 'searchString'));
		}
	}
	
	static function cmmrm_route_single_before($atts = array()) {
		$route = FrontendController::getRoute();
		echo self::loadFrontendView('single-before', compact('route', 'atts'));
	}
	
	static function cmmrm_route_single_map() {
		$route = FrontendController::getRoute();
		$atts = array('mapId' => static::$mapId);
		echo self::loadFrontendView('single-map', compact('route', 'atts'));
	}
	
	static function cmmrm_route_single_details() {
		$route = FrontendController::getRoute();
		echo self::loadFrontendView('single-details', compact('route'));
	}
	
	static function cmmrm_route_single_locations() {
		$route = FrontendController::getRoute();
		if ($route->showLocationsSection()) {
			echo self::loadFrontendView('single-locations', compact('route'));
		}
	}
	
	static function cmmrm_route_index_single($output, $route) {
		return self::loadFrontendView('index-single', compact('route'));
	}
	
	static function getDashboardUrl($action = 'index', $params = array()) {
		return FrontendController::getUrl(FrontendController::URL_DASHBOARD . '/' . $action, $params);
	}
	
	static function pre_get_posts(\WP_Query $query) {
		if (is_admin()) return;
		if ($query->is_main_query() AND FrontendController::isRoutePostType($query)) {
			//$query->set('post_type', Route::POST_TYPE);
			$query->set('posts_per_page', Route::getPaginationLimit());
			Route::registerQueryOrder($query);
			if (!FrontendController::isDashboard($query)) {
				//$query->set('post_status', 'publish');
			}
		}
		if ($query->is_main_query() AND $categorySlug = $query->get(Category::TAXONOMY)) {
			$query->set('post_type', Route::POST_TYPE);
		}
	}
	
	static function before_delete_post($postId) {
		if ($route = Route::getInstance($postId) AND $route instanceof Route) {
			
			global $wpdb;
			
			// Delete imported GPX/KML file
			if ($file = $route->getOriginalImportFile()) {
				//var_dump($file);
				if ($path = $file->getFilePath() AND is_writable($path) AND is_file($path)) {
					//var_dump($path);
					unlink($path);
				}
			}
			
			// Delete all child posts
			$locationsIds = $route->getLocationsIds();
			$parentIds = $locationsIds;
			$parentIds[] = $postId;
			//var_dump($parentIds);
			$childPostsIds = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_parent IN (". implode(',', $parentIds) .')');
			//var_dump($childPostsIds);
			foreach ($childPostsIds as $id) {
				wp_delete_post($id, true);
			}
			
			//die('end');
			
		}
	}
	
	static function posts_search($sql, \WP_Query $query) {
		if(!is_admin()) {
			if (!Settings::getOption(Settings::OPTION_INDEX_SEARCH_WHOLE_WORDS)) {
				global $wpdb;
				$str = $query->get('s');
				if($str == '') {
					$str = (isset($_GET['s']))?$_GET['s']:'';
				}
				if (strlen($str) > 0 AND $query->is_main_query() AND FrontendController::isRoutePostType($query)) {
					$str = '%' . $str .'%';
					$jsql = $wpdb->prepare(') OR (cmmrm_addr.meta_value LIKE %s OR cmmrm_lat.meta_value LIKE %s OR cmmrm_long.meta_value LIKE %s OR cmmrm_lat_long.meta_value LIKE %s) OR (', $str, $str, $str, $str);
					$sql = str_replace(') OR (', $jsql, $sql);
					add_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10, 2);
				}
				return $sql;
			}
			
			preg_match_all('~\(\w+posts\.post_\w+ LIKE \'%.+%\'\)~U', $sql	, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$new = '';
				$new .= str_replace('%\')', ' %\')', str_replace('LIKE \'%', 'LIKE \'', $match[0])) . ' OR ';
				$new .= str_replace('%\')', '\')', str_replace('LIKE \'%', 'LIKE \'% ', $match[0])) . ' OR ';
				$new .= str_replace('%\')', ' %\')', str_replace('LIKE \'%', 'LIKE \'% ', $match[0])) . ' OR ';
				$new .= str_replace('%\')', '\')', str_replace('LIKE \'%', 'LIKE \'', $match[0])) . PHP_EOL;
				$sql = str_replace($match[0], $new, $sql);
			}
		}
		return $sql;
	}

	static function posts_search_join($join, \WP_Query $query) {
		global $wpdb;
		// Additional joins to search by address and postal code
		$join .= PHP_EOL . "JOIN $wpdb->posts cmmrm_route ON cmmrm_route.post_parent = $wpdb->posts.ID";

		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmmrm_addr ON cmmrm_addr.post_id = cmmrm_route.ID AND cmmrm_addr.meta_key = %s", Location::META_ADDRESS);

		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmmrm_lat ON cmmrm_lat.post_id = cmmrm_route.ID AND cmmrm_lat.meta_key = %s", Location::META_LAT);

		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmmrm_long ON cmmrm_long.post_id = cmmrm_route.ID AND cmmrm_long.meta_key = %s", Location::META_LONG);

		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmmrm_lat_long ON cmmrm_lat_long.post_id = cmmrm_route.ID AND cmmrm_lat_long.meta_key = %s", Location::META_LAT_LONG);

		$join .= PHP_EOL;
		remove_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10);
		return $join;
	}
	
	static function manage_cmmrm_route_posts_columns($columns) {
		$before['cb'] = $columns['cb'];
		$before[static::ADMIN_COLUMN_ID] = 'ID';
		unset($columns['cb']);
		//$columns['cmvl_channel'] = 'Lesson';
		return $before + $columns;
	}
	
	static function manage_cmmrm_route_posts_custom_column($column, $postId) {
		if (static::ADMIN_COLUMN_ID == $column) {
			echo $postId;
		}
	}
	
	static function post_row_actions($actions, $post) {
		if ( $post->post_type === Route::POST_TYPE AND $route = Route::getInstance($post) ) {
			$edit = preg_replace('~>.+</a>~', '>Edit post</a>', $actions['edit']);
			unset($actions['edit']);
			$actions = array_merge(
				array(
					'route_editor' => sprintf('<a href="%s">Route Editor</a>', esc_attr($route->getUserEditUrl())),
					'edit' => $edit,
				), $actions
			);
		}
		return $actions;
	}
	
	static function wp_before_admin_bar_render() {
		global $wp_admin_bar, $wp_query;
		//var_dump($wp_admin_bar);exit;
		
		//$new = $wp_admin_bar->get_node('new-cmmrm_route');
		//var_dump($new);exit;
		
		if (is_admin() AND 'post.php' == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
				AND 'edit' == filter_input(INPUT_GET, 'action') AND $postId = filter_input(INPUT_GET, 'post')
				AND $route = Route::getInstance($postId)) {
			
			$wp_admin_bar->add_node(array(
				'id' => 'cmmrm_route_editor',
				'title' => 'Route Editor',
				'href' => $route->getUserEditUrl(),
			));
		}
		if (!is_admin() AND $route = FrontendController::getRoute()) {
			$wp_admin_bar->remove_node('edit');
			$wp_admin_bar->add_node(array(
				'id' => 'cmmrm_route_post_edit',
				'title' => 'Edit Post',
				'href' => $route->getEditUrl(),
			));
			if (!FrontendController::isDashboard()) {
				$wp_admin_bar->add_node(array(
					'id' => 'cmmrm_route_editor',
					'title' => 'Route Editor',
					'href' => $route->getUserEditUrl(),
				));
			} else {
				$wp_admin_bar->add_node(array(
					'id' => 'cmmrm_route_view',
					'title' => 'View Route',
					'href' => $route->getPermalink(),
				));
			}
		}
	}
	
}