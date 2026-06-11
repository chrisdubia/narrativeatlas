<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;

class ZipController extends Controller {
	
	protected static $filters = array(
		'posts_search' => array('args' => 2, 'priority' => 1000),
		'cmmrm_options_config',
	);
	protected static $actions = array(
		'cmmrm_route_index_search_form_top' => array('args' => 1),
	);
	
	static function cmmrm_route_index_search_form_top($atts) {
		
		if (!Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_FILTER_ENABLE)) return;
		if (empty($atts['zipcode'])) return;
		
		global $wp_query;
// 		var_dump($wp_query->request);exit;
		
		$radiusOptions = array();
		$unitLen = Settings::getOption(Settings::OPTION_UNIT_LENGTH);
		$start = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_MIN);
		$max = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_MAX);
		$defaultRadius = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_DEFAULT);
		$step = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_STEP);
		for ($val = $start; $val <= $max; $val += $step) {
			if ($unitLen == Settings::UNIT_FEET) {
				$valMeters = $val * Settings::FEET_IN_MILE * Settings::FEET_TO_METER;
			} else {
				$valMeters = $val * 1000;
			}
			$valKm = round($valMeters/1000);
			$radiusOptions[$valKm] = $val . ' ' . ($unitLen == Settings::UNIT_METERS ? Labels::getLocalized('length_km') : Labels::getLocalized('length_miles'));
		}
		
		$radiusValue = filter_input(INPUT_GET, 'zipradius');
		if (empty($radiusValue)) {
			$radiusValue = ($unitLen == Settings::UNIT_METERS ? $defaultRadius : $defaultRadius * Settings::FEET_IN_MILE * Settings::FEET_TO_METER);
		}
		
		$zipcodeValue = filter_input(INPUT_GET, 'zipcode');
		
		echo self::loadFrontendView('filter', compact('radiusOptions', 'zipcodeValue', 'radiusValue'));
	}
	
	
	static function posts_search($search, \WP_Query $query) {
		if(!is_admin()) {
			if (Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_FILTER_ENABLE) AND FrontendController::isRoutePostType($query)) {
				$zipcode = filter_input(INPUT_GET, 'zipcode');
				$radiusKm = filter_input(INPUT_GET, 'zipradius');
				if ($zipcode AND $radiusKm) {
					
					$radiusMeters = $radiusKm * 1000;
					$radiusMiles = $radiusMeters / Settings::FEET_TO_METER / Settings::FEET_IN_MILE;
					
					$coords = Route::findLocationByAddress($zipcode . ' ' . Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_COUNTRY));
					//var_dump($coords);exit;
					if (!empty($coords)) {
						
						$query->cmmrmRadiusMeters = $radiusMeters;
						$query->cmmrmZipcode = $zipcode;
						$query->cmmrmCoords = $coords;
						
						$sql = '(
							  acos(sin(cmroute_ziplat.meta_value * 0.0175) * sin('. $coords[0] .' * 0.0175) 
								   + cos(cmroute_ziplat.meta_value * 0.0175) * cos('. $coords[0] .' * 0.0175) *    
									 cos(('. $coords[1] .' * 0.0175) - (cmroute_ziplng.meta_value * 0.0175))
								  ) * 3959 <= '. $radiusMiles .'
						  )';
						
						$search .= ' AND ' . $sql;
						
						// Add required joins
						add_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10, 2);
						add_filter('posts_groupby', array(__CLASS__, 'posts_groupby'), 10, 2);
						
					}
					
				}
			}
		}
		return $search;
	}
	
	
	static function posts_search_join($join, \WP_Query $query) {
		global $wpdb;
		// Additional joins to search by address and postal code
		$join .= PHP_EOL . "JOIN $wpdb->posts cmroute_ziploc ON cmroute_ziploc.post_parent = $wpdb->posts.ID";
		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmroute_ziplat ON cmroute_ziplat.post_id = cmroute_ziploc.ID AND cmroute_ziplat.meta_key = %s", Location::META_LAT);
		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmroute_ziplng ON cmroute_ziplng.post_id = cmroute_ziploc.ID AND cmroute_ziplng.meta_key = %s", Location::META_LONG);
		$join .= PHP_EOL;
		remove_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10);
		return $join;
	}
	
	
	static function posts_groupby($groupby, \WP_Query $query) {
		global $wpdb;
		$groupby .= $wpdb->posts . '.ID';
		remove_filter('posts_groupby', array(__CLASS__, 'posts_groupby'), 10);
		return $groupby;
	}
	
	
	
	static function cmmrm_options_config($config) {
		
		$config = array_merge($config, array(
			Settings::OPTION_INDEX_ZIP_RADIUS_FILTER_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'index',
				'subcategory' => 'zip',
				'title' => 'Enable ZIP code radius filter',
				'desc' => 'If enabled the ZIP code radius filter will be added to the index page next to the search box.',
			),
			Settings::OPTION_INDEX_ZIP_RADIUS_COUNTRY => array(
				'type' => Settings::TYPE_STRING,
				'default' => 'USA',
				'category' => 'index',
				'subcategory' => 'zip',
				'title' => 'Country code for the ZIP code searching',
				'desc' => 'The ZIP filter will work only within a single country.',
			),
			Settings::OPTION_INDEX_ZIP_RADIUS_MIN => array(
				'type' => Settings::TYPE_INT,
				'default' => 10,
				'category' => 'index',
				'subcategory' => 'zip',
				'title' => 'Minimum radius value',
			),
			Settings::OPTION_INDEX_ZIP_RADIUS_MAX => array(
				'type' => Settings::TYPE_INT,
				'default' => 1000,
				'category' => 'index',
				'subcategory' => 'zip',
				'title' => 'Maximum radius value',
			),
			Settings::OPTION_INDEX_ZIP_RADIUS_STEP => array(
				'type' => Settings::TYPE_INT,
				'default' => 10,
				'category' => 'index',
				'subcategory' => 'zip',
				'title' => 'Radius value step',
			),
			Settings::OPTION_INDEX_ZIP_RADIUS_DEFAULT => array(
				'type' => Settings::TYPE_INT,
				'default' => 10,
				'category' => 'index',
				'subcategory' => 'zip',
				'title' => 'Radius default value',
			),
			Settings::OPTION_INDEX_ZIP_RADIUS_GEOLOCATION => array(
				'type' => Settings::TYPE_BOOL,
				'default' => 0,
				'category' => 'index',
				'subcategory' => 'zip',
				'title' => 'Enable geolocation',
				'desc' => 'If enabled the user\'s ZIP code will be recognized using browser\'s geolocation API.'
					.'<br />Notice that the geolocation API works only if you\'re using https.',
			),
		));
		
		return $config;
		
	}
	
}