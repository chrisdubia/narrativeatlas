<?php
namespace com\cminds\mapsroutesmanager\shortcode;

use com\cminds\mapsroutesmanager\model;
use com\cminds\mapsroutesmanager\controller;
use com\cminds\mapsroutesmanager\controller\TecController;

class RouteIndexShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cm-route-index';
	
	static function shortcode($atts) {
		
		$the_events_calendar_integration_enable = model\Settings::getOption(model\Settings::OPTION_THE_EVENTS_CALENDAR_INTEGRATION_ENABLE);
		if($the_events_calendar_integration_enable) {
			$tec = "";
			if (isset($_GET['tec'])) {
				$tec = filter_input(INPUT_GET, 'tec');
			}
		}

		if (isset($atts['routetype'])) {
			$atts['cmmrm_route_type'] = $atts['routetype'];
		}
		if (isset($atts['tag'])) {
			$tag = $atts['tag'];
		} else {
			$tag = '';
		}

		$atts = shortcode_atts(apply_filters('cmmrm_routes_shortcode_atts_defaults', array(
			'showonlybyparams' => 0,
			'route' => null,
			'category' => null,
			'author' => null,
			'search' => null,
			'width' => '',
			'theme' => false,
			'mapwidth' => '',
			'mapheight' => '',
			'showmap' => model\Settings::getOption(model\Settings::OPTION_INDEX_MAP_SHOW),
			'showlist' => 1,
			'showfilters' => 1,
			'ajax' => 0,
			'listlayout' => model\Settings::getOption(model\Settings::OPTION_INDEX_LAYOUT),
			'featuredimage' => model\Settings::getOption(model\Settings::OPTION_ROUTE_INDEX_FEATURED_IMAGE),
			'fancy' => model\Settings::getOption(model\Settings::OPTION_FANCY_STYLE_ENABLE),
			'limit' => model\Settings::getOption(model\Settings::OPTION_PAGINATION_LIMIT),
			'cmlocations' => model\Settings::getOption(model\Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION),
			'page' => 1,
			'menu' => 0,
			'query' => null,
			'usertracking' => get_option('cmloc_usertrack_usertracking_enable', '0'),
		), $atts, static::SHORTCODE_NAME), $atts);
		
		$atts['mapId'] = mt_rand();

		if (isset($atts['showonlybyparams']) AND $atts['showonlybyparams'] == '1') {
			if((isset($_GET['route']) && $_GET['route'] != '') || (isset($_GET['category']) && $_GET['category'] != '') || (isset($_GET['routetype']) && $_GET['routetype'] != '') || (isset($_GET['routedifficulty']) && $_GET['routedifficulty'] != '')) {
				$atts['route'] = $_GET['route'];
				$atts['category'] = $_GET['category'];
				$atts['cmmrm_route_type'] = $_GET['routetype'];
				$atts['cmmrm_route_difficulty'] = $_GET['routedifficulty'];
				$atts['showfilters'] = 0;
			} else {
				return '';
			}
		}
		
		if (isset($atts['query']) AND $atts['query'] instanceof \WP_Query) {
			$query = $atts['query'];
		} else {

			$index_orderby = get_option('cmmrm_index_orderby', 'post_date');
			$order = get_option('cmmrm_index_order', 'desc');
			
			$orderby = 'date';
			if($index_orderby == 'post_date') {
				$orderby = 'date';
			}
			if($index_orderby == 'post_title') {
				$orderby = 'title';
			}

			if($atts['route'] != '') {
				$query = new \WP_Query(apply_filters('cmmrm_routes_shortcode_query', array(
					'post_type' => model\Route::POST_TYPE,
					'post_status' => 'publish',
					'author' => $atts['author'],
					'posts_per_page' => $atts['limit'],
					'page' => $atts['page'],
					'tag' => $tag,
					'orderby' => $orderby,
				    'order'   => $order,
					'post_name__in' => array($atts['route']),
					model\Category::TAXONOMY => $atts['category'],
				), $atts, static::SHORTCODE_NAME));
			} else {
				$query = new \WP_Query(apply_filters('cmmrm_routes_shortcode_query', array(
					'post_type' => model\Route::POST_TYPE,
					'post_status' => 'publish',
					'author' => $atts['author'],
					'posts_per_page' => $atts['limit'],
					'page' => $atts['page'],
					'tag' => $tag,
					'orderby' => $orderby,
				    'order'   => $order,
					model\Category::TAXONOMY => $atts['category'],
				), $atts, static::SHORTCODE_NAME));
			}
		}
		
		if (!is_null($atts['search'])) {
			$query->set('s', $atts['search']);
		}
		
		//echo "<pre>"; print_r($query); echo "</pre>";
		//echo $query->request;
				
		//echo "<pre>"; print_r($query->get_posts()); echo "</pre>";
		//echo $query->found_posts;
		//die;
		
		$tec_flag = false;
		if($the_events_calendar_integration_enable) {
			if($tec == '1') {
				$tec_flag = true;
			}
		}
		
		if($tec_flag == false) {
			
			$routes = array_map(function($post) {
				return model\Route::getInstance($post);
			}, $query->get_posts());

			$totalRoutesNumber = $query->found_posts;

		} else {
			
			$routes = $query->get_posts();

			foreach ($routes as $i => $route) {
				$saved_events = TecController::getSavedEvents($route->ID);
				if(count($saved_events) == 0) {
					unset($routes[$i]);
					continue;
				}
			}

			$routes = array_map(function($post) {
				return model\Route::getInstance($post);
			}, $routes);

			$totalRoutesNumber = count($routes);

		}
		
		controller\FrontendController::enqueueStyle();
		wp_enqueue_script('cmmrm-widget-index-map');
		
		do_action('cmmrm_load_single_page_scripts');
		wp_enqueue_script('cmmrm-index-ajax');
		
		$displayParams = model\Settings::getOption(model\Settings::OPTION_INDEX_ROUTE_PARAMS);
		
		global $wp_query, $wp_the_query;
		
		// Temporarily replace $wp_query
		$temp_wp_query = $wp_query;
		$wp_query = $query;
		
		// Temporarily replace controller's query
		$tempControllerQuery = controller\FrontendController::$query;
		controller\FrontendController::$query = $query;
		$mapStyle = controller\RouteController::getStyle($atts['theme']);

		$cmlocations = '';

		$out = controller\RouteController::loadFrontendView('index', compact('mapStyle', 'displayParams', 'atts', 'routes', 'totalRoutesNumber', 'cmlocations'));
		
		// Restore original query
		$wp_query = $temp_wp_query;
		controller\FrontendController::$query = $tempControllerQuery;
		
		//wp_reset_query();
		//wp_reset_postdata();
		
		return $out;
		
	}

}